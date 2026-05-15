<template>
    <div class="fixed top-[68px] hidden h-[calc(100vh-68px)] w-[420px] max-w-full flex-col justify-between bg-white shadow-[1px_0px_0px_0px_rgba(0,0,0,0.1)_inset] xl:flex 2xl:w-[460px] ltr:right-0 rtl:left-0">
        <template v-if="outletOrder?.order">
            <div class="flex flex-col px-3">
                <div class="flex flex-col px-2.5 py-4 shadow-[0px_-1px_0px_0px_rgba(0,0,0,0.1)_inset]">
                    <p class="text-2xl font-semibold text-dark">
                        {{ $t('pos.orders.info.order_id') }} #{{ outletOrder.order?.id }}
                    </p>

                    <p class="text-sm leading-4">
                        {{ outletOrder.order?.customer?.name }}
                    </p>
                </div>

                <div class="flex h-[calc(100vh-400px)] flex-col gap-1 overflow-y-auto overflow-x-hidden py-2">
                    <div
                        class="flex min-h-14 flex-col gap-4 rounded-lg p-2"
                        :class="[index % 2 == 0 ? 'bg-zinc-50' : '']"
                        v-for="(item, index) in outletOrder.order?.items"
                        :key="index"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex gap-2.5">
                                <p class="text-base font-semibold leading-5 text-dark">
                                    {{ index + 1 }}.
                                </p>

                                <div class="flex flex-col">
                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ item.name }}
                                    </p>

                                    <p class="text-sm font-normal leading-4 text-greyish">
                                        {{ item.sku }}
                                    </p>
                                </div>
                            </div>

                            <p class="text-base font-semibold leading-5 text-dark">
                                {{ item.formattedPrice?.price }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 bg-extraLight p-3">
                <div class="flex flex-col gap-1">
                    <div class="flex justify-between">
                        <p class="text-base font-normal leading-5 text-dark">
                            {{ $t('pos.orders.info.subtotal') }}
                        </p>

                        <p class="text-base font-semibold leading-5 text-dark">
                            {{ outletOrder.order?.formattedPrice?.subTotal }}
                        </p>
                    </div>

                    <div class="flex justify-between">
                        <p class="text-base font-normal leading-5 text-dark">
                            {{ $t('pos.orders.info.tax') }}
                        </p>

                        <p class="text-base font-semibold leading-5 text-dark">
                            {{ outletOrder.order?.formattedPrice?.taxAmount }}
                        </p>
                    </div>

                    <div class="flex justify-between">
                        <p class="text-base font-normal leading-5 text-dark">
                            {{ $t('pos.orders.info.discount') }}
                        </p>

                        <p class="text-base font-semibold leading-5 text-dark">
                            {{ outletOrder.order?.formattedPrice?.discountAmount }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-between">
                    <p class="text-xl font-medium leading-6 text-dark">
                        {{ $t('pos.common.cart.payable_amount') }}
                    </p>

                    <p class="text-xl font-medium leading-6 text-dark">
                        {{ outletOrder.order?.formattedPrice?.grandTotal }}
                    </p>
                </div>

                <div class="flex flex-col gap-1">
                    <div class="flex justify-between">
                        <p class="text-base font-normal leading-5 text-dark">
                            {{ $t('pos.orders.info.cash') }}
                        </p>

                        <p class="text-base font-semibold leading-5 text-dark">
                            {{ outletOrder?.customerCredit?.tenderedAmount }}
                        </p>
                    </div>

                    <div class="flex justify-between">
                        <p class="text-base font-normal leading-5 text-dark">
                            {{ $t('pos.orders.info.balance') }}
                        </p>

                        <p class="text-base font-semibold leading-5 text-dark">
                            {{ outletOrder?.customerCredit?.changeAmount }}
                        </p>
                    </div>
                </div>

                <div
                    class="grid gap-2.5"
                    :class="showSyncButton ? 'grid-cols-2' : 'grid-cols-1'"
                >
                    <button
                        v-if="showSyncButton"
                        type="button"
                        class="primary-button w-full"
                        @click="syncOrder(outletOrder)"
                    >
                        <span class="icon-sync text-2xl"></span>

                        {{ $t('pos.orders.offline.sync_order') }}
                    </button>

                    <Print :outletOrder="outletOrder" />
                </div>
            </div>
        </template>

        <template v-else>
            <div class="flex h-full items-center justify-center px-8">
                <p class="text-base font-normal text-dark">
                    {{ $t('pos.orders.info.no_order_selected') }}
                </p>
            </div>
        </template>
    </div>
</template>

<script setup>
    import { ref } from 'vue';
    import Print from '@components/secured/common/Print.vue';

    defineProps({
        outletOrder: {
            type: Object,
            required: true,
        },
        showSyncButton: {
            type: Boolean,
            required: false,
        },
    });

    /**
     * Sync order.
     */
    const emit = defineEmits(['syncOrder']);

    const syncOrder = (outletOrder) => {
        emit('syncOrder', outletOrder);
    };
</script>
