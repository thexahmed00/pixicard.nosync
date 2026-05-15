<template>
    <div class="flex gap-4 max-sm:flex-col">
        <div class="rounded-lg bg-white w-full md:px-4 xl:w-[738px] 2xl:w-[828px]">
            <div class="relative mb-4 flex items-center text-dark">
                <input
                    type="text"
                    class="h-12 w-full px-8 text-base font-normal leading-5 shadow-[0px_-1px_0px_0px_rgba(0,0,0,0.1)_inset]"
                    :placeholder="$t('pos.orders.offline.search_order_id')"
                    @input="searchOrder($event)"
                >

                <i class="icon-search left absolute top-3.5 flex items-center text-xl leading-5"></i>
            </div>
            
            <template v-if="isOrdersListLoading">
                <OrderListSkeleton />
            </template>

            <template v-else>
                <!-- Desktop view -->
                <div class="min-w-full hidden xl:block">
                    <table class="mb-4 min-w-full">
                        <thead>
                            <tr class="h-12 rounded-lg bg-zinc-50 ltr:text-left rtl:text-right">
                                <th class="px-4 py-2">
                                    {{ $t('pos.orders.history.order_id') }}
                                </th>

                                <th class="px-4 py-2">
                                    {{ $t('pos.orders.history.date') }}
                                </th>

                                <th class="px-4 py-2">
                                    {{ $t('pos.orders.history.total_sales') }}
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <template v-if="outletOrders.length">
                                <tr
                                    class="h-12 cursor-pointer rounded-lg text-dark ltr:text-left rtl:text-right"
                                    :class="index % 2 === 0 ? 'bg-zinc-50' : ''"
                                    v-for="(outletOrder, index) in outletOrders"
                                    :key="index"
                                    @click="currentOrder = outletOrder"
                                >
                                    <td class="px-4 py-2">
                                        #{{ outletOrder?.order?.id }}
                                    </td>

                                    <td class="px-4 py-2">
                                        {{ outletOrder?.order?.createdAt }}
                                    </td>

                                    <td class="px-4 py-2">
                                        {{ outletOrder?.order?.formattedPrice?.subTotal }}
                                    </td>
                                </tr>
                            </template>

                            <template v-else>
                                <tr>
                                    <td
                                        class="py-8 text-center"
                                        colspan="5"
                                    >
                                        {{ $t('pos.orders.history.no_records') }}
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile View -->
                <div
                    v-if="outletOrders?.length"
                    class="grid gap-2.5 xl:hidden"
                >
                    <div
                        class="rounded-lg px-2.5 py-4 text-dark"
                        :class="index % 2 === 0 ? 'bg-zinc-50' : ''"
                        v-for="(outletOrder, index) in outletOrders"
                        :key="index"
                        @click="showOrderInfo(outletOrder)"
                    >
                        <div class="flex flex-col gap-y-1">
                            <div class="flex justify-between gap-x-2.5">
                                <p class="text-base leading-5">
                                    # {{ outletOrder?.order?.id }}
                                </p>

                                <p class="text-base leading-5">
                                    {{ outletOrder?.order?.formattedPrice?.subTotal }}
                                </p>
                            </div>

                            <p class="text-base leading-5">
                                {{ outletOrder?.order?.createdAt }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="py-4 text-center xl:hidden"
                >
                    {{ $t('pos.orders.history.no_records') }}
                </div>
            </template>
        </div>

        <!-- For Desktop view -->
        <Info :outletOrder="currentOrder" />

        <!-- For Mobile view -->
        <Teleport to="body">
            <drawer ref="orderInfoDrawer">
                <template v-slot:header>
                    <div class="flex flex-col px-2 py-4">
                        <p class="text-2xl font-semibold text-dark">
                            {{ $t('pos.orders.info.order_id') }} #{{ currentOrder.order?.id }}
                        </p>

                        <p class="text-sm leading-4">
                            {{ currentOrder.order?.customer?.name }}
                        </p>
                    </div>
                </template>

                <template v-slot:content>
                    <div class="flex min-h-full flex-col justify-between">
                        <div class="flex flex-col gap-1 overflow-hidden p-4">
                            <div
                                class="flex min-h-14 flex-col gap-4 rounded-lg p-2"
                                :class="[index % 2 == 0 ? 'bg-zinc-50' : '']"
                                v-for="(item, index) in currentOrder?.order?.items"
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

                        <div class="flex flex-col gap-4 bg-extraLight p-4">
                            <div class="flex flex-col gap-1">
                                <div class="flex justify-between">
                                    <p class="text-base font-normal leading-5 text-dark">
                                        {{ $t('pos.orders.info.subtotal') }}
                                    </p>

                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ currentOrder.order?.formattedPrice?.subTotal }}
                                    </p>
                                </div>

                                <div class="flex justify-between">
                                    <p class="text-base font-normal leading-5 text-dark">
                                        {{ $t('pos.orders.info.tax') }}
                                    </p>

                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ currentOrder.order?.formattedPrice?.taxAmount }}
                                    </p>
                                </div>

                                <div class="flex justify-between">
                                    <p class="text-base font-normal leading-5 text-dark">
                                        {{ $t('pos.orders.info.discount') }}
                                    </p>

                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ currentOrder.order?.formattedPrice?.discountAmount }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-between">
                                <p class="text-xl font-medium leading-6 text-dark">
                                    {{ $t('pos.common.cart.payable_amount') }}
                                </p>

                                <p class="text-xl font-medium leading-6 text-dark">
                                    {{ currentOrder.order?.formattedPrice?.grandTotal }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-1">
                                <div class="flex justify-between">
                                    <p class="text-base font-normal leading-5 text-dark">
                                        {{ $t('pos.orders.info.cash') }}
                                    </p>

                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ currentOrder?.customerCredit?.tenderedAmount }}
                                    </p>
                                </div>

                                <div class="flex justify-between">
                                    <p class="text-base font-normal leading-5 text-dark">
                                        {{ $t('pos.orders.info.balance') }}
                                    </p>

                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ currentOrder?.customerCredit?.changeAmount }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-slot:footer>
                    <Print :outletOrder="currentOrder" />
                </template>
            </drawer>
        </Teleport>
    </div>
