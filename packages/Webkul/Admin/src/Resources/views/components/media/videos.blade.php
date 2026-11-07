@props([
    'name'           => 'images',
    'allowMultiple'  => false,
    'uploadedVideos' => [],
    'width'          => '210px',
    'height'         => '120px',
    'uploadUrl'      => ''
])

<v-media-videos
    name="{{ $name }}"
    v-bind:allow-multiple="{{ $allowMultiple ? 'true' : 'false' }}"
    :uploaded-videos='{{ json_encode($uploadedVideos) }}'
    width="{{ $width }}"
    height="{{ $height }}"
    upload-url="{{ $uploadUrl }}"
    :errors="errors"
    {{ $attributes->get('class') }}
>
</v-media-videos>

@include('admin::components.media.uploader')

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-media-videos-template"
    >
        <!-- Panel Content -->
        <div class="grid">
            <div class="flex gap-1">
                <!-- Upload Video Button -->

                <label
                    class="grid h-[120px] max-h-[120px] w-full max-w-[210px] cursor-pointer items-center justify-items-center rounded border border-dashed transition-all hover:border-gray-400 dark:border-gray-800 dark:mix-blend-exclusion dark:invert"
                    :class="[errors['videos.files[0]'] ? 'border border-red-500' : 'border-gray-300']"
                    :for="$.uid + '_videoInput'"
                    v-if="allowMultiple || videos.length == 0"
                >
                    <div class="flex flex-col items-center">
                        <span class="icon-image text-2xl"></span>

                        <p class="grid text-center text-sm font-semibold text-gray-600 dark:text-gray-300">
                            @lang('admin::app.components.media.videos.add-video-btn')
                            
                            <span class="text-xs">
                                @lang('admin::app.components.media.videos.allowed-types')
                            </span>
                        </p>

                        <input
                            type="file"
                            class="hidden"
                            :id="$.uid + '_videoInput'"
                            accept="video/*"
                            :multiple="allowMultiple"
                            :ref="$.uid + '_videoInput'"
                            @change="add"
                        />
                    </div>
                </label>

                <!-- Uploaded Videos -->
                <draggable
                    class="flex gap-1"
                    ghost-class="draggable-ghost"
                    v-bind="{animation: 200}"
                    :list="videos"
                    item-key="id"
                >
                    <template #item="{ element, index }">
                        <v-media-video-item
                            :name="name"
                            :index="index"
                            :video="element"
                            :width="width"
                            :height="height"
                            :upload-mode="uploadMode"
                            @onRemove="remove($event)"
                            @onRetry="retryUpload($event)"
                            @onReplace="replaceUpload($event)"
                        >
                        </v-media-video-item>
                    </template>
                </draggable>
            </div>
        </div>
    </script>

    <script
        type="text/x-template"
        id="v-media-video-item-template"
    >
        <div class="group relative grid h-[120px] max-h-[120px] min-w-[210px] max-w-[210px] justify-items-center overflow-hidden rounded border border-dashed border-gray-300 transition-all hover:border-gray-400 dark:border-gray-800">
            <!-- Video Preview -->
            <video
                class="h-[120px] w-[210px] object-cover"
                ref="videoPreview"
                v-if="video.url.length > 0"
                :key="video.url"
            >
                <source :src="video.url" type="video/mp4">
            </video>

            <!-- Background upload state (only when the component uploads on its own) -->
            <div
                class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-1.5 bg-white/90 p-2 text-center dark:bg-gray-900/90"
                v-if="video.upload && video.upload.status != 'done'"
            >
                <template v-if="video.upload.status == 'error'">
                    <p class="text-xs font-semibold text-red-600">
                        @lang('admin::app.components.media.videos.upload-failed')
                    </p>

                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="text-xs font-semibold text-blue-600"
                            @click="$emit('onRetry', video)"
                        >
                            @lang('admin::app.components.media.videos.upload-retry')
                        </button>

                        <button
                            type="button"
                            class="text-xs font-semibold text-gray-600 dark:text-gray-300"
                            @click="remove"
                        >
                            @lang('admin::app.components.media.videos.upload-cancel')
                        </button>
                    </div>
                </template>

                <template v-else>
                    <p class="text-xs font-semibold text-gray-800 dark:text-white">
                        <template v-if="video.upload.status == 'uploading'">@{{ video.upload.progress }}%</template>
                        <template v-else>@lang('admin::app.components.media.videos.upload-uploading')</template>
                    </p>

                    <div class="h-1.5 w-full overflow-hidden rounded bg-gray-200 dark:bg-gray-800">
                        <div
                            class="h-full rounded bg-blue-600 transition-all"
                            :style="{ width: video.upload.progress + '%' }"
                        ></div>
                    </div>

                    <button
                        type="button"
                        class="text-xs font-semibold text-gray-600 dark:text-gray-300"
                        @click="remove"
                    >
                        @lang('admin::app.components.media.videos.upload-cancel')
                    </button>
                </template>
            </div>

            <div class="invisible absolute bottom-0 top-0 flex w-full flex-col justify-between bg-white p-3 opacity-80 transition-all group-hover:visible dark:bg-gray-900">
                <!-- Video Name -->
                <p class="break-all text-xs font-semibold text-gray-600 dark:text-gray-300"></p>

                <!-- Actions -->
                <div class="flex justify-between">
                    <!-- Remove Button -->
                    <span
                        class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl hover:bg-gray-200 dark:hover:bg-gray-800"
                        @click="remove"
                    ></span>

                    <!-- Play Pause Button -->
                    <span
                        class="cursor-pointer rounded-md p-1.5 text-2xl hover:bg-gray-200 dark:hover:bg-gray-800"
                        :class="[isPlaying ? 'icon-pause': 'icon-play']"
                        @click="playPause"
                    ></span>

                    <!-- Edit Button -->
                    <label
                        class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl hover:bg-gray-200 dark:hover:bg-gray-800"
                        :for="$.uid + '_videoInput_' + index"
                    ></label>

                    <input
                        type="hidden"
                        :name="name + '[' + (video.media_id ?? video.id) + ']'"
                        v-if="! video.is_new"
                    />

                    <input
                        type="file"
                        :name="name + '[]'"
                        class="hidden"
                        accept="video/*"
                        :id="$.uid + '_videoInput_' + index"
                        :ref="$.uid + '_videoInput_' + index"
                        @change="edit"
                    />
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-media-videos', {
            template: '#v-media-videos-template',

            mixins: [
                window.asoMediaUploadMixin({
                    collection: 'videos',
                    prefix: 'video',
                    compress: false,
                    messages: {
                        failed: "@lang('admin::app.components.media.videos.upload-failed')",
                        sessionExpired: "@lang('admin::app.components.media.videos.upload-session-expired')",
                    },
                }),
            ],

            props: {
                name: {
                    type: String, 
                    default: 'videos',
                },

                allowMultiple: {
                    type: Boolean,
                    default: false,
                },

                uploadedVideos: {
                    type: Array,
                    default: () => []
                },

                width: {
                    type: String,
                    default: '210px'
                },

                height: {
                    type: String,
                    default: '120px'
                },

                errors: {
                    type: Object,
                    default: () => {}
                }
            },

            data() {
                return {
                    videos: [],
                }
            },

            mounted() {
                this.videos = this.uploadedVideos;
            },

            methods: {
                add() {
                    let videoInput = this.$refs[this.$.uid + '_videoInput'];

                    if (videoInput.files == undefined) {
                        return;
                    }

                    const validFiles = Array.from(videoInput.files).every(file => file.type.includes('video/'));

                    if (! validFiles) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('admin::app.components.media.videos.not-allowed-error')"
                        });

                        return;
                    }

                    Array.from(videoInput.files).forEach((file) => {
                        if (this.uploadMode) {
                            this.queueUpload(file);

                            return;
                        }

                        this.videos.push({
                            id: 'video_' + this.videos.length,
                            url: '',
                            file: file
                        });
                    });

                    if (this.uploadMode) {
                        videoInput.value = '';
                    }
                },

                remove(video) {
                    if (this.uploadMode) {
                        this.discardUpload(video);
                    }

                    let index = this.videos.indexOf(video);

                    this.videos.splice(index, 1);
                },
            }
        });

        app.component('v-media-video-item', {
            template: '#v-media-video-item-template',

            props: ['index', 'video', 'name', 'width', 'height', 'uploadMode'],

            data() {
                return {
                    isPlaying: false
                }
            },

            mounted() {
                if (! this.uploadMode && this.video.file instanceof File) {
                    this.setFile(this.video.file);

                    this.readFile(this.video.file);
                }
            },

            methods: {
                edit() {
                    let videoInput = this.$refs[this.$.uid + '_videoInput_' + this.index];

                    if (videoInput.files == undefined) {
                        return;
                    }

                    const validFiles = Array.from(videoInput.files).every(file => file.type.includes('video/'));

                    if (! validFiles) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('admin::app.components.media.videos.not-allowed-error')"
                        });

                        return;
                    }

                    if (this.uploadMode) {
                        this.$emit('onReplace', { item: this.video, file: videoInput.files[0] });

                        videoInput.value = '';

                        return;
                    }

                    this.setFile(videoInput.files[0]);

                    this.readFile(videoInput.files[0]);
                },

                remove() {
                    this.$emit('onRemove', this.video)
                },

                setFile(file) {
                    this.video.is_new = 1;

                    const dataTransfer = new DataTransfer();

                    dataTransfer.items.add(file);

                    this.$refs[this.$.uid + '_videoInput_' + this.index].files = dataTransfer.files;
                },

                readFile(file) {
                    let reader = new FileReader();

                    reader.onload = (e) => {
                        this.video.url = e.target.result;
                    }

                    reader.readAsDataURL(file);
                },

                playPause() {
                    let videoPreview = this.$refs.videoPreview;

                    if (videoPreview.paused == true) {
                        this.isPlaying = true;

                        videoPreview.play();
                    } else {
                        this.isPlaying = false;

                        videoPreview.pause();
                    }
                }
            }
        });
    </script>
@endPushOnce