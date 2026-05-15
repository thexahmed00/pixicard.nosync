<template>
    <div class="flex gap-4">
        <div class="my-4 flex max-w-full flex-col gap-4">
            <div class="custom-scrollbar flex h-[50px] w-full gap-2.5 overflow-x-auto overflow-y-hidden rounded-lg bg-white px-3 py-2 xl:w-[738px] 2xl:w-[828px]">
                <span
                    @click="filterProductsByCategoryId(0)"
                    :class="[activeCategoryId == 0 ? 'bg-light text-primary !border-primary' : 'text-dark']"
                    class="flex h-8 cursor-pointer items-center whitespace-nowrap rounded-lg border-2 border-transparent px-3 py-2 text-base font-medium"
                >
                    {{ $t('pos.home.categories.all') }}
                </span>

                <div
                    class="flex gap-2.5"
                    v-for="(category, index) in categories"
                    :key="index"
                >
                    <category
                        :category="category"
                        :activeCategoryId="activeCategoryId"
                        @click="filterProductsByCategoryId"
                    />

                    <!-- Second-level categories -->
                    <div
                        v-if="
                            category.children?.length
                            && (
                                activeCategoryId === category.id
                                || isChildActive(category.children)
                            )
                        "
                        class="flex items-center gap-2.5"
                    >
                        <span class="icon-chevron-right text-xl text-primary"></span>

                        <div
                            class="flex items-center gap-2.5"
                            v-for="(childrenCategory, index) in category.children"
                            :key="index"
                        >
                            <category
                                :category="childrenCategory"
                                :activeCategoryId="activeCategoryId"
                                @click="filterProductsByCategoryId"
                            />

                            <!-- Third-level categories -->
                            <div
                                v-if="
                                    childrenCategory.children?.length
                                    && (
                                        activeCategoryId === childrenCategory.id
                                        || isChildActive(childrenCategory.children)
                                    )
                                "
                                class="flex items-center gap-2.5"
                            >
                                <span class="icon-chevron-right text-xl text-primary"></span>
                                
                                <div
                                    class="flex items-center gap-2.5"
                                    v-for="(grandChildrenCategory, index) in childrenCategory.children"
                                    :key="index"
                                >
                                    <category
                                        :category="grandChildrenCategory"
                                        :activeCategoryId="activeCategoryId"
                                        @click="filterProductsByCategoryId"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:w-[738px] 2xl:w-[828px]">
                <div
                    class="box-shadow flex cursor-pointer flex-col gap-2 rounded-lg bg-white p-3"
                    v-for="(product, index) in products"
                    :key="index"
                    @click="addToCart(product)"
                >
                    <template v-if="product.images.length">
                        <img
                            :src="product.images[0].url"
                            class="aspect-square rounded sm:max-w-[150px] md:max-w-[210px]"
                            alt="product image"
                        >
                    </template>

                    <template v-else>
                        <img
                            src="/src/assets/images/product-placeholder.webp"
                            class="aspect-square rounded sm:max-w-[150px] md:max-w-[210px]"
                            alt="product image"
                        >
                    </template>

                    <div class="flex h-full flex-col justify-between gap-2">
                        <div class="flex flex-col items-center gap-1">
                            <p class="truncate-text-2 text-center text-base font-normal leading-5 text-dark">
                                {{ product.name }}
                            </p>

                            <p
                                class="flex gap-1.5 text-base font-normal leading-5 text-dark"
                                v-html="product.priceHtml"
                            >
                            </p>
                        </div>

                        <div class="flex items-center justify-between">
                            <template v-if="['simple', 'virtual'].includes(product.type)">
                                <p class="text-[12px] font-semibold text-dark">
                                    {{ $t('pos.home.product.view.qty', { qty: product.quantity }) }}
                                </p>
                            </template>

                            <template v-else>
                                <p class="text-[12px] font-semibold">
                                    N/A
                                </p>
                            </template>

                            <div class="flex h-10 w-10 items-center justify-center rounded-md bg-light p-1">
                                <span class="icon-attribute text-2xl text-primary"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <!-- Cart (Mobile view only) -->
            <mobile-cart v-if="isMobileOrTab" />

            <modal ref="productModal">
                <template v-slot:header="{ toggle }">
                    <div class="flex justify-between gap-2.5">
                        {{ modalProduct.name }}

                        <span
                            class="icon-cross cursor-pointer text-2xl text-dark hover:text-primary"
                            @click="toggle"
                        ></span>
                    </div>
                </template>
                
                <template v-slot:content>
                    <View
                        :product="modalProduct"
                        :productModal="productModal"
                    />
                </template>
            </modal>

            <modal ref="receiptPrintModal">
                <template v-slot:header="{ toggle }">
                    <div class="flex justify-between gap-2.5">
                        <div class="flex items-center gap-2.5">
                            <span class="icon-warning cursor-pointer text-2xl text-dark"></span>
                            
                            <p class="text-2xl font-semibold text-dark">
                                {{ $t('pos.home.print_confirmation_title') }}
                            </p>
                        </div>

                        <span
                            class="icon-cross cursor-pointer text-2xl text-dark hover:text-primary"
                            @click="toggle"
                        ></span>
                    </div>
                </template>
                
                <template v-slot:content>
                    <p class="pb-2.5 text-base font-normal text-dark">
                        {{ $t('pos.home.print_confirm_message') }}
                    </p>

                    <Print :outletOrder="outletOrder" />
                </template>
            </modal>
        </Teleport>

        <!-- Cart (Desktop View Only) -->
        <cart v-if="! isMobileOrTab"/>
    </div>
