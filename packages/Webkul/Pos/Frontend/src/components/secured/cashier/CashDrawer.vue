<template>
    <div class="grid gap-8 xl:w-[738px] 2xl:w-[828px]">
        <div class="mt-8 grid gap-4 max-sm:mt-4">
            <h1 class="text-xl font-medium text-dark">
                {{ $t('pos.cashier.cash_drawer.drawer_amount_summary') }}
            </h1>

            <template v-if="loading">
                <DrawerSkeleton />
            </template>

            <template v-else>
                <div class="rounded-lg bg-white">
                    <div class="grid border-b border-[#E1E1E1] md:p-4">
                        <div class="flex h-12 justify-between rounded-lg bg-zinc-50 p-4 max-sm:h-auto max-sm:flex-col max-sm:px-2.5 max-sm:py-4 md:items-center">
                            <p class="text-base leading-5 text-dark">
                                {{ $t('pos.cashier.cash_drawer.opening_drawer_amount') }}
                            </p>

                            <p class="text-base leading-5 text-dark">
                                {{ outlet.formatPrice(drawer.openingAmount) }}
                            </p>
                        </div>

                        <div class="flex h-12 justify-between rounded-lg p-4 max-sm:h-auto max-sm:flex-col max-sm:px-2.5 max-sm:py-4 md:items-center">
                            <p class="text-base leading-5 text-dark">
                                {{ $t('pos.cashier.cash_drawer.cash_payment_sale') }}
                            </p>

                            <p class="text-base leading-5 text-dark">
                                {{ outlet.formatPrice(drawer.cashPaymentSale) }}
                            </p>
                        </div>

                        <div class="flex h-12 justify-between rounded-lg bg-zinc-50 p-4 max-sm:h-auto max-sm:flex-col max-sm:px-2.5 max-sm:py-4 md:items-center">
                            <p class="text-base leading-5 text-dark">
                                {{ $t('pos.cashier.cash_drawer.other_payment_sale') }}
                            </p>

                            <p class="text-base leading-5 text-dark">
                                {{ outlet.formatPrice(drawer.otherPaymentSale) }}
                            </p>
                        </div>

                        <div class="flex h-12 justify-between rounded-lg p-4 max-sm:h-auto max-sm:flex-col max-sm:px-2.5 max-sm:py-4 md:items-center">
                            <p class="text-base leading-5 text-dark">
                                {{ $t('pos.cashier.cash_drawer.expected_drawer_amount') }}
                            </p>

                            <p class="text-base leading-5 text-dark">
                                {{ outlet.formatPrice(drawer.expectedDrawerAmount) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex h-12 justify-between rounded-lg p-8 max-sm:h-auto max-sm:flex-col max-sm:px-2.5 max-sm:py-4 md:items-center">
                        <p class="text-xl leading-6 text-dark">
                            {{ $t('pos.cashier.cash_drawer.difference') }}
                        </p>

                        <p class="text-xl font-medium leading-6 text-limeGreen">
                            {{ outlet.formatPrice(drawer.differenceAmount) }}
                        </p>
                    </div>
                </div>
            </template>
        </div>

        <v-form v-slot="{ handleSubmit }">
            <form
                class="mb-4 grid content-start gap-4"
                @submit="handleSubmit($event, submitForm)"
            >
                <div class="grid gap-4">
                    <label
                        for="opening_amount"
                        class="text-xl font-medium leading-6 text-dark"
                    >
                        {{ $t('pos.cashier.cash_drawer.opening_drawer_amount') }}
                    </label>

                    <v-field
                        type="text"
                        name="opening_amount"
                        id="opening_amount"
                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                        rules="required"
                        placeholder="100"
                        :label="$t('pos.cashier.cash_drawer.opening_drawer_amount')"
                        v-model="openingAmount"
                    />

                    <v-error-message
                        name="opening_amount"
                        class="text-sm text-red-500"
                    />
                </div>

                <div class="grid gap-4">
                    <label
                        for="remarks"
                        class="text-xl font-medium leading-6 text-dark"
                    >
                        {{ $t('pos.cashier.cash_drawer.remarks') }}
                    </label>

                    <v-field
                        as="textarea"
                        name="remark"
                        id="remark"
                        class="hover:border-light-700 focus:border-light-700 h-20 w-full resize-none rounded-lg border border-greyish px-2.5 py-3 text-base font-normal leading-5 transition-all"
                        :placeholder="$t('pos.cashier.cash_drawer.remarks_placeholder')"
                        rules="required"
                    />

                    <v-error-message
                        name="remark"
                        class="text-sm text-red-500"
                    />
                </div>

                <Button
                    type="submit"
                    :isLoading="isDrawerFormSubmitted"
                    class="secondary-button w-full md:w-80"
                    :label="$t('pos.cashier.cash_drawer.close_drawer')"
                />
            </form>
        </v-form>
    </div>
</template>

<script setup>
    import { computed, inject } from 'vue';
    import { useOutlet } from '@src/composable/outlet';
    import { useQuery, useMutation } from '@vue/apollo-composable';
    import { GET_DRAWER, CLOSE_DRAWER } from '@src/graphql/drawer';
    import DrawerSkeleton from '@skeletons/cashier/Drawer.vue';

    const emitter = inject('emitter');

    const outlet = useOutlet();

    /**
     * Fetch Drawer data
     */
    const { result, loading } = useQuery(GET_DRAWER);

    const drawer = computed(() => result.value?.getDrawerDetails ?? []);

    /**
     * Opening Amount
     */
    const openingAmount = computed(() => (parseFloat(drawer.value.openingAmount ?? 0) + parseFloat(drawer.value.cashPaymentSale ?? 0)).toFixed(2));

    /**
     * Close Drawer
     */
    const { mutate: closeDrawer, loading: isDrawerFormSubmitted } = useMutation(CLOSE_DRAWER);

    const submitForm = (params, { setErrors, resetForm }) => {
        closeDrawer({ input: {
            openingAmount: parseFloat(params.opening_amount),
            remark: params.remark
        }}).then((response) => {
            if (response.data.closeDrawer.success) {
                resetForm();
                
                emitter.emit('add_flash', {
                    type: 'success',
                    message: response.data.closeDrawer.message
                });
            }
        }).catch((error) => {
            emitter.emit('add_flash', {
                type: 'error',
                message: error.message
            });
        });
    };
</script>
