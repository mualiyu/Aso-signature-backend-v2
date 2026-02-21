{!! view_render_event('bagisto.shop.products.view.share.before', ['product' => $product]) !!}

<div class="relative">
    <div
        class="flex cursor-pointer items-center justify-center gap-2.5 max-sm:gap-1.5 max-sm:text-base"
        role="button"
        tabindex="0"
        @click="shareProductDropDown()"
    >
        <span
            class="icon-share text-2xl"
            role="presentation"
        ></span>

        Share
    </div>

    <!-- Share Dropdown -->
    <div
        class="absolute top-10 z-10 w-max rounded-lg border border-gray-300 bg-white p-4 shadow-lg"
        v-show="isShareDropdown"
    >
        <div class="flex flex-col gap-3">
            <!-- Facebook Share -->
            <a
                href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                target="_blank"
                class="flex items-center gap-2 text-blue-600 hover:text-blue-800"
            >
                <span class="icon-facebook text-xl"></span>
                Share on Facebook
            </a>

            <!-- Twitter Share -->
            <a
                href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($product->name) }}"
                target="_blank"
                class="flex items-center gap-2 text-blue-400 hover:text-blue-600"
            >
                <span class="icon-twitter text-xl"></span>
                Share on Twitter
            </a>

            <!-- WhatsApp Share -->
            <a
                href="https://wa.me/?text={{ urlencode($product->name . ' - ' . url()->current()) }}"
                target="_blank"
                class="flex items-center gap-2 text-green-600 hover:text-green-800"
            >
                <span class="icon-whatsapp text-xl"></span>
                Share on WhatsApp
            </a>

            <!-- Email Share -->
            <a
                href="mailto:?subject={{ urlencode($product->name) }}&body={{ urlencode('Check out this product: ' . url()->current()) }}"
                class="flex items-center gap-2 text-gray-600 hover:text-gray-800"
            >
                <span class="icon-email text-xl"></span>
                Share via Email
            </a>

            <!-- Copy Link -->
            <div
                class="flex cursor-pointer items-center gap-2 text-gray-600 hover:text-gray-800"
                @click="copyToClipboard('{{ url()->current() }}')"
            >
                <span class="icon-link text-xl"></span>
                Copy Link
            </div>
        </div>
    </div>
</div>

{!! view_render_event('bagisto.shop.products.view.share.after', ['product' => $product]) !!}

