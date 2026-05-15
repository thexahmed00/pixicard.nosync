<template>
    <header class="sticky top-0 z-[10001] bg-white shadow-[0px_-1px_0px_0px_rgba(0,0,0,0.1)_inset]">
        <!-- Desktop View -->
        <section class="hidden items-center justify-between p-2.5 xl:flex">
            <div class="flex items-center gap-1.5">
                <!-- Heading -->
                <h1 class="text-2xl font-semibold text-dark">
                    Pixicard POS
                </h1>

                <div
                    v-if="$route.path.split('/')[1] == 'home'"
                    class="ml-10 flex items-center gap-x-2"
                >
                    <!-- Search Bar -->
                    <div class="relative flex w-[486px] max-w-[486px] items-center text-dark max-lg:w-[400px] ltr:ml-2.5 rtl:mr-2.5">
                        <input
                            type="text"
                            class="h-12 w-full rounded-md bg-light px-3.5 py-3 text-base font-normal leading-5 text-dark"
                            :placeholder="$t('pos.layout.header.search_products')"
                            @input="searchProduct"
                        >

                        <i class="icon-search absolute top-3 flex items-center text-2xl ltr:right-2 rtl:left-2"></i>
                    </div>

                    <!-- Barcode Scanner -->
                    <div
                        v-if="showBarcodeScanner"
                        class="flex h-12 w-12 cursor-pointer items-center justify-center rounded-md bg-light"
                        @click="$refs.barcodeModal.open()"
                    >
                        <i class="icon-barcode text-2xl text-dark"></i>
                    </div>

                    <!-- Product List -->
                    <div
                        class="flex h-12 w-12 cursor-pointer items-center justify-center rounded-md bg-light"
                        @click="$refs.productCreateModal.open()"
                    >
                        <i class="icon-box text-2xl text-dark"></i>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-x-2">
                <!-- Sync -->
                <div
                    class="flex h-12 w-12 cursor-pointer items-center justify-center rounded-md bg-light"
                    @click="refreshPage()"
                >
                    <i class="icon-sync text-2xl text-dark"></i>
                </div>

                <!-- Network -->
                <div
                    class="flex h-12 w-12 cursor-pointer items-center justify-center rounded-md bg-light"
                    @click="updateNetworkStatus()"
                >
                    <i
                        :class="[isOnline ? 'text-limeGreen' : 'text-red-500']"
                        class="icon-network text-2xl"
                    >
                    </i>
                </div>

                <router-link
                    to="/orders"
                    class="transparent-button"
                >
                    <i class="icon-hold-cart text-2xl"></i>

                    <span class="text-base font-semibold leading-5">
                        {{ $t('pos.layout.header.hold_orders') }}
                    </span>
                </router-link>
            </div>
        </section>

        <!-- Mobile View -->
        <section
            v-if="$route.path.split('/')[1] != 'payment'"
            class="flex items-center gap-2.5 p-2 xl:hidden"
        >
            <div
                class="flex h-12 min-w-12 cursor-pointer items-center justify-center rounded-lg bg-light"
                @click="isMobileMenuOpen = !isMobileMenuOpen"
            >
                <span class="icon-menu text-2xl"></span>
            </div>

            <template v-if="$route.path.split('/')[1] == 'home'">
                <!-- Search Bar -->
                <div class="relative flex w-full items-center text-dark">
                    <input
                        type="text"
                        class="h-12 w-full rounded-md bg-light px-3.5 py-3 text-base font-normal leading-5 text-dark"
                        :placeholder="$t('pos.layout.header.search_products')"
                        @input="searchProduct"
                    >

                    <i class="icon-search absolute top-3 flex items-center text-2xl ltr:right-2 rtl:left-2"></i>
                </div>

                <!-- Barcode Scanner -->
                <div
                    v-if="showBarcodeScanner"
                    class="flex h-12 min-w-12 cursor-pointer items-center justify-center rounded-md bg-light"
                    @click="$refs.barcodeModal.open()"
                >
                    <i class="icon-barcode text-2xl text-dark"></i>
                </div>

                <!-- Product List -->
                <div
                    class="flex h-12 min-w-12 cursor-pointer items-center justify-center rounded-md bg-light"
                    @click="$refs.productCreateModal.open()"
                >
                    <i class="icon-box text-2xl text-dark"></i>
                </div>
            </template>

            <template v-else>
                <p class="text-xl font-medium leading-6">
                    {{ $t(`pos.layout.sidebar.${$route.path.split('/')[1]}`) }}
                </p>
            </template>
        </section>

        <Teleport to="body">
            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 transform -translate-x-full"
                enter-to-class="opacity-100 transform translate-x-0"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="opacity-100 transform translate-x-0"
                leave-to-class="opacity-0 transform -translate-x-full"
            >
                <div
                    class="fixed bottom-0 left-0 right-0 top-0 z-[10002] w-full bg-white"
                    v-if="isMobileMenuOpen"
                >
                    <div class="flex h-full flex-col justify-between bg-extraLight">
                        <div class="flex flex-col gap-y-1">
                            <div class="flex h-16 justify-between gap-2 px-4 py-5">
                                <!-- Heading -->
                                <h1 class="text-xl font-semibold leading-6 text-dark">
                                    {{ $t('pos.layout.header.title') }}
                                </h1>

                                <div class="flex items-center gap-x-6">
                                    <!-- Sync -->
                                    <div
                                        class="flex cursor-pointer items-center justify-center"
                                        @click="refreshPage()"
                                    >
                                        <i class="icon-sync text-2xl text-dark"></i>
                                    </div>

                                    <!-- Network -->
                                    <div
                                        class="flex cursor-pointer items-center justify-center"
                                        @click="updateNetworkStatus()"
                                    >
                                        <i
                                            :class="[isOnline ? 'text-limeGreen' : 'text-red-500']"
                                            class="icon-network text-2xl"
                                        >
                                        </i>
                                    </div>

                                    <!-- Close -->
                                    <div
                                        class="flex cursor-pointer items-center justify-center"
                                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                                    >
                                        <span class="icon-cross rounded-full border-2 border-dark text-lg leading-6"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2.5 p-4 lg:grid-cols-4">
                                <router-link
                                    v-for="(menu, index) in menus"
                                    :key="index"
                                    :to="menu.path"
                                    :class="[$route.path.split('/')[1] === menu.name ? 'text-primary border-2 border-primary bg-light' : '']"
                                    class="grid aspect-square cursor-pointer place-items-center content-center rounded-lg text-greyish hover:text-primary"
                                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                                >
                                    <i
                                        class="icon text-2xl"
                                        :class="menu.icon"
                                    >
                                    </i>

                                    <span class="text-center text-[12px] font-medium leading-4">
                                        {{ $t(`pos.layout.sidebar.${menu.name}`) }}
                                    </span>
                                </router-link>
                            </div>
                        </div>

                        <div class="flex justify-between gap-2.5 border-t bg-white px-4">
                            <div class="flex items-center justify-center gap-2.5">
                                <template v-if="agent?.imageUrl">
                                    <img
                                        :src="agent.imageUrl"
                                        class="h-10 w-10 cursor-pointer rounded-full"
                                        alt="profile image"
                                    >
                                </template>

                                <template v-else>
                                    <img
                                        src="/src/assets/images/user-placeholder.png"
                                        class="h-10 w-10 cursor-pointer rounded-full"
                                        alt="profile image"
                                    >
                                </template>

                                <div class="flex flex-col gap-1">
                                    <span class="text-base font-semibold leading-5">
                                        {{ `${agent?.firstName} ${agent?.lastName}` }}
                                    </span>

                                    <span class="text-[12px] font-medium leading-4">
                                        {{ agent?.email }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="grid h-20 w-20 cursor-pointer place-items-center content-center rounded-lg text-greyish hover:text-primary"
                                @click="logout()"
                            >
                                <i class="icon icon-logout text-2xl"></i>

                                <span class="text-[12px] font-medium leading-4">
                                    {{ $t('pos.layout.sidebar.logout') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>

            <modal ref="barcodeModal">
                <template v-slot:header>
                    {{ $t('pos.layout.header.barcode_form.title') }}
                </template>
                
                <template v-slot:content="{ toggle }">
                    <v-form
                        class="grid gap-4"
                        @submit="submitBarcodeForm"
                    >
                        <div class="grid gap-1">
                            <v-field
                                type="text"
                                name="barcode"
                                id="barcode"
                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                rules="required"
                                :placeholder="$t('pos.layout.header.barcode_form.barcode_placeholder')"
                            />

                            <v-error-message
                                name="barcode"
                                class="text-sm text-red-500"
                            />
                        </div>

                        <div class="flex justify-end gap-6">
                            <button
                                type="button"
                                class="transparent-button w-36"
                                @click="toggle"
                            >
                                {{ $t('pos.layout.header.barcode_form.cancel_btn_title') }}
                            </button>

                            <button
                                type="submit"
                                class="primary-button w-36"
                            >
                                {{ $t('pos.layout.header.barcode_form.proceed_btn_title') }}
                            </button>
                        </div>
                    </v-form>
                </template>
            </modal>

            <modal ref="productCreateModal">
                <template v-slot:header>
                    {{ $t('pos.layout.header.product_create_form.title') }}
                </template>
                
                <template v-slot:content="{ toggle }">
                    <v-form v-slot="{ handleSubmit }">
                        <form
                            class="flex flex-col justify-start gap-4"
                            @submit="handleSubmit($event, submitProductForm)"
                        >
                            <div class="flex flex-col gap-1">
                                <label
                                    for="name"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.layout.header.product_create_form.name') }}
                                </label>

                                <v-field
                                    type="text"
                                    name="name"
                                    id="name"
                                    class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                    rules="required"
                                />

                                <v-error-message
                                    name="name"
                                    class="text-sm text-red-500"
                                />
                            </div>

                            <div :class="[showProductSkuField ? 'flex max-sm:flex-col max-sm:gap-4 gap-2.5' : '']">
                                <div
                                    v-if="showProductSkuField"
                                    class="flex flex-col gap-1"
                                >
                                    <label
                                        for="sku"
                                        class="text-base font-semibold leading-5 text-dark"
                                    >
                                        {{ $t('pos.layout.header.product_create_form.sku') }}
                                    </label>

                                    <v-field
                                        type="text"
                                        name="sku"
                                        id="sku"
                                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                        rules="required"
                                    />

                                    <v-error-message
                                        name="sku"
                                        class="text-sm text-red-500"
                                    />
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label
                                        for="price"
                                        class="text-base font-semibold leading-5 text-dark"
                                    >
                                        {{ $t('pos.layout.header.product_create_form.price') }}
                                    </label>

                                    <v-field
                                        type="text"
                                        name="price"
                                        id="price"
                                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                        rules="required|decimal"
                                    />

                                    <v-error-message
                                        name="price"
                                        class="text-sm text-red-500"
                                    />
                                </div>
                            </div>

                            <div class="flex gap-2.5 max-sm:flex-col max-sm:gap-4">
                                <div class="flex flex-col gap-1">
                                    <label
                                        for="quantity"
                                        class="text-base font-semibold leading-5 text-dark"
                                    >
                                        {{ $t('pos.layout.header.product_create_form.quantity') }}
                                    </label>

                                    <v-field
                                        type="text"
                                        name="quantity"
                                        id="quantity"
                                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                        rules="required|numeric"
                                    />

                                    <v-error-message
                                        name="quantity"
                                        class="text-sm text-red-500"
                                    />
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label
                                        for="weight"
                                        class="text-base font-semibold leading-5 text-dark"
                                    >
                                        {{ $t('pos.layout.header.product_create_form.weight') }}
                                    </label>

                                    <v-field
                                        type="text"
                                        name="weight"
                                        id="weight"
                                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                        rules="required|numeric"
                                    />

                                    <v-error-message
                                        name="weight"
                                        class="text-sm text-red-500"
                                    />
                                </div>
                            </div>

                            <div class="flex justify-end gap-6 max-sm:justify-between">
                                <button
                                    type="button"
                                    class="transparent-button w-36 max-sm:w-full"
                                    @click="toggle"
                                >
                                    {{ $t('pos.layout.header.product_create_form.cancel_btn_title') }}
                                </button>

                                <button
                                    type="submit"
                                    class="primary-button w-36 max-sm:w-full"
                                >
                                    {{ $t('pos.layout.header.product_create_form.proceed_btn_title') }}
                                </button>
                            </div>
                        </form>
                    </v-form>
                </template>
            </modal>
        </Teleport>
    </header>
</template>

<script setup>
    import { computed, inject, ref, onUnmounted, getCurrentInstance, onMounted, watchEffect, watch } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useRouter } from 'vue-router';
    import { useCookies } from '@src/composable/cookies';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { useMutation } from '@vue/apollo-composable';
    import { CREATE_PRODUCT } from '@src/graphql/products';
    import { LOGOUT } from '@src/graphql/session';

    /**
     * General Imports
     */
    const { t } = useI18n();
    const DB = useIndexedDB();
    const emitter = inject('emitter');
    const cookie = useCookies();
    const { appContext } = getCurrentInstance();
    const isOnline = computed(() => appContext.config.globalProperties.$isOnline.value);

    /**
     * Mobile Menu
     */
    const isMobileMenuOpen = ref(false);

    /**
     * Search Products
     */
    const searchProduct = (e) => {
        let searchTerm = e.target.value;

        emitter.emit('search_product', searchTerm);
    };

    /**
     * Product Form
     */
    const productCreateModal = ref(null);

    const { mutate: createProduct } = useMutation(CREATE_PRODUCT);

    const submitProductForm = (params, { setErrors, resetForm }) => {
        if (! isOnline.value) {
            emitter.emit('add_flash', {
                type: 'error',
                message: t('pos.common.flash_messages.offline_error'),
            });

            productCreateModal.value.toggle();

            return;
        }        

        createProduct({ input: {
            name: params.name,
            sku: params.sku || '',
            price: parseFloat(params.price),
            quantity: parseInt(params.quantity),
            weight: parseFloat(params.weight),
        } }).then((response) => {
            const { createOutletProduct } = response?.data;

            if (createOutletProduct?.success) {
                emitter.emit('add_flash', {
                    type: 'success',
                    message: createOutletProduct?.message,
                });

                resetForm();

                productCreateModal.value.toggle();                

                emitter.emit('add_to_cart', {
                    productId: createOutletProduct.product.id,
                    quantity: 1,
                });
            } else {
                if (createOutletProduct?.errors) {
                    setErrors(JSON.parse(createOutletProduct.errors));
                }
            }
        });
    };

    /**
     * Show Barcode Scanner
     */
    const showBarcodeScanner = ref(false);
    const showProductSkuField = ref(false);

    onMounted(() => {
        DB.getItem('configurations', 1).then(data => {
            showBarcodeScanner.value = data.configurations?.find(config => config.code == 'pos.settings.barcode.hide')?.value == '1' ? false : true;

            showProductSkuField.value = data.configurations?.find(config => config.code == 'pos.settings.product.allow_sku')?.value == '1' ? true : false;
        });
    });

    /**
     * Barcode Form
     */
    const barcodeModal = ref(null);

    const submitBarcodeForm = (e) => {
        let barcode = e.barcode;
        const prefix = getBarcodePrefix();

        if (
            prefix
            && ! barcode.startsWith(prefix)
        ) {
            barcode = prefix + barcode;
        }

        emitter.emit('search_barcode', barcode);

        barcodeModal.value.toggle();
    };

    /**
     * Update network status
     */
    const updateNetworkStatus = () => {
        appContext.config.globalProperties.$isOnline.value = !isOnline.value;
    };

    onUnmounted(() => {
        appContext.config.globalProperties.$removeNetworkListeners();
    });

    /**
     * Refresh page
     */
    const refreshPage = () => {
        cookie.remove('products_fetched');
        cookie.remove('customers_fetched');
        cookie.remove('orders_fetched');
        cookie.remove('categories_fetched');

        window.location.reload();
    };

    /**
     * Menu Items
     */
     const menus = ref([
        {
            name: 'home',
            path: '/home',
            icon: 'icon-home',
        },
        {
            name: 'customers',
            path: '/customers',
            icon: 'icon-customer',
        },
        {
            name: 'cashier',
            path: '/cashier',
            icon: 'icon-cashier',
        },
        {
            name: 'orders',
            path: '/orders',
            icon: 'icon-cart',
        },
        {
            name: 'products',
            path: '/products',
            icon: 'icon-product',
        },
        {
            name: 'reports',
            path: '/reports',
            icon: 'icon-reports',
        },
        {
            name: 'settings',
            path: '/settings',
            icon: 'icon-settings',
        },
    ]);

    /**
     * Get Agent Profile
     */
    const agent = ref({});

    watchEffect(async () => {
        const agents = await DB.getAllItems('agents');

        agent.value = agents[0];
    });


    /**
     * Logout
     */
    const { mutate:agentLogout } = useMutation(LOGOUT);

    const router = useRouter();

    const logout = async () => {
        try {
            const data = await agentLogout();

            if (data?.data?.agentLogout?.success) {
                localStorage.removeItem('accessToken');

                cookie.remove('products_fetched');
                cookie.remove('customers_fetched');
                cookie.remove('orders_fetched');
                cookie.remove('categories_fetched');

                router.push({path: '/'});
            }
        } catch (err) {}
    };

    /**
     * If mobile menu is open, disable body scroll
     */
    watch(isMobileMenuOpen, (value) => {
        if (value) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    });

    /**
     * Get Configurations
     */
    const getBarcodePrefix = () => {
        DB.getItem('configurations', 1).then(data => {
            return data.configurations?.find(config => config.code == 'pos.settings.barcode.prefix')?.value;
        });
    };
</script>
