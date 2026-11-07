{{--
    Shared browser side of the background media uploader.

    Exposes `window.asoMediaUploader` (image compression, chunked upload with
    retry, a small concurrency queue) and `window.asoMediaUploadMixin`, the Vue
    behaviour the image and video media components mix in when they are given
    an `upload-url`. Without an upload URL the components keep their classic
    "files travel inside the form" behaviour.
--}}
@pushOnce('scripts')
    <script>
        (function () {
            if (window.asoMediaUploader) {
                return;
            }

            const RETRYABLE_STATUSES = [408, 425, 429, 500, 502, 503, 504];

            const ACTIVE_STATES = ['queued', 'optimising', 'uploading'];

            let running = 0;

            const pending = [];

            const uploader = {
                /**
                 * Size of one chunk. Small enough that a single request finishes in
                 * a reasonable time even on a throttled link, large enough that the
                 * per-request overhead does not dominate.
                 */
                chunkSize: 512 * 1024,

                /**
                 * Files uploading at the same time.
                 */
                concurrency: 2,

                /**
                 * Retries per chunk before the file is marked as failed.
                 */
                maxRetries: 6,

                /**
                 * Per chunk request timeout in milliseconds.
                 */
                chunkTimeout: 4 * 60 * 1000,

                /**
                 * Image compression defaults.
                 */
                image: {
                    maxEdge: 2048,
                    quality: 0.85,
                    minBytes: 400 * 1024,
                },

                isActive(item) {
                    return !! item.upload && ACTIVE_STATES.includes(item.upload.status);
                },

                isCancel(error) {
                    return (window.axios && window.axios.isCancel(error))
                        || error?.code === 'ERR_CANCELED'
                        || error?.name === 'AbortError'
                        || error?.name === 'CanceledError';
                },

                errorMessage(error, messages) {
                    const status = error?.response?.status;

                    if (status === 419 || status === 401) {
                        return messages.sessionExpired;
                    }

                    const data = error?.response?.data;

                    if (data?.errors) {
                        const first = Object.values(data.errors)[0];

                        if (Array.isArray(first) && first.length) {
                            return first[0];
                        }
                    }

                    if (data?.message) {
                        return data.message;
                    }

                    return messages.failed;
                },

                /**
                 * Run tasks with limited concurrency.
                 */
                enqueue(task) {
                    return new Promise((resolve, reject) => {
                        pending.push({ task, resolve, reject });

                        drain();
                    });
                },

                /**
                 * Downscale and re-encode an image in the browser so far less has to
                 * travel over the wire. Falls back to the original file whenever the
                 * browser cannot do better.
                 */
                async compressImage(file, options = {}) {
                    const settings = { ...uploader.image, ...options };

                    if (! /^image\/(jpeg|png|webp|bmp)$/.test(file.type)) {
                        return file;
                    }

                    let objectUrl = null;

                    try {
                        objectUrl = URL.createObjectURL(file);

                        const image = await loadImage(objectUrl);

                        const width = image.naturalWidth;

                        const height = image.naturalHeight;

                        if (! width || ! height) {
                            return file;
                        }

                        const scale = Math.min(1, settings.maxEdge / Math.max(width, height));

                        if (scale === 1 && file.size <= settings.minBytes) {
                            return file;
                        }

                        const canvas = document.createElement('canvas');

                        canvas.width = Math.max(1, Math.round(width * scale));

                        canvas.height = Math.max(1, Math.round(height * scale));

                        const context = canvas.getContext('2d');

                        if (file.type === 'image/jpeg' || file.type === 'image/bmp') {
                            context.fillStyle = '#ffffff';

                            context.fillRect(0, 0, canvas.width, canvas.height);
                        }

                        context.drawImage(image, 0, 0, canvas.width, canvas.height);

                        let blob = await toBlob(canvas, 'image/webp', settings.quality);

                        if (! blob || blob.type !== 'image/webp') {
                            blob = file.type === 'image/png'
                                ? await toBlob(canvas, 'image/png')
                                : await toBlob(canvas, 'image/jpeg', settings.quality);
                        }

                        if (! blob || blob.size >= file.size) {
                            return file;
                        }

                        const extension = blob.type === 'image/webp'
                            ? 'webp'
                            : (blob.type === 'image/png' ? 'png' : 'jpg');

                        return new File(
                            [blob],
                            file.name.replace(/\.[^.]+$/, '') + '.' + extension,
                            { type: blob.type, lastModified: file.lastModified }
                        );
                    } catch (error) {
                        return file;
                    } finally {
                        if (objectUrl) {
                            URL.revokeObjectURL(objectUrl);
                        }
                    }
                },

                /**
                 * Send a file in chunks. Resolves with the media object the server
                 * returns once the last chunk has been stored.
                 */
                async upload(url, file, { onProgress = () => {}, signal } = {}) {
                    const total = Math.max(1, Math.ceil(file.size / uploader.chunkSize));

                    let restarts = 0;

                    while (true) {
                        const uploadId = makeId();

                        try {
                            for (let index = 0; index < total; index++) {
                                const start = index * uploader.chunkSize;

                                const blob = file.slice(start, Math.min(file.size, start + uploader.chunkSize));

                                const data = await sendChunk(url, { uploadId, index, total, file, blob, start, onProgress, signal });

                                if (index === total - 1) {
                                    return data.media;
                                }
                            }
                        } catch (error) {
                            /**
                             * 409 means the server no longer has every chunk (for example the
                             * upload went stale). Start again with a fresh id, but not forever.
                             */
                            if (error?.response?.status === 409 && restarts++ < 2) {
                                continue;
                            }

                            throw error;
                        }
                    }
                },
            };

            function drain() {
                while (running < uploader.concurrency && pending.length) {
                    const { task, resolve, reject } = pending.shift();

                    running++;

                    Promise.resolve()
                        .then(task)
                        .then(resolve, reject)
                        .finally(() => {
                            running--;

                            drain();
                        });
                }
            }

            async function sendChunk(url, context) {
                let attempt = 0;

                while (true) {
                    try {
                        const form = new FormData();

                        form.append('upload_id', context.uploadId);
                        form.append('chunk_index', context.index);
                        form.append('total_chunks', context.total);
                        form.append('file_name', context.file.name);
                        form.append('file_size', context.file.size);
                        form.append('chunk', context.blob, context.file.name);

                        const response = await window.axios.post(url, form, {
                            signal: context.signal,
                            timeout: uploader.chunkTimeout,
                            onUploadProgress: (event) => {
                                const loaded = context.start + Math.min(event.loaded, context.blob.size);

                                context.onProgress(Math.min(1, loaded / Math.max(1, context.file.size)));
                            },
                        });

                        return response.data;
                    } catch (error) {
                        if (uploader.isCancel(error)) {
                            throw error;
                        }

                        const status = error?.response?.status;

                        const retryable = ! status || RETRYABLE_STATUSES.includes(status);

                        if (! retryable || attempt >= uploader.maxRetries) {
                            throw error;
                        }

                        attempt++;

                        await sleep(Math.min(30000, 1000 * Math.pow(2, attempt)), context.signal);
                    }
                }
            }

            function sleep(milliseconds, signal) {
                return new Promise((resolve) => {
                    const timer = setTimeout(resolve, milliseconds);

                    signal?.addEventListener('abort', () => {
                        clearTimeout(timer);

                        resolve();
                    }, { once: true });
                });
            }

            function makeId() {
                if (window.crypto?.randomUUID) {
                    return window.crypto.randomUUID().replace(/-/g, '');
                }

                return Date.now().toString(36) + Math.random().toString(36).slice(2, 14);
            }

            function loadImage(src) {
                return new Promise((resolve, reject) => {
                    const image = new Image();

                    image.onload = () => resolve(image);

                    image.onerror = reject;

                    image.src = src;
                });
            }

            function toBlob(canvas, type, quality) {
                return new Promise((resolve) => canvas.toBlob(resolve, type, quality));
            }

            window.asoMediaUploader = uploader;

            /**
             * Vue options mixed into the media components.
             *
             * `collection` is the data key holding the items ('images' or 'videos'),
             * `prefix` the client side id prefix, `compress` whether files are
             * optimised before upload, and `messages` the translated strings.
             */
            window.asoMediaUploadMixin = function ({ collection, prefix, compress, messages }) {
                return {
                    props: {
                        uploadUrl: {
                            type: String,
                            default: '',
                        },
                    },

                    data() {
                        return {
                            uploadSequence: 0,
                        };
                    },

                    computed: {
                        uploadMode() {
                            return this.uploadUrl.length > 0;
                        },

                        activeUploads() {
                            return this[collection].filter((item) => uploader.isActive(item)).length;
                        },
                    },

                    watch: {
                        activeUploads(active) {
                            this.$emitter.emit('media-upload-state', { source: this.$.uid, active });
                        },
                    },

                    beforeUnmount() {
                        this.$emitter.emit('media-upload-state', { source: this.$.uid, active: 0 });
                    },

                    methods: {
                        /**
                         * Add a freshly picked file and start sending it straight away.
                         */
                        queueUpload(file) {
                            const item = {
                                id: prefix + '_' + (++this.uploadSequence) + '_' + Date.now(),
                                media_id: null,
                                url: URL.createObjectURL(file),
                                is_new: 1,
                                file: null,
                                uploaded_now: false,
                                upload: this.newUploadState(file),
                            };

                            /**
                             * Work with the reactive proxy Vue hands back from the array, not the
                             * raw object, otherwise progress updates would not reach the template.
                             */
                            const reactiveItem = this[collection][this[collection].push(item) - 1];

                            this.startUpload(reactiveItem);

                            return reactiveItem;
                        },

                        newUploadState(file) {
                            return {
                                status: 'queued',
                                progress: 0,
                                error: '',
                                controller: null,
                                file,
                                previous: null,
                            };
                        },

                        startUpload(item) {
                            const controller = new AbortController();

                            item.upload.status = 'queued';
                            item.upload.progress = 0;
                            item.upload.error = '';
                            item.upload.controller = controller;

                            return uploader.enqueue(async () => {
                                if (controller.signal.aborted) {
                                    return;
                                }

                                try {
                                    let file = item.upload.file;

                                    if (compress) {
                                        item.upload.status = 'optimising';

                                        file = await uploader.compressImage(file);
                                    }

                                    if (controller.signal.aborted) {
                                        return;
                                    }

                                    item.upload.status = 'uploading';

                                    const media = await uploader.upload(this.uploadUrl, file, {
                                        signal: controller.signal,
                                        onProgress: (fraction) => {
                                            item.upload.progress = Math.min(99, Math.round(fraction * 100));
                                        },
                                    });

                                    this.finishUpload(item, media);
                                } catch (error) {
                                    if (uploader.isCancel(error)) {
                                        item.upload.status = 'cancelled';

                                        return;
                                    }

                                    item.upload.status = 'error';

                                    item.upload.error = uploader.errorMessage(error, messages);

                                    this.restorePrevious(item);

                                    this.$emitter.emit('add-flash', { type: 'error', message: item.upload.error });
                                }
                            });
                        },

                        finishUpload(item, media) {
                            const previous = item.upload.previous;

                            if (item.url && item.url.startsWith('blob:')) {
                                URL.revokeObjectURL(item.url);
                            }

                            item.media_id = media.id;
                            item.url = media.url;
                            item.is_new = 0;
                            item.uploaded_now = true;
                            item.upload.status = 'done';
                            item.upload.progress = 100;
                            item.upload.previous = null;

                            /**
                             * The item replaced one uploaded earlier in this session; that
                             * file is not referenced anywhere any more.
                             */
                            if (previous && previous.uploaded_now && previous.media_id) {
                                this.deleteRemote(previous.media_id);
                            }
                        },

                        /**
                         * Put an item back to the state it had before a failed replacement.
                         */
                        restorePrevious(item) {
                            const previous = item.upload.previous;

                            if (! previous) {
                                return;
                            }

                            item.media_id = previous.media_id;
                            item.url = previous.url;
                            item.is_new = previous.is_new;
                            item.uploaded_now = previous.uploaded_now;
                            item.upload.previous = null;
                        },

                        retryUpload(item) {
                            if (item.upload?.file) {
                                this.startUpload(item);
                            }
                        },

                        /**
                         * Swap the file of an existing item for a new one.
                         */
                        replaceUpload({ item, file }) {
                            if (uploader.isActive(item)) {
                                item.upload.controller?.abort();
                            }

                            const previous = item.upload?.previous ?? {
                                media_id: item.media_id ?? (item.is_new ? null : item.id),
                                url: item.url,
                                is_new: item.is_new ?? 0,
                                uploaded_now: item.uploaded_now ?? false,
                            };

                            item.upload = this.newUploadState(file);
                            item.upload.previous = previous;
                            item.media_id = null;
                            item.url = URL.createObjectURL(file);
                            item.is_new = 1;
                            item.uploaded_now = false;

                            this.startUpload(item);
                        },

                        /**
                         * Called before an item leaves the list.
                         */
                        discardUpload(item) {
                            if (uploader.isActive(item)) {
                                item.upload.controller?.abort();
                            }

                            if (item.url && item.url.startsWith('blob:')) {
                                URL.revokeObjectURL(item.url);
                            }

                            /**
                             * Files uploaded in this session are removed right away. Media that
                             * existed before the page was opened is only removed when the admin
                             * saves the product, exactly like before.
                             */
                            if (item.uploaded_now && item.media_id) {
                                this.deleteRemote(item.media_id);
                            }

                            const previous = item.upload?.previous;

                            if (previous && previous.uploaded_now && previous.media_id) {
                                this.deleteRemote(previous.media_id);
                            }
                        },

                        deleteRemote(mediaId) {
                            return this.$axios.delete(this.uploadUrl + '/' + mediaId).catch(() => {});
                        },
                    },
                };
            };
        })();
    </script>
@endPushOnce
