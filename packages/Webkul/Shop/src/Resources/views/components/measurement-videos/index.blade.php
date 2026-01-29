<!-- Global Measurement Videos Component -->
<v-measurement-videos></v-measurement-videos>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-measurement-videos-template"
    >
        <div>
            <!-- Video Modal -->
            <div
                class="fixed inset-0 z-[99999] flex items-center justify-center bg-black bg-opacity-50"
                v-show="isVideoOpen"
                @click.self="closeVideo()"
                style="z-index: 99999 !important;"
            >
                <div class="relative w-[95vw] sm:w-[70vw] max-w-4xl rounded-lg bg-black shadow-lg"
                     style="max-width: 98vw; z-index: 99999 !important;">
                    <!-- Video Modal Header -->
                    <div class="absolute right-2 top-2 z-[100000]"
                         style="z-index: 100000 !important;">
                        <button
                            class="text-white hover:text-gray-300 transition-colors"
                            @click="closeVideo()"
                            aria-label="Close video"
                        >
                            <span class="icon-cross text-2xl"></span>
                        </button>
                    </div>

                    <!-- Gender Selection Tabs (if no gender specified) -->
                    <div v-if="!selectedGender" class="bg-white p-4 rounded-t-lg">
                        <div class="flex flex-wrap border-b mb-4">
                            <button
                                class="px-6 py-3 text-sm font-medium transition-colors duration-200"
                                :class="activeTab === 'male' ? 'border-b-2 border-[#4b2462] text-[#4b2462]' : 'text-gray-500 hover:text-gray-700'"
                                @click="selectGender('male')"
                            >
                                Male Measurement Video
                            </button>
                            <button
                                class="px-6 py-3 text-sm font-medium transition-colors duration-200"
                                :class="activeTab === 'female' ? 'border-b-2 border-[#4b2462] text-[#4b2462]' : 'text-gray-500 hover:text-gray-700'"
                                @click="selectGender('female')"
                            >
                                Female Measurement Video
                            </button>
                        </div>
                    </div>

                    <!-- Video Player -->
                    <video
                        ref="videoPlayer"
                        class="h-auto w-full rounded-lg"
                        controls
                        autoplay
                        preload="metadata"
                        :src="currentVideoSrc"
                        style="max-height:80vh;"
                        v-if="currentVideoSrc"
                    >
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-measurement-videos', {
            template: '#v-measurement-videos-template',

            data() {
                return {
                    isVideoOpen: false,
                    activeTab: 'male',
                    selectedGender: null,
                    currentVideoSrc: ''
                }
            },

            mounted() {
                // Listen for global event to open measurement videos
                this.$emitter.on('open-measurement-videos', this.openVideo);

                // Close modal on escape key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.isVideoOpen) {
                        this.closeVideo();
                    }
                });
            },

            beforeUnmount() {
                this.$emitter?.off('open-measurement-videos', this.openVideo);
            },

            methods: {
                openVideo(gender = null) {
                    this.selectedGender = gender;
                    this.activeTab = gender || 'male';

                    if (gender === 'male' || (!gender && this.activeTab === 'male')) {
                        this.currentVideoSrc = '/videos/ASO CLOTHING MALE.mp4';
                    } else if (gender === 'female' || (!gender && this.activeTab === 'female')) {
                        this.currentVideoSrc = '/videos/ASO CLOTHING FEMALE.mp4';
                    }

                    this.isVideoOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                selectGender(gender) {
                    this.selectedGender = gender;
                    this.activeTab = gender;

                    if (gender === 'male') {
                        this.currentVideoSrc = '/videos/ASO CLOTHING MALE.mp4';
                    } else {
                        this.currentVideoSrc = '/videos/ASO CLOTHING FEMALE.mp4';
                    }
                },

                closeVideo() {
                    this.isVideoOpen = false;
                    this.selectedGender = null;
                    this.currentVideoSrc = '';
                    document.body.style.overflow = 'auto';

                    // Pause video when closing
                    if (this.$refs.videoPlayer) {
                        this.$refs.videoPlayer.pause();
                        this.$refs.videoPlayer.currentTime = 0;
                    }
                }
            }
        });
    </script>
@endPushOnce
