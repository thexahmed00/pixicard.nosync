<template>
    <div>
        <Header />
        
        <FlashMessage />

        <ConfirmModal />
        
        <div class="flex min-h-[calc(100vh)] gap-4 bg-white">
            <template v-if="$route.path.split('/')[1] != 'payment'">
                <Sidebar />
            </template>

            <div
                :class="$route.path.split('/')[1] == 'payment' ? 'p-4' : 'pl-4 pr-4 xl:ltr:pl-[120px] xl:rtl:pr-[120px]'"
                class="max-w-full flex-1 transition-all duration-300 xl:bg-light"
            >
                <router-view />
            </div>
        </div>

        <Teleport to="body">
            <modal ref="progressModal">
                <template v-slot:header="{ toggle }">
                    <div class="flex justify-between gap-2.5">
                        {{ $t('pos.layout.main.progress_modal.title') }}

                        <span
                            v-if="
                                ! productsLoading
                                && ! categoriesLoading
                                && ! customersLoading
                                && ! ordersLoading
                            "
                            class="icon-cross cursor-pointer text-2xl text-dark hover:text-primary"
                            @click="toggle"
                        ></span>
                    </div>
                </template>
                
                <template v-slot:content>
                    <div class="flex flex-col">
                        <p class="text-base">
                            {{ $t('pos.layout.main.progress_modal.description') }}
                        </p>

                        <div class="my-5 flex flex-col gap-5 pt-5">
                            <template v-if="! productFetched">
                                <progress-bar
                                    :name="$t('pos.layout.main.progress_modal.products')"
                                    :progress="productProgress"
                                />
                            </template>

                            <template v-if="! categoryFetched">
                                <progress-bar
                                    :name="$t('pos.layout.main.progress_modal.categories')"
                                    :progress="categoryProgress"
                                />
                            </template>

                            <template v-if="! customerFetched">
                                <progress-bar
                                    :name="$t('pos.layout.main.progress_modal.customers')"
                                    :progress="customerProgress"
                                />
                            </template>

                            <template v-if="! orderFetched">
                                <progress-bar
                                    :name="$t('pos.layout.main.progress_modal.orders')"
                                    :progress="orderProgress"
                                />
                            </template>
                        </div>
                    </div>
                </template>
            </modal>

            <modal ref="drawerFormModal">
                <template v-slot:header="{ toggle }">
                    <div class="flex justify-between gap-2.5">
                        {{ $t('pos.layout.main.cash_drawer.title') }}

                        <div
                            class="flex h-6 w-6"
                            @click="toggle"
                        >
                            <span class="icon-cross cursor-pointer text-2xl"></span>
                        </div>
                    </div>
                </template>
                
                <template v-slot:content>
                    <v-form v-slot="{ handleSubmit }">
                        <form
                            class="grid gap-4"
                            @submit="handleSubmit($event, submitDrawerForm)"
                        >
                            <div class="grid gap-1">
                                <label
                                    for="opening_amount"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.layout.main.cash_drawer.opening_drawer_amount') }}
                                </label>

                                <v-field
                                    type="text"
                                    name="opening_amount"
                                    id="opening_amount"
                                    class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                    rules="required"
                                    placeholder="100"
                                    :label="$t('pos.layout.main.cash_drawer.opening_drawer_amount')"
                                />

                                <v-error-message
                                    name="opening_amount"
                                    class="text-sm text-red-500"
                                />
                            </div>

                            <div class="grid gap-1">
                                <label
                                    for="remarks"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.layout.main.cash_drawer.remarks') }}
                                </label>

                                <v-field
                                    as="textarea"
                                    name="remarks"
                                    id="remarks"
                                    class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                    rules="required"
                                    :placeholder="$t('pos.layout.main.cash_drawer.remarks_placeholder')"
                                />

                                <v-error-message
                                    name="remarks"
                                    class="text-sm text-red-500"
                                />
                            </div>

                            <Button
                                type="submit"
                                :isLoading="isDrawerFormSubmitting"
                                class="secondary-button w-full"
                                icon="icon-payment"
                                :label="$t('pos.layout.main.cash_drawer.open_drawer')"
                            />
                        </form>
                    </v-form>
                </template>
            </modal>
        </Teleport>
    </div>
</template>

