<template>
    <div class="grid items-start gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
        <template v-if="orders.length">
            <div
                class="box-shadow grid content-start gap-4 rounded-lg bg-white p-3"
                v-for="(order, index) in orders"
                :key="index"
            >
                <div class="grid gap-1.5">
                    <p class="text-base font-semibold leading-5 text-dark">
                        {{ `${order.customerFirstName} ${order.customerLastName}` }}
                    </p>

                    <p class="text-sm font-normal leading-4 text-greyish">
                        {{ order.createdAt }}
                    </p>
                </div>

                <div class="grid gap-1 rounded-xl bg-light p-4">
                    <p class="text-base font-semibold leading-5 text-primary">
                        {{ $t('pos.orders.hold.note') }}
                    </p>

                    <p class="text-sm font-normal leading-4 text-dark">
                        {{ order.note || 'N/A' }}
                    </p>
                </div>

                <div
                    class="flex justify-between"
                    v-for="(item, index) in order.items"
                    :key="index"
                >
                    <div class="grid gap-1">
                        <p class="text-base font-normal leading-5 text-dark">
                            {{ item.name }}
                        </p>

                        <p class="text-sm font-normal leading-4 text-greyish">
                            {{ item.sku }}
                        </p>
                    </div>

                    <p class="text-base font-normal leading-5 text-dark">
                        {{ item.qty }}
                    </p>
                </div>

                <div class="flex justify-between gap-2.5">
                    <button
                        type="button"
                        class="secondary-button w-full"
                        @click="resumeOrder(order)"
                    >
                        <span class="icon-checkout text-2xl"></span>

                        {{ $t('pos.orders.hold.resume') }}
                    </button>

                    <button
                        type="button"
                        class="transparent-button w-full"
                        @click="removeOrder(order)"
                    >
                        <span class="icon-delete text-2xl"></span>

                        {{ $t('pos.orders.hold.remove') }}
                    </button>
                </div>
            </div>
        </template>

        <template v-else>
            <div class="box-shadow w-full rounded-lg bg-white p-3">
                <p class="text-base font-normal leading-5 text-dark">
                    {{ $t('pos.orders.hold.no_orders') }}
                </p>
            </div>
        </template>
    </div>
</template>

<script setup>
    import { ref, toRaw, inject, onMounted } from 'vue';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { useI18n } from 'vue-i18n';

    /**
     * General variables
     */
    const { t } = useI18n();
    const DB = useIndexedDB();
    const emitter = inject('emitter');

    /**
     * Fetch the orders from the IndexedDB
     */
    const orders = ref([]);

    onMounted(() => {
        DB.getAllItems('hold_orders').then((data) => {
            orders.value = data;          
        });
    });

    /**
     * Remove the order from the IndexedDB
     */
    const removeOrder = (order) => {
        emitter.emit('open_confirm_modal', {
            agree: async () => {
                DB.deleteItem('hold_orders', order.id).then(() => {
                    orders.value = orders.value.filter((item) => item.id !== order.id);
                });

                emitter.emit('add_flash', {
                    type: 'success',
                    message: t('pos.orders.hold.remove_success'),
                });
            }
        });
    };

    /**
     * Resume the order
     */
    const resumeOrder = (order) => {
        emitter.emit('open_confirm_modal', {
            agree: async () => {
                const success = await DB.addItem('carts', toRaw(order));

                if (success) {
                    emitter.emit('customer_updated', order.customer);

                    DB.deleteItem('hold_orders', order.id).then(() => {
                        orders.value = orders.value.filter((item) => item.id !== order.id);
                    });

                    emitter.emit('add_flash', {
                        type: 'success',
                        message: t('pos.orders.hold.resume_success'),
                    });
                }
            }
        });
    };
</script>