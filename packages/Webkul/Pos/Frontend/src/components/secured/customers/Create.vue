<template>
    <div class="flex gap-4">
        <div class="my-4 flex w-full flex-col gap-4">
            <div class="grid gap-4 xl:w-[738px] 2xl:w-[828px]">
                <div class="flex items-center justify-between gap-2.5">                    
                    <p class="text-2xl font-semibold">
                        {{ $t('pos.customers.create.title') }}
                    </p>

                    <div
                        class="flex w-max cursor-pointer items-center"
                        @click="$router.go(-1)"
                    >
                        <span class="icon-chevron-left text-2xl text-primary"></span>

                        <span class="text-base font-semibold leading-5 text-primary">
                            {{ $t('pos.customers.create.back_btn_title') }}
                        </span>
                    </div>
                </div>

                <v-form v-slot="{ handleSubmit }">
                    <form
                        class="md:box-shadow grid gap-6 rounded-lg bg-white max-sm:py-4 md:p-4"
                        @submit="handleSubmit($event, submitForm)"
                    >
                        <div class="grid items-start gap-2.5 max-sm:gap-6 md:grid-cols-2">
                            <div class="grid content-start gap-1">
                                <label
                                    for="first_name"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.customers.create.first_name') }}
                                </label>

                                <v-field
                                    type="text"
                                    name="first_name"
                                    id="first_name"
                                    class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                    rules="required"
                                    :label="$t('pos.customers.create.first_name')"
                                    :placeholder="$t('pos.customers.create.first_name')"
                                    v-model="form.firstName"
                                />

                                <v-error-message
                                    name="first_name"
                                    class="text-sm text-red-500"
                                />
                            </div>

                            <div class="grid content-center gap-1">
                                <label
                                    for="last_name"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.customers.create.last_name') }}
                                </label>

                                <v-field
                                    type="text"
                                    name="last_name"
                                    id="last_name"
                                    class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                    rules="required"
                                    :label="$t('pos.customers.create.last_name')"
                                    :placeholder="$t('pos.customers.create.last_name')"
                                    v-model="form.lastName"
                                />

                                <v-error-message
                                    name="last_name"
                                    class="text-sm text-red-500"
                                />
                            </div>
                        </div>

                        <div class="grid items-start gap-2.5 max-sm:gap-6 md:grid-cols-2">
                            <div class="grid content-start gap-1">
                                <label
                                    for="phone"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.customers.create.phone_number') }}
                                </label>

                                <v-field
                                    type="text"
                                    name="phone"
                                    id="phone"
                                    class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                    rules="required|phone"
                                    :label="$t('pos.customers.create.phone_number')"
                                    :placeholder="$t('pos.customers.create.phone_number')"
                                    v-model="form.phone"
                                />

                                <v-error-message
                                    name="phone"
                                    class="text-sm text-red-500"
                                />
                            </div>

                            <div class="grid content-center gap-1">
                                <label
                                    for="email"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.customers.create.email') }}
                                </label>

                                <v-field
                                    type="email"
                                    name="email"
                                    id="email"
                                    class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                    rules="required|email"
                                    :placeholder="$t('pos.customers.create.email')"
                                    v-model="form.email"
                                />

                                <v-error-message
                                    name="email"
                                    class="text-sm text-red-500"
                                />
                            </div>
                        </div>

                        <div class="grid gap-1">
                            <label
                                for="address"
                                class="text-base font-semibold leading-5 text-dark"
                            >
                                {{ $t('pos.customers.create.address') }}
                            </label>

                            <v-field
                                type="text"
                                name="address[]"
                                id="address"
                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                rules="required|address"
                                :placeholder="$t('pos.customers.create.address')"
                                v-model="form.address"
                            />

                            <v-error-message
                                name="address"
                                class="text-sm text-red-500"
                            />
                        </div>

                        <div class="grid items-start gap-2.5 max-sm:gap-6 md:grid-cols-2">
                            <div class="grid content-start gap-1">
                                <label
                                    for="country"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.customers.create.country') }}
                                </label>

                                <v-field
                                    as="select"
                                    name="country"
                                    id="country"
                                    class="custom-select text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish bg-white px-2.5 py-3 transition-all"
                                    rules="required"
                                    v-model="form.country"
                                >
                                    <option value="">
                                        {{ $t('pos.customers.create.select_country') }}
                                    </option>

                                    <option
                                        v-for="(country, index) in countries"
                                        :key="index"
                                        :value="country.code"
                                    >
                                        {{ country.name }}
                                    </option>
                                </v-field>

                                <v-error-message
                                    name="country"
                                    class="text-sm text-red-500"
                                />
                            </div>

                            <div class="grid content-center gap-1">
                                <label
                                    for="state"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.customers.create.state') }}
                                </label>

                                <template v-if="currentCountryStates?.length">
                                    <v-field
                                        as="select"
                                        name="state"
                                        id="state"
                                        class="custom-select text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish bg-white px-2.5 py-3 transition-all"
                                        rules="required"
                                        v-model="form.state"
                                    >
                                        <option value="">
                                            {{ $t('pos.customers.create.select_state') }}
                                        </option>

                                        <option
                                            v-for="(state, index) in currentCountryStates"
                                            :key="index"
                                            :value="state.code"
                                        >
                                            {{ state.defaultName }}
                                        </option>
                                    </v-field>
                                </template>

                                <template v-else>
                                    <v-field
                                        type="text"
                                        name="state"
                                        id="state"
                                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                        rules="required"
                                        :placeholder="$t('pos.customers.create.state')"
                                        v-model="form.state"
                                    />
                                </template>

                                <v-error-message
                                    name="state"
                                    class="text-sm text-red-500"
                                />
                            </div>
                        </div>

                        <div class="grid items-start gap-2.5 max-sm:gap-6 md:grid-cols-2">
                            <div class="grid content-start gap-1">
                                <label
                                    for="city"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.customers.create.city') }}
                                </label>

                                <v-field
                                    type="text"
                                    name="city"
                                    id="city"
                                    class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                    rules="required"
                                    :label="$t('pos.customers.create.city')"
                                    :placeholder="$t('pos.customers.create.city')"
                                    v-model="form.city"
                                />

                                <v-error-message
                                    name="city"
                                    class="text-sm text-red-500"
                                />
                            </div>

                            <div class="grid content-center gap-1">
                                <label
                                    for="postcode"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.customers.create.pin_code') }}
                                </label>

                                <v-field
                                    type="text"
                                    name="postcode"
                                    id="postcode"
                                    class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                    rules="required"
                                    :label="$t('pos.customers.create.pin_code')"
                                    :placeholder="$t('pos.customers.create.pin_code')"
                                    v-model="form.postcode"
                                />

                                <v-error-message
                                    name="postcode"
                                    class="text-sm text-red-500"
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="primary-button w-full px-7 py-4 md:w-[280px]"
                        >
                            <span class="icon-plus text-2xl"></span>

                            {{ $t('pos.customers.create.save_btn_title') }}
                        </button>
                    </form>
                </v-form>
            </div>
        </div>

        <cart v-if="! isMobileOrTab" />
    </div>
