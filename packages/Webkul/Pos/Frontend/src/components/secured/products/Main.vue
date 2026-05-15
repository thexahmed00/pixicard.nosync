<template>
    <div class="flex gap-4">
        <div class="my-4 flex w-full flex-col gap-4">
            <div class="flex gap-2.5 rounded-lg bg-white py-2 max-sm:text-center md:h-12 md:px-3 xl:w-[738px] 2xl:w-[828px]">
                <div
                    v-on:click="activeTab = index"
                    :class="[activeTab === index ? 'bg-light text-primary !border-primary' : 'text-dark']"
                    class="flex cursor-pointer items-center rounded-lg border-2 border-transparent px-3 py-2 text-base font-medium"
                    v-for="(component, index) in components"
                    :key="index"
                >
                    {{ $t(`pos.products.${index}.title`) }}
                </div>
            </div>

            <KeepAlive>
                <component
                    :is="components[activeTab]"
                    @purchaseRequest="handlePurchaseRequest"
                />
            </KeepAlive>
        </div>

        <template v-if="activeTab === 'low_stock_products'">
            <!-- Mobile View -->
            <div
                v-if="products.length"
                class="fixed bottom-0 left-0 right-0 flex w-full items-center justify-between gap-10 border-t bg-white p-2.5 xl:hidden"
            >
                <p class="text-base font-medium text-dark">
                    {{ $t('pos.products.low_stock_products.items_in_request', { count: products.length }) }}
                </p>

                <button
                    class="secondary-button w-1/2"
                    @click="$refs.purchaseRequestDrawer.toggle()"
                >
                    <span class="icon-eye text-2xl leading-6"></span>

                    {{ $t('pos.products.low_stock_products.view_btn_title') }}
                </button>
            </div>

            <!-- Desktop View -->
            <div class="fixed hidden h-[calc(100vh-68px)] w-[420px] max-w-full flex-col justify-between bg-white shadow-[1px_0px_0px_0px_rgba(0,0,0,0.1)_inset] xl:flex ltr:right-0 rtl:left-0">
                <div class="grid">
                    <div class="px-2.5 py-4 shadow-[0px_-1px_0px_0px_rgba(0,0,0,0.1)_inset]">
                        <p class="flex h-12 items-center text-xl font-medium text-dark">
                            {{ $t('pos.products.low_stock_products.purchase_request') }}
                        </p>
                    </div>

                    <div class="h-[calc(100vh-220px)] overflow-y-auto py-2">
                        <template v-if="products.length">
                            <div
                                class="grid min-h-14 rounded-lg p-2"
                                :class="[index % 2 != 0 ? 'bg-zinc-50' : '']"
                                v-for="(product, index) in products"
                                :key="index"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <div
                                        class="flex cursor-pointer items-center gap-2"
                                        @click="showHide(index)"
                                    >
                                        <span
                                            :class="[showMore[index] ? 'icon-chevron-down' : 'icon-chevron-right']"
                                            class="text-2xl leading-6 text-greyish"
                                        >
                                        </span>

                                        <p class="text-base font-semibold leading-5 text-dark">
                                            {{ index + 1 }}
                                        </p>

                                        <p class="text-base font-semibold leading-5 text-dark">
                                            {{ product.name }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <p class="text-base font-semibold leading-5 text-dark">
                                            {{ $t('pos.products.low_stock_products.qty', { qty: product.requestedQuantity }) }}
                                        </p>

                                        <div
                                            class="flex h-4 w-4 cursor-pointer items-center justify-center rounded-full bg-greyish"
                                            @click="products.splice(index, 1)"
                                        >
                                            <span class="icon-cross text-base leading-6 text-white"></span>
                                        </div>
                                    </div>
                                </div>

                                <p
                                    class="px-8 text-sm text-greyish"
                                    v-if="showMore[index]"
                                >
                                    {{ product.comment }}
                                </p>
                            </div>
                        </template>

                        <template v-else>
                            <div class="flex h-full items-center justify-center">
                                <p class="text-base text-greyish">
                                    {{ $t('pos.products.low_stock_products.no_purchase_request') }}
                                </p>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="bg-extraLight p-3">
                    <Button
                        :isLoading="isRequestProcessing"
                        class="secondary-button w-full"
                        :class="[! products.length ? 'opacity-50 cursor-not-allowed' : '']"
                        icon="icon-checkout"
                        :label="$t('pos.products.low_stock_products.send_request_btn')"
                        @click="requestProductQuantity"
                    />
                </div>
            </div>
        </template>

        <!-- Purchase Request Drawer (Mobile View Only) -->
        <Teleport to="body">
            <drawer ref="purchaseRequestDrawer">
                <template v-slot:header>
                    <p class="mx-4 text-2xl font-semibold text-dark">
                        {{ $t('pos.products.low_stock_products.purchase_request') }}
                    </p>
                </template>

                <template v-slot:content>
                    <template v-if="products.length">
                        <div
                            class="grid min-h-14 rounded-lg p-4"
                            :class="[index % 2 != 0 ? 'bg-zinc-50' : '']"
                            v-for="(product, index) in products"
                            :key="index"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div
                                    class="flex cursor-pointer items-center gap-2"
                                    @click="showHide(index)"
                                >
                                    <span
                                        :class="[showMore[index] ? 'icon-chevron-down' : 'icon-chevron-right']"
                                        class="text-2xl leading-6 text-greyish"
                                    >
                                    </span>

                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ index + 1 }}
                                    </p>

                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ product.name }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ $t('pos.products.low_stock_products.qty', { qty: product.requestedQuantity }) }}
                                    </p>

                                    <div
                                        class="flex h-4 w-4 cursor-pointer items-center justify-center rounded-full bg-greyish"
                                        @click="products.splice(index, 1)"
                                    >
                                        <span class="icon-cross text-base leading-6 text-white"></span>
                                    </div>
                                </div>
                            </div>

                            <p
                                class="px-8 text-sm text-greyish"
                                v-if="showMore[index]"
                            >
                                {{ product.comment }}
                            </p>
                        </div>
                    </template>

                    <template v-else>
                        <div class="flex h-full items-center justify-center">
                            <p class="text-base text-greyish">
                                {{ $t('pos.products.low_stock_products.no_purchase_request') }}
                            </p>
                        </div>
                    </template>
                </template>

                <template v-slot:footer>
                    <Button
                        :isLoading="isRequestProcessing"
                        class="secondary-button w-full"
                        :class="[! products.length ? 'opacity-50 cursor-not-allowed' : '']"
                        icon="icon-checkout"
                        :label="$t('pos.products.low_stock_products.send_request_btn')"
                        @click="requestProductQuantity"
                    />
                </template>
            </drawer>
        </Teleport>
    </div>
