<!--
    Global Advertisement Component

    This component allows you to display advertisements (images, videos, or HTML content)
    in a modal format that can be triggered from anywhere on the site.

    Usage Examples:

    1. Image Advertisement:
       $emitter.emit('open-advertisement', {
           type: 'image',
           src: '/images/promotion.jpg',
           alt: 'Special Promotion',
           title: 'Limited Time Offer',
           description: 'Get 50% off on all items',
           link: '/products/sale',
           linkTarget: '_blank'
       });

    2. Video Advertisement:
       $emitter.emit('open-advertisement', {
           type: 'video',
           src: '/videos/promotional-video.mp4',
           title: 'Watch Our New Collection',
           autoplay: true,
           loop: false,
           muted: false,
           controls: true
       });

    3. HTML Content Advertisement:
       $emitter.emit('open-advertisement', {
           type: 'html',
           content: '<div><h2>Custom HTML Ad</h2><p>Your custom content here</p></div>',
           title: 'Special Announcement'
       });

    4. Auto-show on page load (in your blade file):
       <script>
           window.addEventListener('load', function() {
               setTimeout(() => {
                   $emitter.emit('show-advertisement', {
                       type: 'image',
                       src: '/images/welcome-banner.jpg',
                       alt: 'Welcome'
                   });
               }, 2000); // Show after 2 seconds
           });
       </script>
-->
<v-advertisement></v-advertisement>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-advertisement-template"
    >
        <div>
            <!-- Advertisement Modal -->
            <div
                class="fixed inset-0 z-[99998] flex items-center justify-center bg-black bg-opacity-60"
                v-show="isAdOpen"
                @click.self="closeAd()"
                style="z-index: 99998 !important;"
            >
                <div class="relative w-[95vw] sm:w-[80vw] max-w-5xl rounded-lg bg-white shadow-2xl overflow-hidden"
                     style="max-width: 98vw; z-index: 99998 !important; max-height: 90vh;">
                    <!-- Close Button -->
                    <div class="absolute right-2 top-2 z-[100000]"
                         style="z-index: 100000 !important;">
                        <button
                            class="text-gray-600 hover:text-gray-900 transition-colors bg-white rounded-full p-2 shadow-lg"
                            @click="closeAd()"
                            aria-label="Close advertisement"
                        >
                            <span class="icon-cross text-xl"></span>
                        </button>
                    </div>

                    <!-- Advertisement Content -->
                    <div class="flex flex-col h-full max-h-[90vh]">
                        <!-- Image Advertisement -->
                        <div v-if="currentAd && currentAd.type === 'image'" class="relative">
                            <a
                                v-if="currentAd.link"
                                :href="currentAd.link"
                                :target="currentAd.linkTarget || '_self'"
                                class="block"
                            >
                                <img
                                    :src="currentAd.src"
                                    :alt="currentAd.alt || 'Advertisement'"
                                    class="w-full h-auto object-contain"
                                    style="max-height: 85vh;"
                                />
                            </a>
                            <img
                                v-else
                                :src="currentAd.src"
                                :alt="currentAd.alt || 'Advertisement'"
                                class="w-full h-auto object-contain"
                                style="max-height: 85vh;"
                            />
                        </div>

                        <!-- Video Advertisement -->
                        <div v-if="currentAd && currentAd.type === 'video'" class="relative bg-black">
                            <video
                                ref="adVideoPlayer"
                                class="w-full h-auto"
                                :controls="currentAd.controls !== false"
                                :autoplay="currentAd.autoplay !== false"
                                :loop="currentAd.loop || false"
                                :muted="currentAd.muted || false"
                                preload="metadata"
                                :src="currentAd.src"
                                style="max-height: 85vh;"
                            >
                                Your browser does not support the video tag.
                            </video>
                        </div>

                        <!-- HTML Content Advertisement -->
                        <div
                            v-if="currentAd && currentAd.type === 'html'"
                            class="p-6 overflow-y-auto"
                            style="max-height: 85vh;"
                            v-html="currentAd.content"
                        >
                        </div>

                        <!-- Advertisement Info/Footer (Optional) -->
                        <div
                            v-if="currentAd && (currentAd.title || currentAd.description || currentAd.footer)"
                            class="bg-gray-50 border-t p-4"
                        >
                            <h3 v-if="currentAd.title" class="text-lg font-semibold text-gray-800 mb-2">
                                @{{ currentAd.title }}
                            </h3>
                            <p v-if="currentAd.description" class="text-sm text-gray-600 mb-3">
                                @{{ currentAd.description }}
                            </p>
                            <div v-if="currentAd.footer" class="text-xs text-gray-500">
                                @{{ currentAd.footer }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-advertisement', {
            template: '#v-advertisement-template',

            data() {
                return {
                    isAdOpen: false,
                    currentAd: null,
                    adQueue: []
                }
            },

            mounted() {
                // Listen for global event to open advertisement
                this.$emitter.on('open-advertisement', this.openAd);

                // Listen for auto-show advertisement (e.g., on page load)
                this.$emitter.on('show-advertisement', this.showAd);

                // Close modal on escape key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.isAdOpen) {
                        this.closeAd();
                    }
                });
            },

            beforeUnmount() {
                this.$emitter?.off('open-advertisement', this.openAd);
                this.$emitter?.off('show-advertisement', this.showAd);
            },

            methods: {
                openAd(adData) {
                    if (!adData) {
                        console.warn('Advertisement data is required');
                        return;
                    }

                    // Validate and set advertisement data
                    this.currentAd = {
                        type: adData.type || 'image', // 'image', 'video', or 'html'
                        src: adData.src || adData.url || '',
                        alt: adData.alt || 'Advertisement',
                        title: adData.title || '',
                        description: adData.description || '',
                        footer: adData.footer || '',
                        link: adData.link || '',
                        linkTarget: adData.linkTarget || '_self',
                        controls: adData.controls !== undefined ? adData.controls : true,
                        autoplay: adData.autoplay !== undefined ? adData.autoplay : true,
                        loop: adData.loop || false,
                        muted: adData.muted || false,
                        content: adData.content || '' // For HTML type
                    };

                    this.isAdOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                showAd(adData) {
                    // Same as openAd but can be used for auto-showing ads
                    this.openAd(adData);
                },

                closeAd() {
                    this.isAdOpen = false;
                    this.currentAd = null;
                    document.body.style.overflow = 'auto';

                    // Pause video when closing
                    if (this.$refs.adVideoPlayer) {
                        this.$refs.adVideoPlayer.pause();
                        this.$refs.adVideoPlayer.currentTime = 0;
                    }
                }
            }
        });
    </script>
@endPushOnce
