<template>
    <div class="md:box-shadow grid gap-4 rounded-lg bg-white py-1 md:px-4 xl:w-[738px] 2xl:w-[828px]">
        <div class="relative flex items-center text-dark">
            <input
                type="text"
                class="h-12 w-full px-8 text-base font-normal leading-5 shadow-[0px_-1px_0px_0px_rgba(0,0,0,0.1)_inset]"
                :placeholder="$t('pos.products.requested_products.search_products')"
                v-model="query"
            >

            <i class="icon-search left absolute top-3.5 flex items-center text-xl leading-5"></i>
        </div>

        <template v-if="loading">
            <RequestedProductSkeleton />
        </template>

        <template v-else-if="!products.length">
            <div class="mb-4 flex items-center justify-center">
                <p class="text-base font-normal leading-5 text-dark">
                    {{ $t('pos.products.requested_products.no_products') }}
                </p>
            </div>
        </template>

        <template v-else>
            <div class="grid w-full content-start gap-2">
                <div
                    class="grid gap-4 rounded-lg p-6 max-sm:p-4"
                    :class="[index % 2 == 0 ? 'bg-zinc-50' : '']"
                    v-for="(product, index) in products"
                    :key="index"
                >
                    <div class="flex justify-between gap-y-2.5 max-sm:flex-col md:items-center">
                        <p class="text-base font-medium leading-5 text-dark">
                            {{ product.name }}
                        </p>

                        <div class="flex justify-between gap-6">
                            <p class="text-base leading-5 text-dark">
                                {{ $t('pos.products.requested_products.qty', { qty: product.requestedQuantity }) }}
                            </p>

                            <template v-if="product.requestStatus == 1">
                                <span class="label-success">
                                    {{ $t('pos.products.requested_products.received') }}
                                </span>
                            </template>

                            <template v-else-if="product.requestStatus == 2">
                                <span class="label-closed">
                                    {{ $t('pos.products.requested_products.declined') }}
                                </span>
                            </template>

                            <template v-else>
                                <span class="label-pending">
                                    {{ $t('pos.products.requested_products.pending') }}
                                </span>
                            </template>

                            <p class="text-base leading-5 text-dark">
                                {{ product.createdAt }}
                            </p>
                        </div>
                    </div>

                    <p class="text-justify text-base leading-5 text-dark">
                        {{ product.comment }}
                    </p>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
    import { ref, computed, watch } from 'vue';
    import { useQuery } from '@vue/apollo-composable';
    import { GET_REQUESTED_PRODUCTS } from '@src/graphql/products';
    import RequestedProductSkeleton from '@skeletons/products/Requested.vue';

    /**
     * GraphQL query to get customers
     */
    const query = ref('');

    const { result, loading, refetch } = useQuery(GET_REQUESTED_PRODUCTS, {
        query: query.value,
    });

    const products = computed(() => result.value?.getRequestedProducts ?? []);

    watch(query, () => {
        refetch({
            query: query.value,
        });
    });
</script>