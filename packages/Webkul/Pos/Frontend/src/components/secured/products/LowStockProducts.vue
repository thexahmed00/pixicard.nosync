<template>
    <ProductCardSkeleton v-if="loading" />

    <div
        v-else-if="!products.length"
        class="md:box-shadow flex items-center justify-center rounded-lg bg-white p-5"
    >
        <p class="text-base font-normal leading-5 text-dark">
            {{ $t('pos.products.low_stock_products.no_products') }}
        </p>
    </div>

    <div
        v-else
        class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:w-[738px] 2xl:w-[828px]"
    >
        <div
            class="box-shadow flex cursor-pointer flex-col gap-2 rounded-lg bg-white p-3"
            v-for="(product, index) in products"
            :key="index"
            @click="openLowStockRequestModal(product)"
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
                    <p class="text-[12px] font-semibold leading-4 text-dark">
                        {{ $t('pos.products.low_stock_products.qty', { qty: product.quantity }) }}
                    </p>

                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-light p-1">
                        <span class="icon-attribute text-2xl text-primary"></span>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <modal ref="lowStockRequestModal">
                <template v-slot:header>
                    {{ $t('pos.products.low_stock_products.request.title') }}
                </template>
                
                <template v-slot:content="{ toggle }">
                    <v-form
                        class="grid gap-4"
                        @submit="submitLowStockRequestForm"
                    >
                        <div class="grid gap-1">
                            <label
                                for="requested_quantity"
                                class="text-base font-semibold leading-5 text-dark"
                            >
                                {{ $t('pos.products.low_stock_products.request.requested_quantity') }}
                            </label>

                            <v-field
                                type="text"
                                name="requested_quantity"
                                id="requested_quantity"
                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                rules="required|numeric"
                                v-model="productRequest.requestedQuantity"
                                :placeholder="100"
                                :label="$t('pos.products.low_stock_products.request.requested_quantity')"
                            />

                            <v-error-message
                                name="requested_quantity"
                                class="text-sm text-red-500"
                            />
                        </div>

                        <div class="grid gap-1">
                            <label
                                for="comment"
                                class="text-base font-semibold leading-5 text-dark"
                            >
                                {{ $t('pos.products.low_stock_products.request.comment') }}
                            </label>

                            <v-field
                                name="comment"
                                v-slot="{ field }"
                                :label="$t('pos.products.low_stock_products.request.comment')"
                            >
                                <textarea
                                    name="comment"
                                    id="comment"
                                    v-bind="field"
                                    v-model="productRequest.comment"
                                    class="hover:border-light-700 focus:border-light-700 h-20 w-full resize-none rounded-lg border border-greyish px-2.5 py-3 text-base font-normal leading-5 transition-all"
                                    :placeholder="$t('pos.products.low_stock_products.request.comment_placeholder')"
                                />
                            </v-field>

                            <v-error-message
                                name="comment"
                                class="text-sm text-red-500"
                            />
                        </div>

                        <div class="flex justify-end gap-6">
                            <button
                                type="button"
                                class="transparent-button w-36"
                                @click="toggle"
                            >
                                {{ $t('pos.products.low_stock_products.request.cancel_btn_title') }}
                            </button>

                            <button
                                type="submit"
                                class="primary-button w-36"
                            >
                                {{ $t('pos.products.low_stock_products.request.add_btn_title') }}
                            </button>
                        </div>
                    </v-form>
                </template>
            </modal>
        </Teleport>
    </div>
</template>

<script setup>
    import { ref, computed } from 'vue';
    import { useQuery } from '@vue/apollo-composable';
    import { GET_LOW_STOCK_PRODUCTS } from '@src/graphql/products';
    import ProductCardSkeleton from '@skeletons/products/Card.vue';

    /**
     * General Variables
     */
    const lowStockRequestModal = ref(null);
    const productRequest = ref({
        productId: null,
        name: null,
        requestedQuantity: null,
        comment: null,
    });
    const emit = defineEmits(['purchaseRequest']);

    /**
     * Sending the purchase request
     */
    const openLowStockRequestModal = (product) => {
        productRequest.value.productId = product.id;
        productRequest.value.name = product.name;

        lowStockRequestModal.value.toggle();
    };

    /**
     * Submitting the low stock request form
     */
    const submitLowStockRequestForm = async () => {
        lowStockRequestModal.value.toggle();

        emit('purchaseRequest', productRequest.value);

        productRequest.value = {
            productId: null,
            name: null,
            requestedQuantity: null,
            comment: null,
        };
    };

    /**
     * Fetching the minimum quantity
     */
    const { result, loading } = useQuery(GET_LOW_STOCK_PRODUCTS);

    const products = computed(() => result.value?.getLowStockProducts ?? []);
</script>