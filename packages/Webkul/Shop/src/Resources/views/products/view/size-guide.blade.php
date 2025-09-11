{!! view_render_event('bagisto.shop.products.view.size_guide.before', ['product' => $product]) !!}

<v-size-guide>
    <div
        class="flex cursor-pointer items-center justify-center gap-2.5 max-sm:gap-1.5 max-sm:text-base"
        role="button"
        tabindex="0"
        @click="openSizeGuide()"
    >
        <span
            class="icon-ruler text-2xl"
            role="presentation"
        ></span>

        Size Guide
    </div>
</v-size-guide>

{!! view_render_event('bagisto.shop.products.view.size_guide.after', ['product' => $product]) !!}

@pushOnce('scripts')
    <!-- Size Guide Template -->
    <script
        type="text/x-template"
        id="v-size-guide-template"
    >
        <div>
            <!-- Size Guide Trigger -->
            <div
                class="flex cursor-pointer items-center justify-center gap-2.5 max-sm:gap-1.5 max-sm:text-base"
                role="button"
                tabindex="0"
                @click="openSizeGuide()"
            >
                <span role="presentation" class="text-2xl">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="1em" height="1em">
                        <rect x="2" y="6" width="20" height="4" rx="1" fill="#4b2462"/>
                        <rect x="2" y="14" width="20" height="4" rx="1" fill="#4b2462"/>
                        <rect x="4" y="8" width="1" height="8" fill="#fff"/>
                        <rect x="7" y="8" width="1" height="8" fill="#fff"/>
                        <rect x="10" y="8" width="1" height="8" fill="#fff"/>
                        <rect x="13" y="8" width="1" height="8" fill="#fff"/>
                        <rect x="16" y="8" width="1" height="8" fill="#fff"/>
                        <rect x="19" y="8" width="1" height="8" fill="#fff"/>
                    </svg>
                </span>

                Size Guide

                {{-- close icon --}}
                <span class="icon-cross text-2xl"></span>
            </div>

            <!-- Size Guide Modal -->
            <div
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                v-show="isSizeGuideOpen"
                @click.self="closeSizeGuide()"
            >
                <div class="relative max-h-[90vh] w-[95vw] sm:w-[70%] max-w-4xl overflow-y-auto rounded-lg bg-white p-2 sm:p-6 shadow-lg"
                     style="max-width: 98vw;">
                    <!-- Modal Header -->
                    <div class="mb-4 sm:mb-6 flex items-center justify-between border-b pb-2 sm:pb-4">
                        <h2 class="text-lg sm:text-2xl font-semibold text-gray-800">Size Guide</h2>

                        <button
                            class="text-gray-500 hover:text-gray-700"
                            @click="closeSizeGuide()"
                        >
                            <span class="icon-cross text-2xl"></span>
                        </button>
                    </div>

                    <!-- Tabs -->
                    <div class="mb-4 sm:mb-6">
                        <div class="flex flex-wrap border-b">
                            <a
                                class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-sm font-medium transition-colors duration-200"
                                style="cursor: pointer;"
                                :class="activeTab === 'male' ? 'border-b-2 border-blue-500 text-[#4b2462]-600' : 'text-gray-500 hover:text-gray-700'"
                                @click="activeTab = 'male'"
                            >
                                Male
                            </a>

                            <a
                                class="px-3 sm:px-6 py-2 sm:py-3 text-xs sm:text-sm font-medium transition-colors duration-200"
                                style="cursor: pointer;"
                                :class="activeTab === 'female' ? 'border-b-2 border-blue-500 text-[#4b2462]-600' : 'text-gray-500 hover:text-gray-700'"
                                @click="activeTab = 'female'"
                            >
                                Female
                            </a>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Male Size Guide -->
                        <div v-show="activeTab === 'male'" class="space-y-4 sm:space-y-6">
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[400px] border-collapse border border-gray-300 text-xs sm:text-base">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2 text-left font-semibold">Size</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2 text-left font-semibold">Chest (inches)</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2 text-left font-semibold">Waist (inches)</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2 text-left font-semibold">Hip (inches)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">XS</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">34-36</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">28-30</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">34-36</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">S</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">36-38</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">30-32</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">36-38</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">M</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">38-40</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">32-34</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">38-40</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">L</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">40-42</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">34-36</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">40-42</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">XL</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">42-44</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">36-38</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">42-44</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">XXL</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">44-46</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">38-40</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">44-46</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="rounded-lg bg-blue-50 p-2 sm:p-4">
                                <h3 class="mb-1 sm:mb-2 font-semibold text-[#4b2462]-800">How to Measure (Male)</h3>
                                <ul class="space-y-1 text-xs sm:text-sm text-[#4b2462]-700">
                                    <li><strong>Chest:</strong> Measure around the fullest part of your chest, keeping the tape horizontal.</li>
                                    <li><strong>Waist:</strong> Measure around your natural waistline, keeping the tape comfortably loose.</li>
                                    <li><strong>Hip:</strong> Measure around the fullest part of your hips, keeping the tape horizontal.</li>
                                </ul>
                            </div>

                            <!-- Watch Video Button for Male -->
                            <div class="text-center">
                                <a
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-2 sm:px-6 py-2 sm:py-3 text-[#ffffff] hover:bg-blue-700 transition-colors duration-200 text-xs sm:text-base"
                                    style="background-color: #4b2462; cursor: pointer;"
                                    @click="openVideo('male')"
                                >
                                    <span class="icon-play text-lg text-[#ffffff]"></span>
                                    <span>Watch Measurement Video</span>
                                </a>
                            </div>
                        </div>

                        <!-- Female Size Guide -->
                        <div v-show="activeTab === 'female'" class="space-y-4 sm:space-y-6">
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[400px] border-collapse border border-gray-300 text-xs sm:text-base">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2 text-left font-semibold">Size</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2 text-left font-semibold">Bust (inches)</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2 text-left font-semibold">Waist (inches)</th>
                                            <th class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2 text-left font-semibold">Hip (inches)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">XS</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">32-34</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">24-26</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">34-36</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">S</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">34-36</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">26-28</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">36-38</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">M</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">36-38</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">28-30</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">38-40</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">L</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">38-40</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">30-32</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">40-42</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">XL</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">40-42</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">32-34</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">42-44</td>
                                        </tr>
                                        <tr class="bg-gray-50">
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">XXL</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">42-44</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">34-36</td>
                                            <td class="border border-gray-300 px-2 sm:px-4 py-1 sm:py-2">44-46</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="rounded-lg bg-pink-50 p-2 sm:p-4">
                                <h3 class="mb-1 sm:mb-2 font-semibold text-[#4b2462]-800">How to Measure (Female)</h3>
                                <ul class="space-y-1 text-xs sm:text-sm text-[#4b2462]-700">
                                    <li><strong>Bust:</strong> Measure around the fullest part of your bust, keeping the tape horizontal.</li>
                                    <li><strong>Waist:</strong> Measure around your natural waistline, the narrowest part of your torso.</li>
                                    <li><strong>Hip:</strong> Measure around the fullest part of your hips, about 8 inches below your waist.</li>
                                </ul>
                            </div>

                            <!-- Watch Video Button for Female -->
                            <div class="text-center">
                                <a
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-2 sm:px-6 py-2 sm:py-3 text-[#ffffff] hover:bg-blue-700 transition-colors duration-200 text-xs sm:text-base"
                                    style="background-color: #4b2462; cursor: pointer;"
                                    @click="openVideo('female')"
                                >
                                <span class="icon-play text-lg text-[#ffffff]"></span>
                                <span>Watch Measurement Video</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Video Modal -->
            <div
                class="fixed inset-0 z-10 flex items-center justify-center bg-black bg-opacity-35"
                v-show="isVideoOpen"
                @click.self="closeVideo()"
            >
                <div class="relative w-[95vw] sm:w-[30vw] max-w-lg:w-[30%] max-w-md:w-[100%] max-sm:w-[100%] max-sm:max-w-sm rounded-lg bg-black shadow-lg"
                     style="max-width: 98vw;">
                    <!-- Video Modal Header -->
                    <div class="absolute right-2 top-2 z-10">
                        <button
                            class="text-white hover:text-gray-300"
                            @click="closeVideo()"
                        >
                            <span class="icon-cross text-2xl"></span>
                        </button>
                    </div>

                    <!-- Video Player -->
                    <video
                        ref="videoPlayer"
                        class="h-auto w-full rounded-lg"
                        controls
                        autoplay
                        preload="metadata"
                        :src="currentVideoSrc"
                        style="max-height:60vh;"
                    >
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>
    </script>

    <style>
        @media (max-width: 640px) {
            .v-size-guide-modal {
                padding: 0.5rem !important;
            }
            .v-size-guide-table th,
            .v-size-guide-table td {
                padding-left: 0.25rem !important;
                padding-right: 0.25rem !important;
                padding-top: 0.25rem !important;
                padding-bottom: 0.25rem !important;
                font-size: 0.75rem !important;
            }
        }
    </style>

    <script type="module">
        app.component('v-size-guide', {
            template: '#v-size-guide-template',

            data() {
                return {
                    isSizeGuideOpen: false,
                    isVideoOpen: false,
                    activeTab: 'male',
                    currentVideoSrc: ''
                }
            },

            methods: {
                openSizeGuide() {
                    this.isSizeGuideOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                closeSizeGuide() {
                    this.isSizeGuideOpen = false;
                    document.body.style.overflow = 'auto';
                },

                openVideo(gender) {
                    if (gender === 'male') {
                        this.currentVideoSrc = '/videos/ASO CLOTHING MALE.mp4';
                    } else {
                        this.currentVideoSrc = '/videos/ASO CLOTHING FEMALE.mp4';
                    }
                    this.isVideoOpen = true;
                },

                closeVideo() {
                    this.isVideoOpen = false;
                    this.currentVideoSrc = '';
                    // Pause video when closing
                    if (this.$refs.videoPlayer) {
                        this.$refs.videoPlayer.pause();
                        this.$refs.videoPlayer.currentTime = 0;
                    }
                }
            },

            mounted() {
                // Close modal on escape key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        if (this.isVideoOpen) {
                            this.closeVideo();
                        } else if (this.isSizeGuideOpen) {
                            this.closeSizeGuide();
                        }
                    }
                });
            }
        });
    </script>
@endPushOnce
