<x-admin::layouts>
    <x-slot name="title">
        Create Designer
    </x-slot>
    <form action="{{ route('admin.designers.designers.store') }}" method="POST">
        @csrf

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Create New Designer
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

                <div class="grid grid-cols-2 gap-4 mb-4 last:!mb-0">
                    <div><label class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">Name<span class="required"></span></label><input type="text" id="name" class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400" name="name" required value=""><!----></div>
                    <div><label class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">Email<span class="required"></span></label><input type="email" id="email" class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400" name="email" required value=""><!----></div>
                </div>
                <div class="grid grid-cols-1 gap-4 mb-4 last:!mb-0">
                    <div><label class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">Description<span class="required"></span></label><textarea id="description" class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400" name="description" required value=""></textarea><!----></div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4 last:!mb-0">
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Phone </label><input type="text" id="phone"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="phone" value="">
                    <!---->
                </div>
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Website </label><input type="url" id="website"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="website" value="">
                    <!---->
                </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4 last:!mb-0">
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Instagram </label><input type="url" id="instagram"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="instagram" value="">
                    <!---->
                </div>
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Facebook </label><input type="url" id="facebook"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="facebook" value="">
                    <!---->
                </div>
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Twitter </label><input type="url" id="twitter"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="twitter" value="">
                    <!---->
                </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mb-4 last:!mb-0">
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Pinterest </label><input type="url" id="pinterest"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="pinterest" value="">
                    <!---->
                </div>
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        LinkedIn </label><input type="url" id="linkedin"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="linkedin" value="">
                    <!---->
                </div>
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        YouTube </label><input type="url" id="youtube"
                        class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                        name="youtube" value="">
                    <!---->
                </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4 last:!mb-0">
                <div class="mb-4 last:!mb-0"><label
                        class="mb-1.5 flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-white">
                        Status </label><select name="status"
                        class="custom-select w-full rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400"
                        id="status">
                            <option value="1"> Active </option>
                            <option value="0"> Inactive </option>
                        </select>
                        <!---->
                </div>
                </div>
            </div>
        </div>
    </div>

</form>
</x-admin::layouts>