<script setup>
    import { ref, onMounted, inject, computed, watch } from 'vue'
    import { useRouter } from 'vue-router'
    import { useQuery, useMutation } from '@vue/apollo-composable';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { useCookies } from '@src/composable/cookies';
    import { GET_DRAWER, OPEN_DRAWER } from '@src/graphql/drawer';
    import { PRODUCTS, CATEGORIES } from '@src/graphql/home';
    import { CUSTOMERS } from '@src/graphql/customers';
    import { ORDERS } from '@src/graphql/orders';
    import Header from '@components/secured/layouts/Header.vue';
    import Sidebar from '@components/secured/layouts/Sidebar.vue';
    import FlashMessage from '@components/shared/FlashMessage.vue';
    import ConfirmModal from '@components/secured/common/ConfirmModal.vue';
    import ProgressBar from '@components/secured/common/ProgressBar.vue';

    /**
     * General variables
     */
    const emitter = inject('emitter');
    const router = useRouter();
    const DB = useIndexedDB();
    const cookies = useCookies();


    /**
     * Fetch products
     */
    const progressModal = ref(null);
    const productFetched = ref(cookies.get('products_fetched'));
    const customerFetched = ref(cookies.get('customers_fetched'));
    const orderFetched = ref(cookies.get('orders_fetched'));
    const categoryFetched = ref(cookies.get('categories_fetched'));

    const { result: productResult, loading: productsLoading, fetchMore: fetchMoreProducts } = useQuery(PRODUCTS, {
        page: 1,
        first: 20,
    }, () => ({
        enabled: !productFetched.value,
    }));

    const products = ref([]);
    const productProgress = ref(0);
    const outletProducts = computed(() => productResult.value?.getOutletProducts ?? {});

    watch([outletProducts], async ([outletProductsValue]) => {
        products.value = outletProductsValue.data ?? [];

        const paginatorInfo = outletProductsValue.paginatorInfo ?? {};        

        if (
            paginatorInfo.currentPage === 1
            && !progressModal.isOpen
        ) {
            progressModal.value.open();
        }

        emitter.emit('products_fetched', products.value);

        productProgress.value = Math.ceil((paginatorInfo.currentPage / paginatorInfo.lastPage) * 100);

        if (paginatorInfo.currentPage < paginatorInfo.lastPage) {
            await fetchMoreProducts({
                variables: {
                    page: paginatorInfo.currentPage + 1,
                },
                updateQuery: (previousResult, { fetchMoreResult }) => {
                    const newProducts = fetchMoreResult.getOutletProducts.data;
                    const updatedProducts = [
                        ...previousResult.getOutletProducts.data,
                        ...newProducts
                    ];

                    return {
                        getOutletProducts: {
                            ...fetchMoreResult.getOutletProducts,
                            data: updatedProducts,
                        }
                    };
                }
            });
        } else if (products.value.length === paginatorInfo.total) {
            await DB.updateItem('products', {
                id: 'all',
                products: products.value
            });

            cookies.set('products_fetched', true);
        }
    }, { deep: true });


    /**
     * Fetch categories
     */
    const { result: categoryResult, loading: categoriesLoading, fetchMore: fetchMoreCategories } = useQuery(CATEGORIES, {
        page: 1,
        first: 20,
    }, () => ({
        enabled: !categoryFetched.value,
    }));

    const categories = ref([]);
    const categoryProgress = ref(0);
    const outletCategories = computed(() => categoryResult.value?.getOutletCategories ?? {});

    watch([outletCategories], async ([outletCategoriesValue]) => {
        categories.value = outletCategoriesValue.data ?? [];

        const paginatorInfo = outletCategoriesValue.paginatorInfo ?? {};

        if (
            paginatorInfo.currentPage === 1
            && !progressModal.isOpen
        ) {
            progressModal.value.open();
        }

        emitter.emit('categories_fetched', categories.value);

        categoryProgress.value = Math.ceil((paginatorInfo.currentPage / paginatorInfo.lastPage) * 100);

        if (paginatorInfo.currentPage < paginatorInfo.lastPage) {
            await fetchMoreCategories({
                variables: {
                    page: paginatorInfo.currentPage + 1,
                },
                updateQuery: (previousResult, { fetchMoreResult }) => {
                    const newCategories = fetchMoreResult.getOutletCategories.data;
                    const updatedCategories = [
                        ...previousResult.getOutletCategories.data,
                        ...newCategories
                    ];

                    return {
                        getOutletCategories: {
                            ...fetchMoreResult.getOutletCategories,
                            data: updatedCategories,
                        }
                    };
                }
            });
        } else if (categories.value.length === paginatorInfo.total) {
            await DB.updateItem('categories', {
                id: 'all',
                categories: categories.value
            });

            cookies.set('categories_fetched', true);
        }
    }, { deep: true });

    /**
     * Fetch Customer data
     */
    const { result: customerResult, loading: customersLoading, fetchMore: fetchMoreCustomers } = useQuery(CUSTOMERS, {
        page: 1,
        first: 20,
    }, () => ({
        enabled: !customerFetched.value,
    }));

    const customers = ref([]);
    const customerProgress = ref(0);
    const outletCustomers = computed(() => customerResult.value?.getCustomers ?? {});

    watch([outletCustomers], async ([outletCustomersValue]) => {
        customers.value = outletCustomersValue.data ?? [];

        const paginatorInfo = outletCustomersValue.paginatorInfo ?? {};

        if (
            paginatorInfo.currentPage === 1
            && !progressModal.isOpen
        ) {
            progressModal.value.open();
        }

        customerProgress.value = Math.ceil((paginatorInfo.currentPage / paginatorInfo.lastPage) * 100);

        if (paginatorInfo.currentPage < paginatorInfo.lastPage) {
            await fetchMoreCustomers({
                variables: {
                    page: paginatorInfo.currentPage + 1,
                },
                updateQuery: (previousResult, { fetchMoreResult }) => {
                    const newCustomers = fetchMoreResult.getCustomers.data;
                    const updatedCustomers = [
                        ...previousResult.getCustomers.data,
                        ...newCustomers
                    ];

                    return {
                        getCustomers: {
                            ...fetchMoreResult.getCustomers,
                            data: updatedCustomers,
                        }
                    };
                }
            });
        } else if (customers.value.length === paginatorInfo.total) {
            await DB.updateItem('customers', {
                id: 'all',
                customers: customers.value
            });

            cookies.set('customers_fetched', true);
        }
    }, { deep: true });

    /**
     * Fetch Order data
     */
    const { result: orderResult, loading: ordersLoading, fetchMore: fetchMoreOrders } = useQuery(ORDERS, {
        page: 1,
        first: 20,
    }, () => ({
        enabled: !orderFetched.value,
    }));

    const orders = ref([]);
    const orderProgress = ref(0);
    const outletOrders = computed(() => orderResult.value?.getOrders ?? {});

    watch([outletOrders], async ([outletOrdersValue]) => {
        orders.value = outletOrdersValue.data ?? [];

        const paginatorInfo = outletOrdersValue.paginatorInfo ?? {};

        if (
            paginatorInfo.currentPage === 1
            && !progressModal.isOpen
        ) {
            progressModal.value.open();
        }

        orderProgress.value = Math.ceil((paginatorInfo.currentPage / paginatorInfo.lastPage) * 100);

        if (paginatorInfo.currentPage < paginatorInfo.lastPage) {
            await fetchMoreOrders({
                variables: {
                    page: paginatorInfo.currentPage + 1,
                },
                updateQuery: (previousResult, { fetchMoreResult }) => {
                    const newOrders = fetchMoreResult.getOrders.data;
                    const updatedOrders = [
                        ...previousResult.getOrders.data,
                        ...newOrders
                    ];

                    return {
                        getOrders: {
                            ...fetchMoreResult.getOrders,
                            data: updatedOrders,
                        }
                    };
                }
            });
        } else if (orders.value.length === paginatorInfo.total) {
            await DB.updateItem('orders', {
                id: 'all',
                orders: orders.value
            });

            cookies.set('orders_fetched', true);
        }
    }, { deep: true });

    /**
     * Fetch Drawer data
     */    
    const { result, error } = useQuery(GET_DRAWER);

    const drawerData = computed(() => result.value?.getDrawerDetails ?? []);

    watch(error, (value) => {
        if (value.message === 'Unauthenticated.') {
            localStorage.removeItem('accessToken');

            router.push({path: '/'});
        } else if (value.message === 'status_off') {
            localStorage.removeItem('accessToken');
            
            router.push({path: '/404'});
        }
    });

    watch(drawerData, (data) => {        
        if (data.openingAmount == null) {
            drawerFormModal.value.toggle();
        }
    });

    /**
     * Drawer Form
     */
    const drawerFormModal = ref(null);
    const isDrawerFormSubmitting = ref(false);

    const { mutate: openDrawer } = useMutation(OPEN_DRAWER);

    const submitDrawerForm = (params, { setErrors, resetForm }) => {
        isDrawerFormSubmitting.value = true;

        openDrawer({ input: {
            openingAmount: parseFloat(params.opening_amount),
            remark: params.remark
        } }).then((response) => {
            const openDrawerResult = response.data.openDrawer;

            if (openDrawerResult?.success) {
                drawerFormModal.value.toggle();

                resetForm();

                emitter.emit('add_flash', {
                    type: 'success',
                    message: openDrawerResult?.message
                });
            } else {
                if (openDrawerResult?.errors) {
                    setErrors(JSON.parse(openDrawerResult.errors));
                }
            }

            isDrawerFormSubmitting.value = false;
        });
    };

    /**
     * Progress modal close on all data fetched
     */
    watch([
        productsLoading,
        categoriesLoading,
        customersLoading,
        ordersLoading
    ], ([
        productsLoadingValue,
        categoriesLoadingValue,
        customersLoadingValue,
        ordersLoadingValue
    ]) => {
        if (
            ! productsLoadingValue
            && !categoriesLoadingValue
            && !customersLoadingValue
            && !ordersLoadingValue
        ) {
            progressModal.value.close();
        }
    });

    onMounted(() => {
        const message = localStorage.getItem('loginMessage');

        if (message) {
            emitter.emit('add_flash', {
                type: 'success',
                message: message
            });

            localStorage.removeItem('loginMessage');
        }
    });
</script>
