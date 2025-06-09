@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-create-designer-form-template"
    >
        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form @submit="handleSubmit($event, create)">
                <!-- Designer Create Modal -->
                <x-admin::modal ref="designerCreateModal">
                    <!-- Modal Header -->
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            Create Designer
                        </p>
                    </x-slot>

                    <!-- Modal Content -->
                    <x-slot:content>
                        <div class="flex gap-4 max-sm:flex-wrap">
                            <!-- Full Name -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    Full Name
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    rules="required"
                                    label="Full Name"
                                    placeholder="Enter full name"
                                />

                                <x-admin::form.control-group.error control-name="full_name" />
                            </x-admin::form.control-group>

                            <!-- Slug -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    Slug
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    id="slug"
                                    name="slug"
                                    rules="required"
                                    label="Slug"
                                    placeholder="Enter slug"
                                />

                                <x-admin::form.control-group.error control-name="slug" />
                            </x-admin::form.control-group>
                        </div>

                        <!-- Email -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                Email
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="email"
                                id="email"
                                name="email"
                                rules="required|email"
                                label="Email"
                                placeholder="email@example.com"
                            />

                            <x-admin::form.control-group.error control-name="email" />
                        </x-admin::form.control-group>

                        <!-- Phone -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Phone
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                id="phone"
                                name="phone"
                                rules="phone"
                                label="Phone"
                                placeholder="Enter phone number"
                            />

                            <x-admin::form.control-group.error control-name="phone" />
                        </x-admin::form.control-group>

                        <!-- Bio -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Bio
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                id="bio"
                                name="bio"
                                label="Bio"
                                placeholder="Enter bio"
                            />

                            <x-admin::form.control-group.error control-name="bio" />
                        </x-admin::form.control-group>

                        <!-- Image -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Image
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="file"
                                id="image"
                                name="image"
                                label="Image"
                            />

                            <x-admin::form.control-group.error control-name="image" />
                        </x-admin::form.control-group>

                        <!-- Website -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                Website
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="url"
                                id="website"
                                name="website"
                                label="Website"
                                placeholder="Enter website URL"
                            />

                            <x-admin::form.control-group.error control-name="website" />
                        </x-admin::form.control-group>

                        <!-- Social Media Links -->
                        <div class="flex gap-4 max-sm:flex-wrap">
                            <!-- Facebook -->
                            <x-admin::form.control-group class="w-full">
                                <x-admin::form.control-group.label>
                                    Facebook
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="url"
                                    id="facebook"
                                    name="facebook"
                                    label="Facebook"
                                    placeholder="Enter Facebook URL"
                                />

                                <x-admin::form.control-group.error control-name="facebook" />
                            </x-admin::form.control-group>

                            <!-- X -->
                            <x-admin::form.control-group class="w-full">
                                <x-admin::form.control-group.label>
                                    X (Twitter)
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="url"
                                    id="x"
                                    name="x"
                                    label="X"
                                    placeholder="Enter X URL"
                                />

                                <x-admin::form.control-group.error control-name="x" />
                            </x-admin::form.control-group>
                        </div>

                        <div class="flex gap-4 max-sm:flex-wrap">
                            <!-- LinkedIn -->
                            <x-admin::form.control-group class="w-full">
                                <x-admin::form.control-group.label>
                                    LinkedIn
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="url"
                                    id="linkedin"
                                    name="linkedin"
                                    label="LinkedIn"
                                    placeholder="Enter LinkedIn URL"
                                />

                                <x-admin::form.control-group.error control-name="linkedin" />
                            </x-admin::form.control-group>

                            <!-- Instagram -->
                            <x-admin::form.control-group class="w-full">
                                <x-admin::form.control-group.label>
                                    Instagram
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="url"
                                    id="instagram"
                                    name="instagram"
                                    label="Instagram"
                                    placeholder="Enter Instagram URL"
                                />

                                <x-admin::form.control-group.error control-name="instagram" />
                            </x-admin::form.control-group>
                        </div>

                        <!-- YouTube -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                YouTube
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="url"
                                id="youtube"
                                name="youtube"
                                label="YouTube"
                                placeholder="Enter YouTube URL"
                            />

                            <x-admin::form.control-group.error control-name="youtube" />
                        </x-admin::form.control-group>
                    </x-slot>

                    <!-- Modal Footer -->
                    <x-slot:footer>
                        <!-- Modal Submission -->
                        <div class="flex items-center gap-x-2.5">
                            <!-- Save Button -->
                            <x-admin::button
                                button-type="submit"
                                class="primary-button justify-center"
                                title="Save Designer"
                                ::loading="isStoring"
                                ::disabled="isStoring"
                            />
                        </div>
                    </x-slot>
                </x-admin::modal>
            </form>
        </x-admin::form>
    </script>

    <script type="module">
        app.component('v-create-designer-form', {
            template: '#v-create-designer-form-template',

            data() {
                return {
                    isStoring: false,
                };
            },

            methods: {
                openModal() {
                    this.$refs.designerCreateModal.open();
                },

                create(params, { resetForm, setErrors }) {
                    this.isStoring = true;

                    this.$axios.post("{{ route('admin.designers.designers.store') }}", params)
                        .then((response) => {
                            this.$refs.designerCreateModal.close();

                            this.$emit('designer-created', response.data.data);

                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            resetForm();

                            this.isStoring = false;
                        })
                        .catch(error => {
                            console.log(error);
                            
                            this.isStoring = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                }
            }
        })
    </script>
@endPushOnce