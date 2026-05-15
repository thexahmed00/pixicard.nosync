<template>
    <div class="max-w-full max-sm:w-full">
        <span
            class="flex items-center gap-2.5"
            v-html="product.priceHtml"
        >
        </span>

        <div
            v-if="product.groupedProducts.length"
            class="mt-4 grid gap-5 max-sm:mt-3 max-sm:gap-3"
        >
            <div
                class="flex items-center justify-between gap-5"
                v-for="(groupedProduct, index) in product.groupedProducts"
                :key="index"
            >
                <template v-if="groupedProduct.associatedProduct.isSaleable">
                    <div class="text-sm font-medium">
                        <p class="text-base">
                            {{ $t('pos.home.product.view.type.grouped.name') }}
                        </p>

                        <p class="mt-1.5 text-zinc-500">
                            {{ `${groupedProduct.associatedProduct.name} + ${groupedProduct.associatedProduct.priceHtml.minPrice}` }}
                        </p>
                    </div>

                    <quantity-changer
                        :name="`qty[${groupedProduct.associatedProductId}]`"
                        :value="groupedProduct.qty"
                    />
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
    import QuantityChanger from '@components/secured/common/QuantityChanger.vue';

    defineProps({
        product: {
            type: Object,
            required: true,
        },
    });
</script>