<v-customer-edit
    :customer="customer"
    @update-customer="updateCustomer"
>
    <div class="flex cursor-pointer items-center justify-between gap-1.5 px-2.5 text-blue-600 transition-all hover:underline"></div>
</v-customer-edit>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-customer-edit-template"
    >
        <!-- Customer Edit Button -->
        @if (bouncer()->hasPermission('customers.customers.edit'))
            <div 
                class="flex cursor-pointer items-center justify-between gap-1.5 px-2.5 text-blue-600 transition-all hover:underline"
                @click="$refs.customerEditModal.toggle()"
            >
                @lang('admin::app.customers.customers.view.edit.edit-btn')
            </div>
        @endif

        {!! view_render_event('bagisto.admin.customers.customers.view.edit.edit_form_controls.before', ['customer' => $customer]) !!}

        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form
                @submit="handleSubmit($event, edit)"
                ref="customerEditForm"
            >
                <!-- Customer Edit Modal -->
                <x-admin::modal ref="customerEditModal">
                    <!-- Modal Header -->
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('admin::app.customers.customers.view.edit.title')
                        </p>    
                    </x-slot>
    
                    <!-- Modal Content -->
                    <x-slot:content>
                        {!! view_render_event('bagisto.admin.customers.customers.view.edit.before', ['customer' => $customer]) !!}

                        <div class="flex gap-4 max-sm:flex-wrap">
                            <!--First Name -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.customers.customers.view.edit.first-name')
                                </x-admin::form.control-group.label>
            
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="first_name" 
                                    id="first_name" 
                                    ::value="customer.first_name"
                                    rules="required"
                                    :label="trans('admin::app.customers.customers.view.edit.first-name')"
                                    :placeholder="trans('admin::app.customers.customers.view.edit.first-name')"
                                />
            
                                <x-admin::form.control-group.error control-name="first_name" />
                            </x-admin::form.control-group>
            
                            <!--Last Name -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.customers.customers.view.edit.last-name')
                                </x-admin::form.control-group.label>
            
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="last_name" 
                                    ::value="customer.last_name"
                                    id="last_name"
                                    rules="required"
                                    :label="trans('admin::app.customers.customers.view.edit.last-name')"
                                    :placeholder="trans('admin::app.customers.customers.view.edit.last-name')"
                                />
            
                                <x-admin::form.control-group.error control-name="last_name" />
                            </x-admin::form.control-group>
                        </div>
            
                        <!-- Email -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.customers.customers.view.edit.email')
                            </x-admin::form.control-group.label>
            
                            <x-admin::form.control-group.control
                                type="email"
                                name="email"
                                ::value="customer.email"
                                id="email"
                                rules="required|email"
                                :label="trans('admin::app.customers.customers.view.edit.email')"
                                placeholder="email@example.com"
                            />
            
                            <x-admin::form.control-group.error control-name="email" />
                        </x-admin::form.control-group>
            
                        <div class="flex gap-4 max-sm:flex-wrap">
                            <!-- Phone -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.customers.customers.view.edit.contact-number')
                                </x-admin::form.control-group.label>
            
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="phone"
                                    ::value="customer.phone"
                                    id="phone"
                                    rules="phone"
                                    :label="trans('admin::app.customers.customers.view.edit.contact-number')"
                                    :placeholder="trans('admin::app.customers.customers.view.edit.contact-number')"
                                />
            
                                <x-admin::form.control-group.error control-name="phone" />
                            </x-admin::form.control-group>
            
                            <!-- Date -->
                            <x-admin::form.control-group class="mb-2.5 w-full">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.customers.customers.view.edit.date-of-birth')
                                </x-admin::form.control-group.label>
            
                                <x-admin::form.control-group.control
                                    type="date"
                                    name="date_of_birth" 
                                    id="dob"
                                    ::value="customer.date_of_birth"
                                    :label="trans('admin::app.customers.customers.view.edit.date-of-birth')"
                                    :placeholder="trans('admin::app.customers.customers.view.edit.date-of-birth')"
                                />
                                
                                <x-admin::form.control-group.error control-name="date_of_birth" />
                            </x-admin::form.control-group>
                        </div>

                        <div class="flex gap-4 max-sm:flex-wrap">
                            <!-- Gender -->
                            <x-admin::form.control-group class="w-full">
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.customers.customers.view.edit.gender')
                                </x-admin::form.control-group.label>
            
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="gender"
                                    ::value="customer.gender"
                                    id="gender"
                                    rules="required"
                                    :label="trans('admin::app.customers.customers.view.edit.gender')"
                                >
                                    <option value="Male">
                                        @lang('admin::app.customers.customers.view.edit.male')
                                    </option>
            
                                    <option value="Female">
                                        @lang('admin::app.customers.customers.view.edit.female')
                                    </option>
            
                                    <option value="Other">
                                        @lang('admin::app.customers.customers.view.edit.other')
                                    </option>
                                </x-admin::form.control-group.control>
            
                                <x-admin::form.control-group.error control-name="gender" />
                            </x-admin::form.control-group>
            
                            <!-- Customer Group -->
                            <x-admin::form.control-group class="w-full">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.customers.customers.view.edit.customer-group')
                                </x-admin::form.control-group.label>
            
                                <x-admin::form.control-group.control
                                    type="select"
                                    name="customer_group_id"
                                    ::value="customer.customer_group_id"
                                    id="customerGroup" 
                                    :label="trans('admin::app.customers.customers.view.edit.customer-group')"
                                >
                                    <option
                                        v-for="group in groups" 
                                        :value="group.id"
                                    > 
                                        @{{ group.name }} 
                                    </option>
                                </x-admin::form.control-group.control>
                            </x-admin::form.control-group>
                        </div>
            
                        <div class="flex gap-60 max-sm:flex-wrap">
                            <!-- Customer Status -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.marketing.promotions.cart-rules.edit.status')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="hidden"
                                    name="status"
                                    value="0"
                                />
                                
                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="status"
                                    :value="1"
                                    :label="trans('admin::app.marketing.promotions.cart-rules.edit.status')"
                                    ::checked="customer.status"
                                />
                            </x-admin::form.control-group>

                            <!-- Customer Suspended Status -->
                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.customers.customers.view.edit.suspended')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="hidden"
                                    name="is_suspended"
                                    value="0"
                                />
                                
                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="is_suspended"
                                    :value="1"
                                    :label="trans('admin::app.customers.customers.view.edit.suspended')"
                                    ::checked="customer.is_suspended"
                                />
                            </x-admin::form.control-group>`
                        </div>


                        <hr>
                        
                        <!-- Measurements Card -->
                        <div class="flex flex-col gap-4">
                            <x-admin::form.control-group class="w-full">
                                <x-admin::form.control-group.label>
                                    @lang('Body Measurements')
                                </x-admin::form.control-group.label>

                                <div class="grid grid-cols-3 gap-4">
                                    <!-- Upper Body Measurements -->
                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Neck</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="neck"
                                            value="16"
                                            label="Neck"
                                            placeholder="Neck (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Shoulder Width</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="shoulder_width"
                                            value="18"
                                            label="Shoulder Width"
                                            placeholder="Shoulder Width (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Chest</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="chest"
                                            value="40"
                                            label="Chest"
                                            placeholder="Chest (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Bust</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="bust"
                                            value="38"
                                            label="Bust"
                                            placeholder="Bust (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Arm Circumference</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="arm_circumference"
                                            value="14"
                                            label="Arm Circumference"
                                            placeholder="Arm Circumference (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Sleeve Length</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="sleeve_length"
                                            value="25"
                                            label="Sleeve Length"
                                            placeholder="Sleeve Length (inches)"
                                        />
                                    </div>

                                    <!-- Torso Measurements -->
                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Back Width</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="back_width"
                                            value="16"
                                            label="Back Width"
                                            placeholder="Back Width (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Back Length</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="back_length"
                                            value="27"
                                            label="Back Length"
                                            placeholder="Back Length (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Front Length</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="front_length"
                                            value="26"
                                            label="Front Length"
                                            placeholder="Front Length (inches)"
                                        />
                                    </div>

                                    <!-- Lower Body Measurements -->
                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Waist</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="waist"
                                            value="34"
                                            label="Waist"
                                            placeholder="Waist (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Hip</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="hip"
                                            value="40"
                                            label="Hip"
                                            placeholder="Hip (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Inseam</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="inseam"
                                            value="32"
                                            label="Inseam"
                                            placeholder="Inseam (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Outseam</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="outseam"
                                            value="42"
                                            label="Outseam"
                                            placeholder="Outseam (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Thigh</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="thigh"
                                            value="22"
                                            label="Thigh"
                                            placeholder="Thigh (inches)"
                                        />
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="mb-1 text-sm text-gray-800 dark:text-white">Knee</label>
                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="knee"
                                            value="15"
                                            label="Knee"
                                            placeholder="Knee (inches)"
                                        />
                                    </div>
                                </div>
                            </x-admin::form.control-group>
                        </div>

                        <hr>
                        {!! view_render_event('bagisto.admin.customers.customers.view.edit.after', ['customer' => $customer]) !!}
                    </x-slot>

                    <!-- Modal Footer -->
                    <x-slot:footer>
                        <x-admin::button
                            button-type="submit"
                            class="primary-button justify-center"
                            :title="trans('admin::app.customers.customers.view.edit.save-btn')"
                            ::loading="isUpdating"
                            ::disabled="isUpdating"
                        />
                    </x-slot>
                </x-admin::modal>
            </form>
        </x-admin::form>

        {!! view_render_event('bagisto.admin.customers.customers.view.edit.edit_form_controls.after', ['customer' => $customer]) !!}
    </script>

    <script type="module">
        app.component('v-customer-edit', {
            template: '#v-customer-edit-template',

            props: ['customer'],

            emits: ['update-customer'],

            data() {
                return {
                    groups: @json($groups),

                    isUpdating: false,
                };
            },

            methods: {
                edit(params, {resetForm, setErrors}) {
                    this.isUpdating = true;

                    let formData = new FormData(this.$refs.customerEditForm);

                    formData.append('_method', 'put');

                    this.$axios.post('{{ route('admin.customers.customers.update', $customer->id) }}', formData)
                        .then((response) => {
                            this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                            this.$emit('update-customer', response.data.data);

                            resetForm();

                            this.isUpdating = false;

                            this.$refs.customerEditModal.close();
                        })
                        .catch(error => {
                            this.isUpdating = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                },
            }
        })
    </script>
@endPushOnce
