<template>
    <div class="flex gap-4">
        <div class="flex flex-1 flex-col gap-4 max-xl:flex-auto">
            <div class="grid gap-4 xl:w-[738px] 2xl:w-[828px]">
                <div class="flex items-center justify-between">
                    <p class="text-2xl font-semibold text-primary">
                        {{ $t('pos.orders.offline.title') }}
                    </p>

                    <button
                        v-if="isOnline"
                        type="button"
                        class="transparent-button md:primary-button"
                        @click="syncOfflineOrders"
                    >
                        <span class="icon-sync text-2xl"></span>

                        <span class="max-sm:hidden">
                            {{ $t('pos.orders.offline.sync_all_orders') }}
                        </span>
                    </button>
                </div>

                <div class="md:box-shadow grid gap-4 rounded-lg bg-white py-1 md:px-4">
                    <div class="relative flex items-center text-dark">
                        <input
                            type="text"
                            class="h-12 w-full px-8 text-base font-normal leading-5 shadow-[0px_-1px_0px_0px_rgba(0,0,0,0.1)_inset]"
                            :placeholder="$t('pos.orders.offline.search_order_id')"
                            @input="searchOrder"
                        >

                        <i class="icon-search left absolute top-3.5 flex items-center text-xl leading-5"></i>
                    </div>

                    <!-- Desktop View -->
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
                                </tr>
                            </thead>

                            <tbody>
                                <template v-if="offlineOrders.length">
                                    <tr
                                        class="h-12 cursor-pointer rounded-lg text-dark ltr:text-left rtl:text-right"
                                        :class="index % 2 === 0 ? 'bg-zinc-50' : ''"
                                        v-for="(order, index) in offlineOrders"
                                        :key="index"
                                        @click="currentOrder = order"
                                    >
                                        <td class="px-4 py-2">#{{ order.id }}</td>
                                        <td class="px-4 py-2">{{ order.date }}</td>
                                    </tr>
                                </template>

                                <template v-else>
                                    <tr>
                                        <td
                                            class="pb-4 pt-8 text-center"
                                            colspan="2"
                                        >
                                            {{ $t('pos.orders.offline.no_orders') }}
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile View -->
                    <div
                        v-if="offlineOrders?.length"
                        class="grid gap-2.5 xl:hidden"
                    >
                        <div
                            class="rounded-lg px-2.5 py-4 text-dark"
                            :class="index % 2 === 0 ? 'bg-zinc-50' : ''"
                            v-for="(order, index) in offlineOrders"
                            :key="index"
                            @click="showOrderInfo(order)"
                        >
                            <div class="flex flex-col gap-y-1">
                                <div class="flex justify-between gap-x-2.5">
                                    <p class="text-base leading-5">
                                        {{ $t('pos.orders.history.order_id') }}
                                    </p>

                                    <p class="text-base leading-5">
                                        # {{ order.id }}
                                    </p>
                                </div>

                                <div class="flex justify-between gap-x-2.5">
                                    <p class="text-base leading-5">
                                        {{ $t('pos.orders.history.date') }}
                                    </p>

                                    <p class="text-base leading-5">
                                        {{ order.date }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="py-4 text-center xl:hidden"
                    >
                        {{ $t('pos.orders.offline.no_orders') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- For Desktop view -->
        <Info
            :outletOrder="currentOrder"
            :showSyncButton="true"
            @syncOrder="handleOrderSync"
        />

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
                                :class="index % 2 === 0 ? 'bg-zinc-50' : ''"
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
                    <div class="flex w-full gap-2.5">
                        <button
                            type="button"
                            class="primary-button w-full"
                            @click="handleOrderSync(currentOrder)"
                        >
                            <span class="icon-sync text-2xl"></span>

                            {{ $t('pos.orders.offline.sync_order') }}
                        </button>

                        <Print :outletOrder="currentOrder" />
                    </div>
                </template>
            </drawer>
        </Teleport>
    </div>
</template>

<script setup>
    import { ref, onBeforeMount, inject, computed, getCurrentInstance } from 'vue';
    import { useI18n } from 'vue-i18n';
    import Info from '@components/secured/orders/Info.vue';
    import Print from '@components/secured/common/Print.vue';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { useOutlet } from '@src/composable/outlet';
    import { useMutation } from '@vue/apollo-composable';
    import { PROCESS_PAYMENT } from '@src/graphql/payment';
    import { SYNC_ORDER } from '@src/graphql/orders';

    /**
     * General variables
     */
    const { t } = useI18n();
    const DB = useIndexedDB();
    const offlineOrders = ref([]);
    const currentOrder = ref({});
    const { formatDate } = useOutlet();
    const emitter = inject('emitter');
    const { appContext } = getCurrentInstance();
    const isOnline = computed(() => appContext.config.globalProperties.$isOnline.value);

    /**
     * Show order info
     */
    const orderInfoDrawer = ref(null);
    const showOrderInfo = (order) => {
        currentOrder.value = order;

        orderInfoDrawer.value.open();        
    }

    /**
     * Search order
     */
    const searchOrder = (event) => {
        const value = event.target.value;

        if (value) {
            offlineOrders.value = offlineOrders.value.filter((order) => {                
                return order.id.toString().includes(value);
            });
        } else {
            fetchOfflineOrders();
        }
    };

    const fetchOfflineOrders = async () => {
        currentOrder.value = {};

        await DB.getAllItems('offline_orders').then((orders) => {
            orders.forEach((order) => {
                order.date = formatDate(order.date);
            });

            offlineOrders.value = orders;            
        });
    }

    /**
     * Sync order
     */
    const { mutate: syncOfflineOrder } = useMutation(PROCESS_PAYMENT);

    const { mutate: syncOffline } = useMutation(SYNC_ORDER);

    const handleOrderSync = async (outletOrder) => {
        if (! isOnline.value) {
            emitter.emit('add_flash', {
                type: 'warning',
                message: t('pos.common.flash_messages.offline_error'),
            });

            return;
        }        

        try {
            let response;
            
            if (outletOrder.order?.appliedCartRuleIds !== undefined) {
                const { paymentDetails } = outletOrder;

                response = await syncOfflineOrder({ input: paymentDetails });
                
                if (! response.data.confirmPayment.success) {
                    throw new Error('Payment confirmation failed');
                }
                
                await orderInfoDrawer.value.close();

                await DB.deleteItem('offline_orders', outletOrder.id);
                
                emitter.emit('add_flash', {
                    type: 'success',
                    message: t('pos.orders.offline.sync_success'),
                });
                
                await fetchOfflineOrders();

                setOrder(response.data.confirmPayment.outletOrder);
            } else {
                const orderItems = outletOrder.order.items.map(item => ({
                    productId: parseInt(item.productId),
                    qtyOrdered: parseInt(item.quantity),
                }));
                
                const { orderNote, cartId, ...paymentDetail } = outletOrder.paymentDetails;
                
                const data = {
                    customerId: parseInt(outletOrder.order.customerId),
                    orderNote,
                    ...paymentDetail,
                    orderItems
                };
                
                response = await syncOffline({ input: data });
                
                if (! response.data.syncOrder.success) {
                    throw new Error('Order sync failed');
                }
                
                await orderInfoDrawer.value.close();

                await DB.deleteItem('offline_orders', outletOrder.id);
                
                emitter.emit('add_flash', {
                    type: 'success',
                    message: t('pos.orders.offline.sync_success'),
                });
                
                await fetchOfflineOrders();

                setOrder(response.data.syncOrder.outletOrder);
            }
        } catch (error) {
            console.error('Order sync error:', error);

            emitter.emit('add_flash', {
                type: 'error',
                message: t('pos.common.flash_messages.error_message'),
            });
        }
    };

    /**
     * Sync all orders sequentially
     */
    const syncOfflineOrders = async () => {
        const orders = offlineOrders.value;
        
        for (const order of orders) {
            await handleOrderSync(order);            
        }
    };

    /**
     * Set order
     */
    const setOrder = async (order) => {
        await DB.getItem('orders', 'all').then(async (data) => {
            const previousOrders = data.orders;

            await DB.updateItem('orders', {
                id: 'all',
                orders: [...previousOrders, order],
            });
        });
    }

    /**
     * Lifecycle hooks
     */
    onBeforeMount(async () => {
        await fetchOfflineOrders();
    });
</script>