</template>

<script setup>
    import { ref, onBeforeMount } from 'vue';
    import Info from '@components/secured/orders/Info.vue';
    import Print from '@components/secured/common/Print.vue';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import OrderListSkeleton from '@skeletons/orders/List.vue';

    /**
     * General variables
     */
    const DB = useIndexedDB();

    /**
     * Fetch orders
     */
    const outletOrders = ref([]);
    const currentOrder = ref({});
    const isOrdersListLoading = ref(true);

    onBeforeMount(async () => {
        outletOrders.value = await DB.getItem('orders', 'all').then((data) => data.orders);

        isOrdersListLoading.value = false;
    });

    /**
     * Show order info
     */
    const orderInfoDrawer = ref(null);
    const showOrderInfo = (order) => {
        currentOrder.value = order;

        orderInfoDrawer.value.open();
    };

    /**
     * Search order
     */
    const searchOrder = async (event) => {
        const searchValue = event.target.value;

        if (searchValue) {
            isOrdersListLoading.value = true;

            outletOrders.value = await DB.getItem('orders', 'all').then((data) => {
                return data.orders.filter((order) => {                    
                    return order.order.customerFirstName.toLowerCase().includes(searchValue)
                        || order.order.customerLastName.toLowerCase().includes(searchValue)
                        || order.order.customerEmail.toLowerCase().includes(searchValue)
                        || order.order.id.toString().includes(searchValue);
                });
            });

            isOrdersListLoading.value = false;
        } else {
            outletOrders.value = await DB.getItem('orders', 'all').then((data) => data.orders);
        }
    };
</script>