</template>

<script setup>
    import { ref, onMounted, inject, onBeforeUnmount } from 'vue';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { useWindowWidth } from '@src/composable/window';
    import Category from '@components/secured/home/Category.vue';
    import View from '@components/secured/home/product/View.vue';
    import Print from '@components/secured/common/Print.vue';
    import MobileCart from '@components/secured/home/MobileCart.vue';

    /**
     * General variables
     */
    const emitter = inject('emitter');
    const DB = useIndexedDB();
    const { isMobileOrTab } = useWindowWidth();
    const activeCategoryId = ref(0);

    /**
     * Check if child category is active
     */
    const isChildActive = (children) => {
        return children.some(child => {
            return (
                child.id === activeCategoryId.value
                || (
                    child.children?.length
                    && isChildActive(child.children)
                )
            );
        });
    };

    /**
     * Filter Products by Category Id
     */
    const filterProductsByCategoryId = async (categoryId) => {
        activeCategoryId.value = categoryId;

        const data = await DB.getItem('products', 'all');

        if (! data?.products.length) {
            return;
        }

        if (categoryId) {
            products.value = data.products.filter(product => 
                product.categories.some(category => category.id == categoryId)
            );
        } else {
            products.value = data.products;
        }
    };

    /**
     * Fetch products and categories
     */
    const products = ref([]);
    const categories = ref([]);
    
    onMounted(async () => {
        categories.value = await DB.getItem('categories', 'all').then((data) => data?.categories) || [];

        products.value = await DB.getItem('products', 'all').then((data) => data?.products) || [];
    });

    /**
     * Search product
     */
    const searchProduct = async (searchTerm) => {        
        if (searchTerm) {
            products.value = await DB.getItem('products', 'all').then((data) => {
                return data.products.filter(product => {
                    const term = searchTerm.toLowerCase();

                    return product.name.toLowerCase().includes(term)
                        || product.sku.toLowerCase().includes(term);
                });
            });
        } else {
            const data = await DB.getItem('products', 'all').then((data) => data?.products);

            products.value = data;
        }
    };

    /**
     * Search product by barcode
     */
    const searchBarcodeProduct = (barcode) => {
        const product = products.value.find(product => product.barcode === barcode);        

        if (product) {
            addToCart(product);
        }        
    };

    /**
     * Add product to cart
     */
    const productModal = ref(null);
    const modalProduct = ref(null);

    const addToCart = (product) => {
        if (['simple', 'virtual'].includes(product.type)) {
            const productData = {
                productId: product.id,
                quantity: 1,
            };

            emitter.emit('add_to_cart', productData);
        } else {
            modalProduct.value = product;
            
            productModal.value.toggle();
        }
    }

    /**
     * Listen for search product event
     */
    const registerEvents = () => {
        emitter.on('products_fetched', (data) => products.value = data);
        emitter.on('categories_fetched', (data) => categories.value = data);
        emitter.on('search_barcode', searchBarcodeProduct);
        emitter.on('search_product', searchProduct);
    };

    /**
     * Check for receipt print
     */
    const outletOrder = ref(null);

    const receiptPrintModal = ref(null);

    const checkForReceiptPrint = () => {
        DB.getAllItems('agents').then(agent => {
            const outletOrderData = localStorage.getItem('outlet_order');        

            if (
                agent.length
                && Boolean(agent[0]?.outlet?.receipt?.showPrintConfirmation)
                && outletOrderData
            ) {
                outletOrder.value = JSON.parse(outletOrderData);

                receiptPrintModal.value.toggle();
            }

            localStorage.removeItem('outlet_order');
        });
    };

    onMounted(() => {
        registerEvents();

        checkForReceiptPrint();
    });

    onBeforeUnmount(() => {
        emitter.off('products_fetched');
        emitter.off('categories_fetched');
        emitter.off('search_barcode');
        emitter.off('search_product');
    });
</script>