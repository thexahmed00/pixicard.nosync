<template>
    <div class="grid gap-4">
        <div
            class="flex w-max cursor-pointer items-center"
            @click="$router.go(-1)"
        >
            <span class="icon-chevron-left text-2xl text-primary"></span>

            <span class="text-base font-semibold leading-5 text-primary">
                {{ $t('pos.payment.back') }}
            </span>
        </div>

        <v-form
            class="grid gap-4 xl:grid-cols-2"
            @submit="process"
        >
            <template v-if="!cart?.items?.length">
                <div class="md:box-shadow flex items-center justify-center rounded-lg bg-white">
                    <p class="text-base leading-5 text-dark">
                        {{ $t('pos.payment.empty_cart') }}
                    </p>
                </div>
            </template>

            <template v-else>
                <div class="md:box-shadow flex flex-col gap-4 rounded-lg bg-white">
                    <div class="flex flex-col px-3">
                        <div class="flex flex-col gap-1 py-4">
                            <p class="text-2xl font-semibold text-dark">
                                {{ $t('pos.payment.order_details') }}
                            </p>

                            <div class="flex justify-between">
                                <p class="text-sm leading-4 text-dark">
                                    {{ `${cart.customerFirstName} ${cart.customerLastName}` }}
                                </p>

                                <p class="text-sm leading-4 text-dark">
                                    {{ cart.customerEmail }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col content-baseline gap-1 overflow-y-auto py-2 max-sm:h-full xl:h-[calc(100vh-416px)]">
                            <div
                                class="flex h-max flex-col gap-4 rounded-lg p-2"
                                :class="[index % 2 == 0 ? 'bg-zinc-50' : '']"
                                v-for="(item, index) in cart?.items"
                                :key="index"
                            >
                                <div class="flex justify-between gap-2">
                                    <div class="flex gap-2.5">
                                        <p class="text-base font-semibold leading-5 text-dark">
                                            {{ index + 1 }}
                                        </p>

                                        <div class="flex flex-col gap-1">
                                            <p class="text-base font-semibold leading-5 text-dark">
                                                {{ item.name }}
                                            </p>

                                            <p class="text-sm font-normal leading-4 text-greyish">
                                                {{ item.sku }}
                                            </p>
                                        </div>
                                    </div>

                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ item.formattedPrice.total }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 rounded-lg bg-extraLight p-3">
                        <div class="flex flex-col gap-1">
                            <div class="flex justify-between">
                                <p class="text-base font-normal leading-5 text-dark">
                                    {{ $t('pos.payment.subtotal') }}
                                </p>

                                <p class="text-base font-semibold leading-5 text-dark">
                                    {{ cart?.formattedPrice?.subTotal }}
                                </p>
                            </div>

                            <div class="flex justify-between">
                                <p class="text-base font-normal leading-5 text-dark">
                                    {{ $t('pos.payment.tax') }}
                                </p>

                                <p class="text-base font-semibold leading-5 text-dark">
                                    {{ cart?.formattedPrice?.taxTotal }}
                                </p>
                            </div>

                            <div class="flex justify-between">
                                <p class="text-base font-normal leading-5 text-dark">
                                    {{ $t('pos.payment.discount') }}
                                </p>

                                <p class="text-base font-semibold leading-5 text-dark">
                                    {{ cart?.formattedPrice?.discount }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <p class="text-xl font-medium leading-6 text-dark">
                                {{ $t('pos.payment.grand_total') }}
                            </p>

                            <p class="text-xl font-medium leading-6 text-dark">
                                {{ cart?.formattedPrice?.grandTotal }}
                            </p>
                        </div>

                        <Button
                            v-if="! isMobileOrTab"
                            :isLoading="isPaymentProcessing"
                            class="secondary-button w-full"
                            type="submit"
                            icon="icon-payment"
                            :label="$t('pos.payment.confirm_payment')"
                        />
                    </div>
                </div>
            </template>

            <div class="flex flex-col gap-2">
                <div class="box-shadow grid grid-cols-3 rounded-lg bg-white p-4 max-sm:hidden">
                    <div class="flex flex-col gap-1">
                        <p class="text-3xl font-semibold text-limeGreen">
                            {{ cart?.formattedPrice?.grandTotal ?? 0 }}
                        </p>

                        <p class="text-xl font-normal leading-6 text-dark">
                            {{ $t('pos.payment.payable_amount') }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="text-3xl font-semibold text-limeGreen">
                            {{ cashTotal != '' ? cashTotal : 0 }}
                        </p>

                        <p class="text-xl font-normal leading-6 text-dark">
                            {{ $t('pos.payment.received') }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <p class="text-3xl font-semibold text-[#F83015]">
                            {{ amountRemaining }}
                        </p>

                        <p class="text-xl font-normal leading-6 text-dark">
                            {{ $t('pos.payment.remaining') }}
                        </p>
                    </div>
                </div>

                <div class="md:box-shadow grid gap-4 rounded-lg bg-white p-4 max-sm:p-0">
                    <div class="flex h-9 gap-2.5">
                        <div
                            v-on:click="paymentMethod = method"
                            :class="[paymentMethod === method ? 'bg-light text-primary !border-primary' : 'text-dark']"
                            class="flex cursor-pointer items-center rounded-lg border-2 border-transparent px-3 py-2 text-base font-medium leading-5"
                            v-for="(method, index) in ['pos_cash', 'pos_card', 'pos_split']"
                            :key="index"
                        >
                            {{ $t(`pos.payment.${method}`) }}
                        </div>
                    </div>

                    <template v-if="
                        paymentMethod === 'pos_cash'
                        || paymentMethod === 'pos_split'
                    ">
                        <div class="grid gap-1">
                            <v-field
                                type="text"
                                name="cash_total"
                                id="cash_total"
                                class="w-full rounded border border-greyish px-2.5 py-3 text-right text-2xl font-medium text-dark"
                                :rules="paymentMethod === 'pos_cash' ? `required|decimal|min_value:${cart?.grandTotal}` : 'required|decimal'"
                                :label="$t('pos.payment.pos_cash')"
                                v-model="cashTotal"
                            />

                            <v-error-message
                                name="cash_total"
                                class="text-sm text-red-500"
                            />
                        </div>
                    </template>

                    <template v-if="
                        paymentMethod === 'pos_cash'
                        && ! isMobileOrTab
                    ">
                        <div class="grid grid-cols-3 gap-2">
                            <div
                                v-for="(key, index) in keys"
                                :key="index"
                                :class="[key === 'cancel' ? 'col-span-2' : '']"
                                class="flex cursor-pointer items-center justify-center rounded bg-light py-2 text-2xl font-semibold text-dark hover:bg-blue-100"
                                @click="keyPress(key)"
                            >
                                <template v-if="key === 'cancel'">
                                    {{ $t('pos.payment.cancel') }}
                                </template>

                                <template v-else-if="key === 'x'">
                                    <span class="icon-backspace text-2xl"></span>
                                </template>

                                <template v-else>
                                    {{ key }}
                                </template>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="
                        paymentMethod === 'pos_card'
                        || paymentMethod === 'pos_split'
                    ">
                        <div class="grid gap-5 border-t border-greyish pt-5">
                            <div class="grid gap-1">
                                <label
                                    for="card_details"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.payment.card_details') }}
                                </label>

                                <div class="flex gap-10">
                                    <div class="border-b border-greyish">
                                        <span class="text-light-600 text-lg leading-5">
                                            xxxx
                                        </span>
                                    </div>

                                    <div class="border-b border-greyish">
                                        <span class="text-light-600 text-lg leading-5">
                                            xxxx
                                        </span>
                                    </div>

                                    <div class="border-b border-greyish">
                                        <span class="text-light-600 text-lg leading-5">
                                            xxxx
                                        </span>
                                    </div>

                                    <v-field
                                        type="text"
                                        name="card_details"
                                        id="card_details"
                                        class="text-light-600 w-10 border-b border-greyish text-base leading-5"
                                        maxlength="4"
                                        rules="required|numeric|min:4|max:4"
                                        v-model="cardDetails"
                                        :label="$t('pos.payment.card_details')"
                                    />
                                </div>

                                <v-error-message
                                    name="card_details"
                                    class="text-sm text-red-500"
                                />
                            </div>

                            <div class="grid gap-1">
                                <label
                                    for="bank_name"
                                    class="text-base font-semibold leading-5 text-dark"
                                >
                                    {{ $t('pos.payment.bank') }}
                                </label>

                                <v-field
                                    as="select"
                                    name="bank_name"
                                    id="bank_name"
                                    class="custom-select text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish bg-white px-2.5 py-2 transition-all"
                                    v-model="bankName"
                                    rules="required"
                                    :label="$t('pos.payment.bank')"
                                >
                                    <option value="">
                                        {{ $t('pos.payment.select_bank') }}
                                    </option>

                                    <option
                                        v-for="(paymentBank, index) in paymentBanks"
                                        :key="index"
                                        :value="paymentBank.name"
                                    >
                                        {{ paymentBank.name }}
                                    </option>

                                    <option value="other">
                                        {{ $t('pos.payment.other') }}
                                    </option>
                                </v-field>

                                <v-error-message
                                    name="bank_name"
                                    class="text-sm text-red-500"
                                />
                            </div>
                        </div>
                    </template>

                    <Button
                        v-if="isMobileOrTab"
                        :isLoading="isPaymentProcessing"
                        class="secondary-button w-full"
                        type="submit"
                        icon="icon-payment"
                        :label="$t('pos.payment.confirm_payment')"
                    />
                </div>
            </div>
        </v-form>
    </div>
</template>

<script setup>
    import { ref, onMounted, computed, watch, inject, getCurrentInstance, toRaw } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useMutation } from '@vue/apollo-composable';
    import { useRouter } from 'vue-router';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { useWindowWidth } from '@src/composable/window';
    import { PROCESS_PAYMENT } from '@src/graphql/payment';
    import { SYNC_ORDER } from '@src/graphql/orders';

    /**
     * Inject the dependency
     */
    const emitter = inject('emitter');
    const { t } = useI18n();
    const DB = useIndexedDB();
    const router = useRouter();
    const { appContext } = getCurrentInstance();
    const isOnline = computed(() => appContext.config.globalProperties.$isOnline.value);


    /**
     * Data to be used in the component
     */
    const cart = ref({});
    const paymentMethod = ref('pos_cash');
    const isPaymentProcessing = ref(false);
    const cashTotal = ref('');
    const cardDetails = ref('');
    const bankName = ref('');
    const { isMobileOrTab } = useWindowWidth();

    /**
     * Return the amount remaining
     */
    const amountRemaining = computed(() => {
        const grandTotal = parseFloat(cart.value?.grandTotal ?? 0);
        const cashTotalAmount = parseFloat(cashTotal.value) || 0;
        
        return (grandTotal - cashTotalAmount).toFixed(2);
    });

    /**
     * Get the cart
     */
    const getCart = async () => {
        const cartId = router.currentRoute.value.params.id;

        await DB.getItem('carts', cartId).then(async (response) => {
            if (response) {                
                cart.value = response;
            } else {
                cart.value = await DB.getItem('carts', parseInt(cartId));
            }
        });
    };

    /**
     * Process the payment
     */
    const { mutate: processPayment } = useMutation(PROCESS_PAYMENT);

    const { mutate: syncOffline } = useMutation(SYNC_ORDER);

    const process = async () => {
        isPaymentProcessing.value = true;

        const data = {
            cartId: parseInt(cart.value.id),
            paymentMode: paymentMethod.value,
            orderNote: cart.value.note ?? '',
        };

        if (
            paymentMethod.value === 'pos_cash'
            || paymentMethod.value === 'pos_split'
        ) {
            data['cashTotal'] = parseFloat(cashTotal.value);
        }
        
        if (
            paymentMethod.value === 'pos_card'
            || paymentMethod.value === 'pos_split'
        ) {
            data['cardDetails'] = cardDetails.value;
            data['bankName'] = bankName.value;
        }

        if (isOnline.value) {
            if (cart.value.appliedCartRuleIds !== undefined) {
                processPayment({ input: data }).then(async (response) => {
                    const { confirmPayment } = response.data;

                    if (confirmPayment.success) {
                        emitter.emit('add_flash', {
                            type: 'success',
                            message: confirmPayment.message,
                        });

                        DB.deleteItem('carts', cart.value.id).then(() => {
                            localStorage.setItem('outlet_order', JSON.stringify(confirmPayment.outletOrder));

                            router.push({path: '/home'});

                            isPaymentProcessing.value = false;
                        });
                    }
                });
            } else {                
                const orderItems = cart.value.items.map(item => ({
                    productId: parseInt(item.productId),
                    qtyOrdered: parseInt(item.quantity),
                }));                
                
                const { orderNote, cartId, ...paymentDetail } = data;
                
                const input = {
                    customerId: parseInt(cart.value.customerId),
                    orderNote,
                    ...paymentDetail,
                    orderItems
                };
                
                const response = await syncOffline({ input });

                const { syncOrder } = response.data;                

                if (syncOrder.success) {
                    emitter.emit('add_flash', {
                        type: 'success',
                        message: syncOrder.message,
                    });

                    DB.deleteItem('carts', cart.value.id).then(() => {
                        localStorage.setItem('outlet_order', JSON.stringify(syncOrder.outletOrder));

                        router.push({path: '/home'});

                        isPaymentProcessing.value = false;
                    });
                }
            }
        } else {
            await DB.addItem('offline_orders', {
                order: {
                    ...toRaw(cart.value),
                },
                paymentDetails: data,
                date: new Date(),
            });

            await DB.deleteItem('carts', cart.value.id);

            emitter.emit('add_flash', {
                type: 'warning',
                message: t('pos.payment.order_placed'),
            });

            router.push({path: '/home'});
        }
    };

    /**
     * Get the cart and payment bank's on mounted
     */
    const paymentBanks = ref([]);
    
    onMounted(async () => {
        const agent = await DB.getAllItems('agents');        

        if (agent.length) {
            paymentBanks.value = agent[0]?.banks;
        }

        getCart();
    });

    /**
     * Watch the amount received
     */
    watch(() => cashTotal.value, () => {
        if (cashTotal.value > 10000000000000) {
            emitter.emit('add_flash', {
                type: 'warning',
                message: t('pos.payment.amount_exceeded'),
            });
        }
    });

    /**
     * Method to handle the key press
     */
    const keys = ref(['1', '2', '3', '4', '5', '6', '7', '8', '9', '00', '0', 'x', '.', 'cancel']);

    const keyPress = (key) => {
        switch (key) {
            case 'cancel':
                cashTotal.value = '';
            break;
            
            case 'x':
                cashTotal.value = cashTotal.value.slice(0, -1);
            break;

            case '.':
                if (! cashTotal.value.includes('.')) {
                    cashTotal.value += '.';
                }
            break;

            case '0':
                if (cashTotal.value !== '') {
                    cashTotal.value += '0';
                }
            break;

            case '00':
                if (cashTotal.value !== '') {
                    cashTotal.value += '00';
                }
            break;

            default:
                if (cashTotal.value === '') {
                    cashTotal.value = key;
                } else {
                    cashTotal.value += key;
                }
            break;
        }
    };
</script>