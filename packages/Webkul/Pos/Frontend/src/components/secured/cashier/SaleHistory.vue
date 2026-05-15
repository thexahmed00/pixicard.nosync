<template>
    <SaleHistorySkeleton v-if="loading" />

    <div
        v-else
        class="my-4 grid gap-3"
    >
        <div class="flex items-center justify-between">
            <p class="text-2xl font-semibold text-dark">
                {{ $t('pos.cashier.sale_history.title') }}
            </p>

            <button
                type="button"
                class="transparent-button"
                @click="$refs.totalSaleDrawer.toggle()"
            >
                <span class="icon-filter text-2xl text-primary"></span>

                {{ $t('pos.cashier.sale_history.filters') }}
            </button>
        </div>

        <div class="rounded-lg bg-white md:p-4">
            <!-- Desktop View -->
            <table class="min-w-full max-sm:hidden">
                <thead>
                    <tr class="h-12 rounded-lg bg-zinc-50 ltr:text-left rtl:text-right">
                        <th class="px-4 py-2">
                            {{ $t('pos.cashier.sale_history.date') }}
                        </th>

                        <th class="px-4 py-2">
                            {{ $t('pos.cashier.sale_history.cash_payments') }}
                        </th>

                        <th class="px-4 py-2">
                            {{ $t('pos.cashier.sale_history.other_payments') }}
                        </th>

                        <th class="px-4 py-2">
                            {{ $t('pos.cashier.sale_history.total_sale') }}
                        </th>

                        <th class="px-4 py-2">
                            {{ $t('pos.cashier.sale_history.drawer_note') }}
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <template v-if="orders.length">
                        <tr
                            class="h-12 rounded-lg text-dark ltr:text-left rtl:text-right"
                            :class="index % 2 === 0 ? 'bg-zinc-50' : ''"
                            v-for="(order, index) in orders"
                            :key="index"
                        >
                            <td class="px-4 py-2">{{ order.createdAt }}</td>
                            <td class="px-4 py-2">{{ order.cashPayment }}</td>
                            <td class="px-4 py-2">{{ order.otherPayment }}</td>
                            <td class="px-4 py-2">{{ order.totalSale }}</td>
                            <td class="px-4 py-2">{{ order.drawerNote ?? 'N/A' }}</td>
                        </tr>
                    </template>

                    <template v-else>
                        <tr>
                            <td
                                class="pb-4 pt-8 text-center"
                                colspan="5"
                            >
                                {{ $t('pos.cashier.sale_history.no_records') }}
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Mobile View -->
            <div
                v-if="orders?.length"
                class="grid gap-2.5 md:hidden"
            >
                <div
                    class="rounded-lg px-2.5 py-4 text-dark"
                    :class="index % 2 === 0 ? 'bg-zinc-50' : ''"
                    v-for="(order, index) in orders"
                    :key="index"
                >
                    <div class="flex flex-col gap-y-1">
                        <div class="flex justify-between gap-x-2.5">
                            <p class="text-base leading-5">
                                {{ $t('pos.cashier.sale_history.date') }}
                            </p>

                            <p class="text-base leading-5">
                                {{ order.createdAt }}
                            </p>
                        </div>

                        <div class="flex justify-between gap-x-2.5">
                            <p class="text-base leading-5">
                                {{ $t('pos.cashier.sale_history.cash_payments') }}
                            </p>

                            <p class="text-base leading-5">
                                {{ order.cashPayment }}
                            </p>
                        </div>

                        <div class="flex justify-between gap-x-2.5">
                            <p class="text-base leading-5">
                                {{ $t('pos.cashier.sale_history.other_payments') }}
                            </p>

                            <p class="text-base leading-5">
                                {{ order.otherPayment }}
                            </p>
                        </div>

                        <div class="flex justify-between gap-x-2.5">
                            <p class="text-base leading-5">
                                {{ $t('pos.cashier.sale_history.total_sale') }}
                            </p>

                            <p class="text-base leading-5">
                                {{ order.totalSale }}
                            </p>
                        </div>

                        <div class="flex justify-between gap-x-2.5">
                            <p class="text-base leading-5">
                                {{ $t('pos.cashier.sale_history.drawer_note') }}
                            </p>

                            <p class="text-base leading-5">
                                {{ order.drawerNote ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="py-4 text-center md:hidden"
            >
                {{ $t('pos.cashier.sale_history.no_records') }}
            </div>
        </div>

        <Teleport to="body">
            <drawer ref="totalSaleDrawer">
                <template v-slot:header>
                    <p class="px-6 text-2xl font-semibold text-dark">
                        {{ $t('pos.cashier.sale_history.drawer.title') }}
                    </p>
                </template>

                <template v-slot:content>
                    <div class="grid gap-5 p-6">
                        <div class="grid gap-1">
                            <label
                                for="date"
                                class="text-base font-semibold leading-5 text-dark"
                            >
                                {{ $t('pos.cashier.sale_history.drawer.date') }}
                            </label>

                            <date-time>
                                <v-field
                                    type="text"
                                    name="date"
                                    id="date"
                                    class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded-md border border-greyish px-2.5 py-2 transition-all"
                                    v-model="filter.date"
                                />
                            </date-time>
                        </div>

                        <div class="grid gap-1">
                            <label
                                for="cash_payment"
                                class="text-base font-semibold leading-5 text-dark"
                            >
                                {{ $t('pos.cashier.sale_history.drawer.cash_payment') }}
                            </label>

                            <v-field
                                type="text"
                                name="cash_payment"
                                id="cash_payment"
                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded-md border border-greyish px-2.5 py-2 transition-all"
                                v-model="filter.cashPayment"
                            />
                        </div>

                        <div class="grid gap-1">
                            <label
                                for="other_payment"
                                class="text-base font-semibold leading-5 text-dark"
                            >
                                {{ $t('pos.cashier.sale_history.drawer.other_payment') }}
                            </label>

                            <v-field
                                type="text"
                                name="other_payment"
                                id="other_payment"
                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded-md border border-greyish px-2.5 py-2 transition-all"
                                v-model="filter.otherPayment"
                            />
                        </div>

                        <div class="grid gap-1">
                            <label
                                for="total_sale"
                                class="text-base font-semibold leading-5 text-dark"
                            >
                                {{ $t('pos.cashier.sale_history.drawer.total_sale') }}
                            </label>

                            <v-field
                                type="text"
                                name="total_sale"
                                id="total_sale"
                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded-md border border-greyish px-2.5 py-2 transition-all"
                                v-model="filter.totalSale"
                            />
                        </div>
                    </div>
                </template>

                <template v-slot:footer>
                    <button
                        type="button"
                        class="secondary-button w-full"
                        @click="applyFilter"
                    >
                        <span class="icon-filter text-2xl leading-6 text-white"></span>

                        {{ $t('pos.cashier.sale_history.drawer.filter_btn_title') }}
                    </button>
                </template>
            </drawer>
        </Teleport>
    </div>
</template>

<script setup>
    import { reactive, computed } from 'vue';
    import { useQuery } from '@vue/apollo-composable';
    import { GET_SALE_HISTORY } from '@src/graphql/drawer';
    import SaleHistorySkeleton from '@skeletons/cashier/SaleHistory.vue';

    /**
     * Filter object
     */
    const filter = reactive({
        date: '',
        cashPayment: '',
        otherPayment: '',
        totalSale: '',
    });

    /**
     * Fetch Drawer data
     */
    const { result, loading, refetch } = useQuery(GET_SALE_HISTORY);

    const orders = computed(() => result.value?.getSaleHistory ?? []);

    /**
     * Apply filter
     */
    const applyFilter = () => {
        refetch({
            date: filter.date,
            cashPayment: parseInt(filter.cashPayment),
            otherPayment: parseInt(filter.otherPayment),
            totalSale: parseInt(filter.totalSale),
        });
    };
</script>