</template>

<script setup>
    import { ref, inject } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useMutation } from '@vue/apollo-composable';
    import { REQUEST_PRODUCT_QTY } from '@src/graphql/products';
    import LowStockProducts from '@components/secured/products/LowStockProducts.vue';
    import RequestedProducts from '@components/secured/products/RequestedProducts.vue';
    import Settings from '@components/secured/products/Settings.vue';

    /**
     * General Variables
     */
    const emitter = inject('emitter');
    const { t } = useI18n();
    const components = {
        'low_stock_products': LowStockProducts,
        'requested_products': RequestedProducts,
        'settings': Settings,
    };
    const products = ref([]);

    /**
     * Handle Purchase Request
     */
    const handlePurchaseRequest = (request) => {
        let alreadyExists = products.value.find((item) => item.productId === request.productId);

        if (alreadyExists) {
            emitter.emit('add_flash', {
                type: 'warning',
                message: t('pos.products.low_stock_products.request.already_exist'),
            });
        } else {
            products.value.push(request);
        }
    };

    /**
     * send the product quantity request
     */
    const purchaseRequestDrawer = ref(null);

    const { mutate, loading: isRequestProcessing } = useMutation(REQUEST_PRODUCT_QTY, {
        onCompleted: () => {
            products.value = [];
        },
    });

    const requestProductQuantity = () => {
        if (products.value.length) {
            mutate({ input: {
                products: products.value.map((product) => ({
                    productId: parseInt(product.productId),
                    requestedQuantity: parseInt(product.requestedQuantity),
                    comment: product.comment,
                })),
            }}).then((response) => {
                if (response.data.requestProductQty.success) {
                    emitter.emit('add_flash', {
                        type: 'success',
                        message: response.data.requestProductQty.message,
                    });

                    products.value = [];
                }
            }).catch((error) => {
                emitter.emit('add_flash', {
                    type: 'error',
                    message: error.message,
                });
            }).finally(() => {
                purchaseRequestDrawer.value.close();
            })
        }
    };

    /**
     * Active Tab
     */
    const activeTab = ref('low_stock_products');
    const showMore = ref({});

    const showHide = (key) => {
        showMore.value[key] = !showMore.value[key];
    }
</script>