</template>

<script setup>
    import { useI18n } from 'vue-i18n';
    import { reactive, watchEffect, getCurrentInstance, ref, watch, toRaw, inject, computed } from 'vue';
    import { useMutation } from '@vue/apollo-composable';
    import { CREATE } from '@src/graphql/customers';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { useWindowWidth } from '@src/composable/window';
    import { useRouter } from 'vue-router';


    /**
     * General use variables
     */
    const { t } = useI18n();
    const DB = useIndexedDB();
    const emitter = inject('emitter');
    const router = useRouter();
    const { appContext } = getCurrentInstance();
    const isOnline = computed(() => appContext.config.globalProperties.$isOnline.value);
    const { isMobileOrTab } = useWindowWidth();

    /**
     * Fetch countries and states
     */
    const countries = ref([]);
    const countryStates = ref([]);
    const currentCountryStates = ref([]);

    watchEffect(async () => {
        const countriesStates = await DB.getItem('countries_states', 1);

        countries.value = countriesStates?.countries;
        countryStates.value = countriesStates?.countryStates;
    });

    const form = reactive({
        firstName: '',
        lastName: '',
        phone: '',
        email: '',
        address: '',
        country: '',
        state: '',
        city: '',
        postcode: '',
    });

    watch(() => form.country, (value) => {
        currentCountryStates.value = countryStates.value.find((countryState) => countryState.countryCode === value)?.states;
    });

    /**
     * Form Submission
     */
    const { mutate } = useMutation(CREATE, () => ({
        variables: {
            input: toRaw(form),
        },
    }));

    const submitForm = async (params, { setErrors, resetForm }) => {
        if (isOnline.value) {
            const data = await mutate();

            const createCustomer = data?.data?.createPosCustomer;

            if (createCustomer?.success === true) {
                emitter.emit('add_flash', {
                    type: 'success',
                    message: createCustomer.message,
                });

                const customers = await DB.getItem('customers', 'all').then((data) => data.customers);

                await DB.updateItem('customers', {
                    id: 'all',
                    customers: [...customers, createCustomer.customer],
                });

                await DB.deleteAllItems('current_customer');

                await DB.addItem('current_customer', createCustomer.customer);

                router.push({path: '/customers'});
            } else if (createCustomer?.success === false) {
                if (createCustomer?.errors) {
                    setErrors(JSON.parse(createCustomer.errors));
                } else {
                    emitter.emit('add_flash', {
                        type: 'error',
                        message: createCustomer?.message,
                    });
                }
            }
        } else {
            await DB.getItem('customers', 'all').then(async (data) => {
                let errors = {};

                if (data.customers.find(customer => customer.email == form.email)) {
                    Object.assign(errors, {
                        email: [t('pos.customers.create.email_already_exist')],
                    });
                }

                if (data.customers.find(customer => customer.phone == form.phone)) {
                    Object.assign(errors, {
                        phone: [t('pos.customers.create.phone_already_exist')],
                    });
                }

                if (Object.keys(errors).length) {                    
                    setErrors(errors);

                    return;
                }

                await DB.addItem('offline_customers', toRaw(form)).then(() => {
                    emitter.emit('add_flash', {
                        type: 'success',
                        message: t('pos.customers.create.create_success'),
                    });

                    router.push({path: '/customers'});
                });
            });
        }
    }
</script>
