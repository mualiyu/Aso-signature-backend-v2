{{--
    Save button that knows about background media uploads.

    While images or videos are still travelling to the server the button is
    disabled, a form submit is blocked with a warning, and leaving the page
    asks for confirmation. The media components report their state through
    the `media-upload-state` emitter event.
--}}
<v-product-save-button>
    <button class="primary-button">
        @lang('admin::app.catalog.products.edit.save-btn')
    </button>
</v-product-save-button>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-product-save-button-template"
    >
        <button
            type="submit"
            class="primary-button"
            :class="{ 'cursor-not-allowed opacity-60': active > 0 }"
            :disabled="active > 0"
            :title="active > 0 ? '@lang('admin::app.catalog.products.edit.uploads-in-progress')' : ''"
        >
            <template v-if="active > 0">
                @lang('admin::app.catalog.products.edit.save-btn-uploading') (@{{ active }})
            </template>

            <template v-else>
                @lang('admin::app.catalog.products.edit.save-btn')
            </template>
        </button>
    </script>

    <script type="module">
        app.component('v-product-save-button', {
            template: '#v-product-save-button-template',

            data() {
                return {
                    sources: {},
                };
            },

            computed: {
                active() {
                    return Object.values(this.sources).reduce((sum, count) => sum + count, 0);
                },
            },

            mounted() {
                this.$emitter.on('media-upload-state', this.onUploadState);

                this.form = this.$el.closest('form');

                if (this.form) {
                    this.form.addEventListener('submit', this.guardSubmit, true);
                }

                window.addEventListener('beforeunload', this.guardUnload);
            },

            beforeUnmount() {
                this.$emitter.off('media-upload-state', this.onUploadState);

                if (this.form) {
                    this.form.removeEventListener('submit', this.guardSubmit, true);
                }

                window.removeEventListener('beforeunload', this.guardUnload);
            },

            methods: {
                onUploadState({ source, active }) {
                    this.sources = { ...this.sources, [source]: active };
                },

                guardSubmit(event) {
                    if (this.active === 0) {
                        return;
                    }

                    event.preventDefault();

                    event.stopImmediatePropagation();

                    this.$emitter.emit('add-flash', {
                        type: 'warning',
                        message: "@lang('admin::app.catalog.products.edit.uploads-in-progress')",
                    });
                },

                guardUnload(event) {
                    if (this.active === 0) {
                        return;
                    }

                    event.preventDefault();

                    event.returnValue = "@lang('admin::app.catalog.products.edit.uploads-leave-warning')";

                    return event.returnValue;
                },
            },
        });
    </script>
@endPushOnce
