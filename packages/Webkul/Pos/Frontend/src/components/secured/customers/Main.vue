<template>
    <div class="flex gap-4">
        <div class="my-4 flex w-full flex-col gap-4">
            <div class="grid gap-4 xl:w-[738px] 2xl:w-[828px]">
                <div class="flex items-center justify-between">
                    <p class="text-2xl font-semibold">
                        {{ $t('pos.customers.title') }}
                    </p>

                    <router-link
                        to="/customers/create"
                        class="max-sm:transparent-button primary-button"
                    >
                        <span class="icon-plus text-2xl"></span>

                        <span class="max-sm:hidden">
                            {{ $t('pos.customers.add_btn_title') }}
                        </span>
                    </router-link>
                </div>

                <template v-if="isCurrentCustomerLoading">
                    <CurrentCustomerSkeleton />
                </template>

                <template v-else>
                    <!-- Desktop View -->
                    <div class="box-shadow flex gap-6 rounded-lg bg-white p-4 max-sm:hidden">
                        <template v-if="currentCustomer?.id">
                            <template v-if="currentCustomer.imageUrl">
                                <img
                                    :src="currentCustomer.imageUrl"
                                    class="h-40 w-40 rounded-lg"
                                    alt="profile image"
                                >
                            </template>

                            <template v-else>
                                <img
                                    src="/src/assets/images/user-placeholder.png"
                                    class="h-40 w-40 rounded-lg"
                                    alt="profile image"
                                >
                            </template>

                            <div class="grid w-full">
                                <div class="grid content-start gap-1">
                                    <p class="text-4xl font-normal text-dark">
                                        {{ currentCustomer.firstName }} {{ currentCustomer.lastName }}

                                        <span class="text-lg font-medium leading-6 text-dark">
                                            #{{ currentCustomer.id }}
                                        </span>
                                    </p>

                                    <div class="grid gap-0.5">
                                        <p class="text-base font-normal leading-5 text-greyish">
                                            {{ currentCustomer.email }}
                                        </p>

                                        <p class="text-base font-normal leading-5 text-greyish">
                                            {{ currentCustomer.phone ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-end justify-between">
                                    <div class="flex gap-6 text-dark">
                                        <router-link
                                            :to="`/customers/edit/${currentCustomer.id}`"
                                            class="flex cursor-pointer items-center gap-2.5"
                                        >
                                            <span class="icon-edit text-2xl"></span>

                                            {{ $t('pos.customers.edit_btn_title') }}
                                        </router-link>

                                        <div
                                            class="flex cursor-pointer items-center gap-2.5"
                                            @click="deleteCurrentCustomer()"
                                        >
                                            <span class="icon-delete text-2xl"></span>

                                            {{ $t('pos.customers.delete_btn_title') }}
                                        </div>
                                    </div>

                                    <button
                                        class="transparent-button"
                                        @click="removeCurrentCustomer()"
                                    >
                                        {{ $t('pos.customers.remove_btn_title') }}
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="flex h-40 w-full items-center justify-center">
                                <p class="text-lg font-normal text-greyish">
                                    {{ $t('pos.customers.no_current_customer') }}
                                </p>
                            </div>
                        </template>
                    </div>

                    <!-- Mobile View -->
                    <div class="box-shadow flex flex-col gap-5 rounded-lg bg-white p-4 md:hidden">
                        <template v-if="currentCustomer?.id">
                            <div class="flex gap-2.5">
                                <template v-if="currentCustomer.imageUrl">
                                    <img
                                        :src="currentCustomer.imageUrl"
                                        class="h-20 w-20 rounded-lg"
                                        alt="profile image"
                                    >
                                </template>

                                <template v-else>
                                    <img
                                        src="/src/assets/images/user-placeholder.png"
                                        class="h-20 w-20 rounded-lg"
                                        alt="profile image"
                                    >
                                </template>

                                <div class="grid content-start gap-1">
                                    <p class="text-xl font-normal text-dark">
                                        {{ currentCustomer.firstName }} {{ currentCustomer.lastName }}

                                        <span class="text-lg font-medium leading-6 text-dark">
                                            #{{ currentCustomer.id }}
                                        </span>
                                    </p>

                                    <div class="grid gap-0.5">
                                        <p class="text-base font-normal leading-5 text-greyish">
                                            {{ currentCustomer.email }}
                                        </p>

                                        <p class="text-base font-normal leading-5 text-greyish">
                                            {{ currentCustomer.phone ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-end justify-between">
                                <router-link
                                    :to="`/customers/edit/${currentCustomer.id}`"
                                    class="flex cursor-pointer items-center gap-2.5"
                                >
                                    <span class="icon-edit text-2xl"></span>

                                    {{ $t('pos.customers.edit_btn_title') }}
                                </router-link>

                                <div
                                    class="flex cursor-pointer items-center gap-2.5"
                                    @click="deleteCurrentCustomer()"
                                >
                                    <span class="icon-delete text-2xl"></span>

                                    {{ $t('pos.customers.delete_btn_title') }}
                                </div>

                                <div
                                    class="flex cursor-pointer items-center gap-2.5"
                                    @click="removeCurrentCustomer()"
                                >
                                    <span class="icon-cross text-2xl"></span>

                                    {{ $t('pos.customers.remove_btn_title') }}
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="flex h-40 w-full items-center justify-center">
                                <p class="text-lg font-normal text-greyish">
                                    {{ $t('pos.customers.no_current_customer') }}
                                </p>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="box-shadow grid gap-4 rounded-lg bg-white px-4 py-1">
                    <div class="flex h-12 gap-2.5 rounded-lg bg-white pt-2">
                        <div
                            v-on:click="activeTab = type"
                            :class="[activeTab === type ? 'bg-light text-primary !border-primary' : 'text-dark']"
                            class="flex cursor-pointer items-center rounded-lg border-2 border-transparent px-3 py-2 text-base font-medium"
                            v-for="(type, index) in ['customers', 'offline_customers']"
                            :key="index"
                        >
                            {{ $t(`pos.customers.${type}`) }}
                        </div>
                    </div>

                    <div class="relative flex items-center text-dark">
                        <input
                            type="text"
                            class="h-12 w-full px-8 text-base font-normal leading-5 shadow-[0px_-1px_0px_0px_rgba(0,0,0,0.1)_inset]"
                            :placeholder="$t('pos.customers.search_customers')"
                            @input="searchCustomers($event)"
                        >

                        <i class="icon-search left absolute top-3.5 flex items-center text-xl leading-5"></i>
                    </div>

                    <template v-if="isCustomerListLoading">
                        <CustomerListSkeleton />
                    </template>

                    <template v-else>
                        <div class="grid h-[428px] w-full content-start gap-1 overflow-y-auto overflow-x-hidden">
                            <template v-if="customers.length">
                                <div
                                    class="mr-4 flex cursor-pointer justify-between rounded-lg p-2.5 hover:bg-light"
                                    v-for="(customer, index) in customers"
                                    :key="index"
                                >
                                    <div
                                        class="flex gap-2.5" 
                                        @click="setCurrentCustomer(customer)"
                                    >
                                        <template v-if="customer.imageUrl">
                                            <img
                                                :src="customer.imageUrl"
                                                class="h-12 w-12 rounded"
                                                alt="profile image"
                                            >
                                        </template>

                                        <template v-else>
                                            <img
                                                src="/src/assets/images/user-placeholder.png"
                                                class="h-12 w-12 rounded"
                                                alt="profile image"
                                            >
                                        </template>

                                        <div class="grid gap-1">
                                            <p class="text-base font-normal leading-5 text-dark">
                                                {{ customer.firstName }} {{ customer.lastName }}
                                            </p>

                                            <p class="text-base font-normal leading-5 text-greyish">
                                                {{ customer.email }}
                                            </p>
                                        </div>
                                    </div>

                                    <template v-if="activeTab === 'customers'">
                                        <p class="text-base font-normal leading-5 text-dark">
                                            {{ customer.createdAt?.split(" ")?.[0]?.split("-")?.reverse()?.join("/") }}
                                        </p>
                                    </template>

                                    <template v-else-if="isOnline">
                                        <div
                                            class="flex h-12 w-12 cursor-pointer items-center justify-center rounded-md bg-light hover:bg-white"
                                            @click="syncCustomer(customer)"
                                        >
                                            <span class="icon-sync text-2xl text-dark"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template v-else>
                                <div class="flex h-32 items-center justify-center">
                                    <p class="text-lg font-normal text-greyish">
                                        {{ $t('pos.customers.no_customers_found') }}
                                    </p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <cart v-if="! isMobileOrTab" />
    </div>
</template>

<script setup>
    import { useI18n } from 'vue-i18n';
    import { ref, toRaw, computed, onBeforeMount, inject, watch, getCurrentInstance } from 'vue';
    import { useMutation } from '@vue/apollo-composable';
    import { DELETE } from '@src/graphql/customers';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { useWindowWidth } from '@src/composable/window';
    import CurrentCustomerSkeleton from '@skeletons/customers/Current.vue';
    import CustomerListSkeleton from '@skeletons/customers/List.vue';
    import { CREATE } from '@src/graphql/customers';

    /**
     * General variables
     */
    const DB = useIndexedDB();
    const { t } = useI18n();
    const emitter = inject('emitter');
    const activeTab = ref('customers');
    const { isMobileOrTab } = useWindowWidth();
    const { appContext } = getCurrentInstance();
    const isOnline = computed(() => appContext.config.globalProperties.$isOnline.value);


    /**
     * Setup for current customer
     */
    const customers = ref([]);
    const currentCustomer = ref({});
    const isCustomerListLoading = ref(true);
    const isCurrentCustomerLoading = ref(true);

    onBeforeMount(async () => {
        customers.value = await DB.getItem('customers', 'all').then(data => data?.customers ?? []);

        currentCustomer.value = await DB.getCurrentCustomer();

        isCustomerListLoading.value = false;

        isCurrentCustomerLoading.value = false;
    });

    /**
     * Get customers list
     */
    const getCustomersList = async () => {
        if (activeTab.value === 'customers') {
            customers.value = await DB.getItem('customers', 'all').then(data => data.customers);
        } else {
            customers.value = await DB.getAllItems('offline_customers');
        }
    };

    /**
     * Search customers
     */
    const searchCustomers = async (event) => {
        const searchValue = event.target.value;

        if (searchValue) {
            isCustomerListLoading.value = true;

            if (activeTab.value === 'customers') {
                customers.value = await DB.getItem('customers', 'all').then(data => {
                    return data.customers.filter(customer => {
                        return customer.name.toLowerCase().includes(searchValue.toLowerCase());
                    });
                });
            } else {
                customers.value = await DB.getAllItems('offline_customers').then(customers => {
                    return customers.filter(customer => {
                        return customer.firstName.toLowerCase().includes(searchValue.toLowerCase())
                            || customer.lastName.toLowerCase().includes(searchValue.toLowerCase());
                    });
                });
            }

            isCustomerListLoading.value = false;
        } else {
            await getCustomersList();
        }
    };

    /**
     * Watch for active tab
     */
    watch(activeTab, async () => {
        isCustomerListLoading.value = true;

        await getCustomersList();

        isCustomerListLoading.value = false;
    });

    /**
     * Sync customer
     */
    const { mutate } = useMutation(CREATE);

    const syncCustomer = async (customer) => {
        emitter.emit('open_confirm_modal', {
            agree: async () => {
                if (! isOnline.value) {
                    emitter.emit('add_flash', {
                        type: 'warning',
                        message: t('pos.common.flash_messages.offline_error'),
                    });

                    return;
                }

                const { id:customerId, ...customerData } = customer;

                const data = await mutate({
                    input: customerData,
                });

                const { createPosCustomer } = data?.data;

                if (createPosCustomer?.success === true) {
                    emitter.emit('add_flash', {
                        type: 'success',
                        message: createPosCustomer.message,
                    });

                    const customers = await DB.getItem('customers', 'all').then((data) => data.customers);

                    await DB.updateItem('customers', {
                        id: 'all',
                        customers: [...customers, createPosCustomer.customer],
                    });

                    await DB.deleteItem('offline_customers', customerId);

                    await getCustomersList();
                } else if (createPosCustomer?.success === false) {
                    emitter.emit('add_flash', {
                        type: 'error',
                        message: t('pos.common.flash_messages.error_message'),
                    });
                }
            },
        });
    };

    
    /**
     * Methods to set current customer
     */
    const setCurrentCustomer = async (customer) => {
        if (activeTab.value === 'offline_customers') {
            return;
        }

        await DB.deleteAllItems('current_customer');

        const success = await DB.addItem('current_customer', toRaw(customer));
        
        if (success) {
            currentCustomer.value = customer;

            emitter.emit('add_flash', {
                type: 'success',
                message: t('pos.customers.current_customer_updated'),
            });

            emitter.emit('customer_updated');
        } else {
            emitter.emit('add_flash', {
                type: 'error',
                message: t('pos.common.flash_messages.error_message'),
            });
        }
    };
    

    /**
     * Methods to remove current customer
     */
    const removeCurrentCustomer = () => {
        emitter.emit('open_confirm_modal', {
            agree: async () => {
                await DB.deleteAllItems('current_customer');
            
                currentCustomer.value = {};

                emitter.emit('add_flash', {
                    type: 'success',
                    message: t('pos.customers.current_customer_removed'),
                });

                emitter.emit('customer_updated');
            },
        });
    };


    /**
     * Delete customer
     */
    const { mutate: deleteCustomer } = useMutation(DELETE);

    const deleteCurrentCustomer = async () => {
        emitter.emit('open_confirm_modal', {
            agree: async () => {                
                let customerId = currentCustomer.value.id;                

                const { data } = await deleteCustomer({ id: customerId });

                if (data?.deletePosCustomer?.success) {
                    customers.value = await DB.getItem('customers', 'all').then((data) => {
                        return data.customers.filter((customer) => customer.id != customerId);
                    });

                    await DB.updateItem('customers', {
                        id: 'all',
                        customers: toRaw(customers.value),
                    });

                    await DB.deleteAllItems('current_customer');

                    currentCustomer.value = {};

                    emitter.emit('add_flash', {
                        type: 'success',
                        message: data.deletePosCustomer.message,
                    });

                    emitter.emit('customer_updated');
                } else {
                    emitter.emit('add_flash', {
                        type: 'error',
                        message: data.deletePosCustomer.message,
                    });
                }
            },
        });
    };
</script>
