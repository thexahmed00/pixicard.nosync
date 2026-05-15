<template>
    <TodaySaleSkeleton v-if="loading" />

    <div
        v-else
        class="mb-4 grid gap-4"
    >
        <div class="mt-4 grid grid-cols-3 gap-4 max-sm:grid-cols-2">
            <div class="box-shadow grid gap-1.5 rounded-lg bg-white p-4 max-sm:col-span-2">
                <p class="text-base leading-5 text-dark">
                    {{ $t('pos.cashier.today_sale.opening_drawer_amount') }}
                </p>

                <p class="text-3xl font-medium text-primary">
                    {{ todaySales?.openingAmount }}
                </p>
            </div>

            <div class="box-shadow col-span-1 grid gap-1.5 rounded-lg bg-white p-4">
                <p class="text-base leading-5 text-dark">
                    {{ $t('pos.cashier.today_sale.cash_payment_sale') }}
                </p>

                <p class="text-3xl font-medium text-limeGreen">
                    {{ todaySales?.cashPaymentSale }}
                </p>
            </div>

            <div class="box-shadow col-span-1 grid gap-1.5 rounded-lg bg-white p-4">
                <p class="text-base leading-5 text-dark">
                    {{ $t('pos.cashier.today_sale.other_payment_sale') }}
                </p>

                <p class="text-3xl font-medium text-limeGreen">
                    {{ todaySales?.otherPaymentSale }}
                </p>
            </div>
        </div>

        <div class="grid gap-3">
            <div class="flex items-center justify-between">
                <p class="text-2xl font-semibold text-dark">
                    {{ $t('pos.cashier.today_sale.sale_history') }}
                </p>

                <button
                    type="button"
                    class="transparent-button"
                    @click="$refs.todaySaleDrawer.toggle()"
                >
                    <span class="icon-filter text-2xl text-primary"></span>

                    {{ $t('pos.cashier.today_sale.filters') }}
                </button>
            </div>

            <div class="rounded-lg bg-white md:p-4">
                <!-- Desktop View -->
                <table class="min-w-full max-sm:hidden">
                    <thead>
                        <tr class="h-12 rounded-lg bg-zinc-50 ltr:text-left rtl:text-right">
                            <th class="px-4 py-2">
                                {{ $t('pos.cashier.today_sale.order_id') }}
                            </th>

                            <th class="px-4 py-2">
                                {{ $t('pos.cashier.today_sale.time') }}
                            </th>

                            <th class="px-4 py-2">
                                {{ $t('pos.cashier.today_sale.order_total') }}
                            </th>

                            <th class="px-4 py-2">
                                {{ $t('pos.cashier.today_sale.payment_mode') }}
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <template v-if="todaySales.orders?.length">
                            <tr
                                class="h-12 rounded-lg text-dark ltr:text-left rtl:text-right"
                                :class="index % 2 === 0 ? 'bg-zinc-50' : ''"
                                v-for="(order, index) in todaySales.orders"
                                :key="index"
                            >
                                <td class="px-4 py-2">
                                    # {{ order.orderId }}
                                </td>

                                <td class="px-4 py-2">
                                    {{ order.createdAt }}
                                </td>

                                <td class="px-4 py-2">
                                    {{ outlet.formatPrice(order.orderTotal) }}
                                </td>

                                <td class="px-4 py-2">
                                    {{ order.paymentMode }}
                                </td>
                            </tr>
                        </template>

                        <template v-else>
                            <tr>
                                <td
                                    class="pb-4 pt-8 text-center"
                                    colspan="4"
                                >
                                    {{ $t('pos.cashier.today_sale.no_records') }}
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Mobile View -->
                <div
                    v-if="todaySales.orders?.length"
                    class="grid gap-2.5 md:hidden"
                >
                    <div
                        class="rounded-lg px-2.5 py-4 text-dark"
                        :class="index % 2 === 0 ? 'bg-zinc-50' : ''"
                        v-for="(order, index) in todaySales.orders"
                        :key="index"
                    >
                        <div class="flex flex-col gap-y-1">
                            <div class="flex justify-between gap-x-2.5">
                                <p class="text-base leading-5">
                                    # {{ order.orderId }}
                                </p>

                                <p class="text-base leading-5">
                                    {{ outlet.formatPrice(order.orderTotal) }}
                                </p>
                            </div>

                            <div class="flex justify-between gap-x-2.5">
                                <p class="text-base leading-5">
                                    {{ order.createdAt }}
                                </p>

                                <p class="text-base leading-5">
                                    {{ order.paymentMode }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="py-4 text-center md:hidden"
                >
                    {{ $t('pos.cashier.today_sale.no_records') }}
                </div>
            </div>
        </div>

        <Teleport to="body">
            <drawer ref="todaySaleDrawer">
                <template v-slot:header>
                    <p class="px-6 text-2xl font-semibold text-dark">
                        {{ $t('pos.cashier.today_sale.drawer.title') }}
                    </p>
                </template>

                <template v-slot:content>
                    <div class="grid gap-5 p-6">
                        <div class="grid gap-1">
                            <label
                                for="order_id"
                                class="text-base font-semibold leading-5 text-dark"
                            >
                                {{ $t('pos.cashier.today_sale.drawer.order_id') }}
                            </label>

                            <v-field
                                type="text"
                                name="order_id"
                                id="order_id"
                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded-md border border-greyish px-2.5 py-2 transition-all"
                                v-model="filter.orderId"
                            />
                        </div>

                        <div class="grid gap-1">
                            <label
                                for="time"
                                class="text-base font-semibold leading-5 text-dark"
                            >
                                {{ $t('pos.cashier.today_sale.drawer.time') }}
                            </label>

                            <date-time>
                                <v-field
                                    type="text"
                                    name="time"
                                    id="time"
                                    class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded-md border border-greyish px-2.5 py-2 transition-all"
                                    v-model="filter.time"
                                />
                            </date-time>
                        </div>

                        <div class="grid gap-1">
                            <label
                                for="order_total"
                                class="text-base font-semibold leading-5 text-dark"
                            >
                                {{ $t('pos.cashier.today_sale.drawer.order_total') }}
                            </label>

                            <v-field
                                type="text"
                                name="order_total"
                                id="order_total"
                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded-md border border-greyish px-2.5 py-2 transition-all"
                                v-model="filter.orderTotal"
                            />
                        </div>

                        <div class="grid gap-1">
                            <label
                                for="payment_mode"
                                class="text-base font-semibold leading-5 text-dark"
                            >
                                {{ $t('pos.cashier.today_sale.drawer.payment_mode') }}
                            </label>

                            <v-field
                                name="payment_mode"
                                v-slot="{ field }"
                                v-model="filter.paymentMode"
                            >
                                <select
                                    id="payment_mode"
                                    v-bind="field"
                                    v-model="filter.paymentMode"
                                    class="custom-select text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish bg-white px-2.5 py-2 transition-all"
                                >
                                    <option value="">
                                        {{ $t('pos.cashier.today_sale.drawer.select_payment_mode') }}
                                    </option>

                                    <option
                                        v-for="(paymentMode, index) in paymentModes"
                                        :key="index"
                                        :value="paymentMode"
                                    >
                                        {{ $t(`pos.cashier.today_sale.drawer.${paymentMode}`) }}
                                    </option>
                                </select>
                            </v-field>
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

                        {{ $t('pos.cashier.today_sale.drawer.filter_btn_title') }}
                    </button>
                </template>
            </drawer>
        </Teleport>
    </div>
</template>

<script setup>
    import { reactive, computed } from 'vue';
    import { useOutlet } from '@src/composable/outlet';
    import { useQuery } from '@vue/apollo-composable';
    import { GET_TODAY_SALE } from '@src/graphql/drawer';
    import TodaySaleSkeleton from '@skeletons/cashier/TodaySale.vue';

    const outlet = useOutlet();

    /**
     * Fetch today sale data
     */
    const { result, loading, refetch } = useQuery(GET_TODAY_SALE);
    const paymentModes = ['pos_cash', 'pos_card', 'pos_split'];
    const filter = reactive({
        orderId: null,
        paymentMode: "",
        time: null,
        orderTotal: "",
    });

    const todaySales = computed(() => result.value?.getTodaySales ?? []);

    /**
     * Apply filter
     */
    const applyFilter = () => {
        refetch({
            orderId: parseInt(filter.orderId),
            paymentMode: filter.paymentMode,
            time: filter.time,
            orderTotal: parseInt(filter.orderTotal),
        });
    };
</script>