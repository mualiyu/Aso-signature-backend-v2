<x-admin::layouts>
    <x-slot name="title">
        Edit Designer
    </x-slot>
    <form action="{{ route('admin.designers.designers.update', ['id' => $designer->id]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Edit Designer
        </p>

        <div class="flex items-center gap-x-2.5">
            <div class="flex items-center gap-x-2.5">
                <button class="primary-button" type="submit">
                    Save Designer
                </button>
            </div>
        </div>
    </div>

    <div class="grid gap-4 mt-4">
        <div class="flex flex-col gap-2 flex-1 max-xl:flex-auto">
            <div class="box-shadow relative rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white"> General </p>

                <div class="grid grid-cols-2 mb-5">
                    <div class="flex pt-2">
                        <!-- Add Logo -->
                        <div class="flex w-2/5 flex-col gap-2">
                            <p class="font-medium text-gray-800 dark:text-white">
                                @lang('admin::app.catalog.categories.create.logo')
                            </p>

                            <p class="text-xs text-gray-500">
                                @lang('admin::app.catalog.categories.create.logo-size')
                            </p>

                            {{-- <x-admin::media.images name="logo_path" /> --}}
                            <label
                                class="grid h-[120px] max-h-[120px] min-h-[110px] w-full min-w-[110px] max-w-[120px] cursor-pointer items-center justify-items-center rounded border border-dashed border-gray-300 transition-all hover:border-gray-400 dark:border-gray-800 dark:mix-blend-exclusion dark:invert border-gray-300"
                                for="24_imageInput" style="max-width: 120px; max-height: 120px;"
                                onclick="$refs.24_imageInput.click()"
                                >
                                <div class="flex flex-col items-center">
                                    <img src="{{ url('/storage/' .$designer->logo->src) ?? "" }}" alt="Preview" style="position:relative; z-index:1; object-fit:cover; display: {{ $designer->logo->src ? 'block' : 'none' }}; height: 100px" class="w-full top-0  object-cover "
                                        id="24_imagePreview">
                                    <div id="ddff" style="display: {{ $designer->logo->src ? 'none' : 'block' }};">
                                        <span class="icon-image text-2xl ml-5"></span>
                                        <p
                                            class="grid text-center text-sm font-semibold text-gray-600 dark:text-gray-300">
                                            Add Image <span class="text-xs"> png, jpeg, jpg </span>
                                        </p>
                                    </div>
                                    <input type="file" class="hidden" id="24_imageInput" name="logo_path"
                                        accept="image/*" onchange="previewImage(this)"  ref="24_imageInput">

                                    <script>
                                        function previewImage(input) {
                                        var file = input.files[0];
                                        var reader = new FileReader();
                                        reader.onload = function(e) {
                                            var image = document.getElementById("24_imagePreview");
                                            var ddff = document.getElementById("ddff");
                                            image.style.display = "block";
                                            image.src = e.target.result;

                                            ddff.style.display = "none";
                                        };
                                        reader.readAsDataURL(file);
                                    }
                                    </script>
                                </div>
                            </label>
                        </div>

                        <!-- Add Banner -->
                        <div class="flex w-3/5 flex-col gap-2">
                            <p class="font-medium text-gray-800 dark:text-white">
                                @lang('admin::app.catalog.categories.create.banner')
                            </p>

                            <p class="text-xs text-gray-500">
                                @lang('admin::app.catalog.categories.create.banner-size')
                            </p>

                            {{-- <x-admin::media.images name="banner_path" width="220px" /> --}}
                            <label
                            class="grid h-[120px] max-h-[120px] min-h-[110px] w-full min-w-[200px] max-w-[220px] cursor-pointer items-center justify-items-center rounded border border-dashed border-gray-300 transition-all hover:border-gray-400 dark:border-gray-800 dark:mix-blend-exclusion dark:invert border-gray-300"
                            style="max-width: 220px; max-height: 120px;"
                            onclick="$refs.32_imageInput.click()"
                            >
                            <div class="flex flex-col items-center">
                                <img src="{{ url('/storage/' .$designer->banner->src) ?? "" }}" alt="Preview" style="position:relative; z-index:1; object-fit:cover; display: {{ $designer->banner->src ? 'block' : 'none' }}; height: 100px;" class="w-full top-0 object-cover"
                                    id="32_imageInput">
                                <div id="ddfff" style="display: {{ $designer->banner->src ? 'none' : 'block' }};">
                                    <span class="icon-image text-2xl ml-5"></span>
                                    <p
                                        class="grid text-center text-sm font-semibold text-gray-600 dark:text-gray-300">
                                        Add Image <span class="text-xs"> png, jpeg, jpg </span>
                                    </p>
                                </div>
                                <input type="file" class="hidden" id="32_imageInput" name="banner_path"
                                    accept="image/*" onchange="previewImage1(this)" ref="32_imageInput">

                                <script>
                                    function previewImage1(input) {
                                    var file = input.files[0];
                                    var reader = new FileReader();
                                    reader.onload = function(e) {
                                        var image = document.getElementById("32_imageInput");
                                        var ddfff = document.getElementById("ddfff");
                                        image.style.display = "block";
                                        image.src = e.target.result;

                                        ddfff.style.display = "none";
                                    };
                                    reader.readAsDataURL(file);
                                }
                                </script>
                            </div>
                        </label>
                        </div>
                    </div>
                    <div class="mb-4 last:!mb-0">
                        <label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Status<span class="required"></span> </label><select name="status"
                        class="custom-select w-full rounded-md border bg-white mb-2 px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                        id="status">
                        <option value="1" @selected($designer->status == 1)> Active </option>
                        <option value="0" @selected($designer->status == 0)> Inactive </option>
                        </select>
                        <!---->
                        <div><label class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">Name<span class="required"></span></label><input type="text" id="name" class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400" name="name" required value="{{ $designer->name }}"><!----></div>
                        <div><label class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">Email<span class="required"></span></label><input type="email" id="email" class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400" name="email" required value="{{ $designer->email }}"><!----></div>
                </div>
                </div>
                <div class="grid grid-cols-1 gap-4 mb-4 last:!mb-0">
                    <div><label class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">Description<span class="required"></span></label><textarea id="description" class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400" name="description" required value="{{ $designer->description }}"></textarea><!----></div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4 last:!mb-0">
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Phone </label><input type="text" id="phone"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="phone" value="{{ $designer->phone }}">
                    <!---->
                </div>
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Website </label><input type="url" id="website"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="website" value="{{ $designer->website }}">
                    <!---->
                </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4 last:!mb-0">
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Instagram </label><input type="url" id="instagram"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="instagram" value="{{ $designer->instagram }}">
                    <!---->
                </div>
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Facebook </label><input type="url" id="facebook"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="facebook" value="{{ $designer->facebook }}">
                    <!---->
                </div>
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Twitter </label><input type="url" id="twitter"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="twitter" value="{{ $designer->twitter }}">
                    <!---->
                </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4 last:!mb-0">
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Pinterest </label><input type="url" id="pinterest"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="pinterest" value="{{ $designer->pinterest }}">
                    <!---->
                </div>
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        LinkedIn </label><input type="url" id="linkedin"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="linkedin" value="{{ $designer->linkedin }}">
                    <!---->
                </div>
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        YouTube </label><input type="url" id="youtube"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="youtube" value="{{ $designer->youtube }}">
                    <!---->
                </div>
                </div>

                {{-- <div class="grid grid-cols-2 gap-4 mb-4 last:!mb-0">
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Status </label><select name="status"
                        class="custom-select w-full rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                        id="status">
                            <option value="1" @selected($designer->status == 1)> Active </option>
                            <option value="0" @selected($designer->status == 0)> Inactive </option>
                        </select>
                        <!---->
                </div>
                </div> --}}
            </div>
        </div>
    </div>

</form>
</x-admin::layouts>
