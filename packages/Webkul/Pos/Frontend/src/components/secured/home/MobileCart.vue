<template>
    <div>
        <!-- Mobile view -->
        <div class="fixed bottom-0 left-0 right-0 flex w-full items-center justify-between gap-10 border-t bg-white p-2.5 xl:hidden">
            <div class="flex flex-col gap-y-0.5">
                <p class="text-lg font-medium leading-6">
                    {{ currentCart?.formattedPrice?.grandTotal ?? formatPrice('0.00') }}
                </p>

                <p class="text-sm leading-4">
                    {{ $t('pos.home.items_in_cart', { count: currentCart?.items?.length ?? 0 }) }}
                </p>
            </div>

            <button
                class="secondary-button px-6"
                @click="$refs.mobileCartDrawer.toggle()"
            >
                <span class="icon-cart text-2xl leading-6"></span>

                <span class="text-base font-semibold">
                    {{ $t('pos.home.view_cart_btn') }}
                </span>
            </button>
        </div>

        <drawer ref="mobileCartDrawer">
            <template v-slot:header>
                <div class="px-2 py-4">
                    <div class="flex h-12 w-full items-center justify-between gap-5">
                        <template v-if="currentCustomer?.id">
                            <div class="flex items-center gap-2.5">
                                <template v-if="currentCustomer.imageUrl">
                                    <img
                                        :src="currentCustomer.imageUrl"
                                        class="h-11 w-11 rounded-full"
                                        alt="profile image"
                                    >
                                </template>

                                <template v-else>
                                    <img
                                        src="/src/assets/images/user-placeholder.png"
                                        class="h-11 w-11 rounded-full"
                                        alt="profile image"
                                    >
                                </template>
                                
                                <div class="grid">
                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ currentCustomer?.name }}
                                    </p>

                                    <p class="text-sm font-normal leading-4 text-greyish">
                                        #{{ currentCustomer?.id }}
                                    </p>
                                </div>
                            </div>
                        </template>
                        
                        <template v-else>
                            <router-link
                                to="/customers"
                                class="flex cursor-pointer items-center gap-2.5 rounded-lg bg-zinc-50 p-3"
                            >
                                <span class="icon-plus text-2xl text-dark"></span>
                                
                                <p class="text-base leading-5 text-dark">
                                    {{ $t('pos.common.cart.add_customer') }}
                                </p>
                            </router-link>
                        </template>

                        <div class="custom-scrollbar flex max-w-48 cursor-pointer items-center overflow-x-auto rounded-lg bg-extraLight">
                            <span
                                v-if="carts.length > 1"
                                class="icon-minus flex min-h-12 min-w-12 items-center justify-center px-3 py-2 text-2xl text-dark"
                                @click="removeCart"
                            >
                            </span>
                            
                            <div
                                @click="setCurrentCart(cart.id)"
                                :class="[cart.isCurrentCart ? 'border-2 border-primary' : '']"
                                class="flex min-h-12 min-w-12 items-center justify-center rounded-lg px-3 py-2"
                                v-for="(cart, index) in carts"
                                :key="index"
                            >
                                {{ index + 1 }}
                            </div>

                            <span
                                class="icon-plus flex min-h-12 min-w-12 items-center justify-center px-3 py-2 text-2xl text-dark"
                                @click="createNewCart"
                            >
                            </span>
                        </div>
                    </div>
                </div>
            </template>

            <template v-slot:content>
                <div class="flex h-full flex-col">
                    <div class="flex flex-1 flex-col gap-2">
                        <div
                            class="m-2 grid min-h-14 gap-4 rounded-lg p-2"
                            :class="{
                                'bg-zinc-50': index % 2 != 0,
                                'shadow-[3px_0px_0px_0px_rgba(50,181,18,1)_inset]': showMore[index]
                            }"
                            v-for="(item, index) in currentCart?.items"
                            :key="index"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex gap-2.5">
                                    <span
                                        :class="[showMore[index] ? 'icon-chevron-down' : 'icon-chevron-right']"
                                        class="cursor-pointer text-2xl leading-6 text-greyish"
                                        @click="showHide(index)"
                                    >
                                    </span>

                                    <p class="text-base font-semibold leading-5 text-dark">
                                        {{ index + 1 }}
                                    </p>

                                    <div class="grid">
                                        <p class="text-base font-semibold leading-5 text-dark">
                                            {{ item.name }}
                                        </p>

                                        <p class="text-sm font-normal leading-4 text-greyish">
                                            {{ item.sku }}
                                        </p>

                                        <div class="flex gap-x-2.5">
                                            <div
                                                v-for="(option, optionIndex) in item.additional?.attributes"
                                                :key="optionIndex"
                                                class="flex gap-x-2.5"
                                            >
                                                <p class="text-sm font-medium leading-4 text-greyish">
                                                    {{ option.attributeName + ':' }}
                                                </p>

                                                <p class="text-sm font-normal leading-4 text-greyish">
                                                    {{ option.optionLabel }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2.5">
                                    <template v-if="item.customPrice">
                                        <div class="grid">
                                            <span class="leading-4 text-greyish line-through">
                                                {{ item.product.priceHtml.minPrice }}
                                            </span>

                                            <span class="text-base font-semibold leading-5 text-dark">
                                                {{ item.formattedPrice.price }}
                                            </span>
                                        </div>
                                    </template>

                                    <template v-else>
                                        <span class="text-base font-semibold leading-5 text-dark">
                                            {{ item.formattedPrice?.price }}
                                        </span>
                                    </template>

                                    <div
                                        class="flex h-4 w-4 cursor-pointer items-center justify-center rounded-full bg-greyish"
                                        @click="removeCartItem(item)"
                                    >
                                        <span class="icon-cross text-base leading-6 text-white"></span>
                                    </div>
                                </div>
                            </div>

                            <template v-if="showMore[index]">
                                <div class="grid grid-cols-2 items-start gap-4 pl-9">
                                    <div class="grid content-start gap-1">
                                        <label
                                            for="quantity"
                                            class="text-base font-normal leading-5 text-dark"
                                        >
                                            {{ $t('pos.common.cart.quantity') }}
                                        </label>

                                        <div class="relative">
                                            <v-field
                                                type="text"
                                                name="quantity"
                                                id="quantity"
                                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish py-2 pl-2.5 pr-12 transition-all"
                                                v-model="item.quantity"
                                            />

                                            <div
                                                class="absolute top-1 flex h-[34px] cursor-pointer items-center rounded bg-limeGreen px-1 ltr:right-1 rtl:left-1"
                                                @click="updateCartItemQty(item)"
                                            >
                                                <span class="icon-checkout text-2xl text-white"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="isOnline"
                                        class="grid content-center gap-1"
                                    >
                                        <label
                                            for="discount"
                                            class="text-base font-normal leading-5 text-dark"
                                        >
                                            {{ $t('pos.common.cart.discount') }}
                                        </label>

                                        <div class="relative">
                                            <v-field
                                                type="text"
                                                name="discount"
                                                id="discount"
                                                :value="getAdditionalDiscountPercentage(item)"
                                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish py-2 pl-20 pr-10 text-center transition-all"
                                            />

                                            <div
                                                class="absolute top-1 flex h-[34px] cursor-pointer items-center rounded bg-light px-1 ltr:left-1 rtl:right-1"
                                            >
                                                <span class="icon-dollar text-2xl text-primary"></span>
                                            </div>

                                            <div
                                                class="absolute top-1 flex h-[34px] cursor-pointer items-center rounded bg-light px-1 ltr:left-10 rtl:right-10"
                                            >
                                                <span class="icon-percentage text-2xl text-primary"></span>
                                            </div>

                                            <div
                                                class="absolute top-1 flex h-[34px] cursor-pointer items-center rounded bg-limeGreen px-1 ltr:right-1 rtl:left-1"
                                                @click="applyAdditionalDiscount(item)"
                                            >
                                                <span class="icon-checkout text-2xl text-white"></span>
                                            </div>
                                        </div>

                                        <v-error-message
                                            name="last_name"
                                            class="text-sm text-red-500"
                                        />
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex h-14 items-center justify-between rounded-lg bg-light px-4">
                        <p class="text-lg font-medium leading-6">
                            {{ $t('pos.common.cart.add') }}
                        </p>

                        <div class="flex items-center gap-4">
                            <template v-if="isOnline">
                                <!-- Coupon code -->
                                <template v-if="currentCart?.couponCode">
                                    <div class="flex items-center">
                                        <p class="text-base leading-5 text-primary">
                                            {{ currentCart.couponCode }}
                                        </p>

                                        <span
                                            class="icon-cross cursor-pointer text-2xl text-primary"
                                            @click="removeCartCoupon()"
                                        >
                                        </span>
                                    </div>
                                </template>

                                <template v-else>
                                    <p
                                        class="cursor-pointer text-base leading-5 text-primary"
                                        @click="$refs.couponModal.toggle()"
                                    >
                                        {{ $t('pos.common.cart.coupon_code') }}
                                    </p>
                                </template>
                            </template>

                            <!-- Order note -->
                            <template v-if="currentCart?.note">
                                <div class="flex items-center">
                                    <p class="text-base leading-5 text-primary">
                                        {{ $t('pos.common.cart.note') }}
                                    </p>

                                    <span
                                        class="icon-cross cursor-pointer text-2xl text-primary"
                                        @click="removeOrderNote()"
                                    >
                                    </span>
                                </div>
                            </template>

                            <template v-else>
                                <p
                                    class="cursor-pointer text-base leading-5 text-primary"
                                    @click="$refs.orderNoteModal.toggle()"
                                >
                                    {{ $t('pos.common.cart.note') }}
                                </p>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template v-slot:footer>
                <div class="grid gap-2">
                    <div class="flex justify-between">
                        <p class="text-base font-normal leading-5 text-dark">
                            {{ $t('pos.common.cart.subtotal') }}
                        </p>

                        <p class="text-base font-semibold leading-5 text-dark">
                            {{ currentCart?.formattedPrice?.subTotal }}
                        </p>
                    </div>

                    <div class="flex justify-between">
                        <p class="text-base font-normal leading-5 text-dark">
                            {{ $t('pos.common.cart.tax') }}
                        </p>

                        <p class="text-base font-semibold leading-5 text-dark">
                            {{ currentCart?.formattedPrice?.taxTotal }}
                        </p>
                    </div>

                    <div
                        class="flex justify-between"
                        v-if="currentCart?.discountAmount"
                    >
                        <p class="text-base font-normal leading-5 text-dark">
                            {{ $t('pos.common.cart.discount') }}
                        </p>

                        <p class="text-base font-semibold leading-5 text-dark">
                            {{ currentCart?.formattedPrice?.discountAmount }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-between">
                    <p class="text-xl font-medium leading-6 text-dark">
                        {{ $t('pos.common.cart.payable_amount') }}
                    </p>

                    <p class="text-xl font-medium leading-6 text-dark">
                        {{ currentCart?.formattedPrice?.grandTotal }}
                    </p>
                </div>

                <div class="mt-2.5 flex justify-between gap-2.5">
                    <button
                        type="button"
                        class="primary-button w-full"
                        :class="[! currentCart?.id ? 'opacity-50' : '']"
                        :disabled="! currentCart?.id"
                        @click="holdOrder"
                    >
                        <span class="icon-hold-cart text-2xl"></span>

                        {{ $t('pos.common.cart.hold_order') }}
                    </button>

                    <template v-if="currentCart?.id">
                        <router-link
                            :to="`/payment/${currentCart?.id}`"
                            class="secondary-button w-full"
                        >
                            <span class="icon-checkout text-2xl"></span>

                            {{ $t('pos.common.cart.proceed') }}
                        </router-link>
                    </template>

                    <template v-else>
                        <button
                            type="button"
                            class="secondary-button w-full opacity-50"
                            disabled
                        >
                            <span class="icon-checkout text-2xl"></span>

                            {{ $t('pos.common.cart.proceed') }}
                        </button>
                    </template>
                </div>
            </template>
        </drawer>

        <Teleport to="body">
            <modal ref="couponModal">
                <template v-slot:header>
                    {{ $t('pos.common.cart.coupon_form.title') }}
                </template>
                
                <template v-slot:content="{ toggle }">
                    <v-form
                        class="grid gap-4"
                        @submit="submitCouponForm()"
                    >
                        <div class="grid gap-1">
                            <v-field
                                type="text"
                                name="code"
                                id="code"
                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                rules="required"
                                placeholder="XXX000XXX"
                                v-model="couponCode"
                            />

                            <v-error-message
                                name="coupon"
                                class="text-sm text-red-500"
                            />
                        </div>

                        <div class="flex justify-end gap-6">
                            <button
                                type="button"
                                class="transparent-button w-36"
                                @click="toggle"
                            >
                                {{ $t('pos.common.cart.coupon_form.cancel_btn_title') }}
                            </button>

                            <button
                                type="submit"
                                class="primary-button w-36"
                            >
                                {{ $t('pos.common.cart.coupon_form.proceed_btn_title') }}
                            </button>
                        </div>
                    </v-form>
                </template>
            </modal>

            <modal ref="orderNoteModal">
                <template v-slot:header>
                    {{ $t('pos.common.cart.order_note_form.title') }}
                </template>
                
                <template v-slot:content="{ toggle }">
                    <v-form
                        class="grid gap-4"
                        @submit="submitOrderNoteForm"
                    >
                        <div class="grid gap-1">
                            <v-field
                                type="text"
                                name="note"
                                id="note"
                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                                rules="required"
                                v-model="orderNote"
                                :placeholder="$t('pos.common.cart.order_note_form.note_placeholder')"
                            />

                            <v-error-message
                                name="note"
                                class="text-sm text-red-500"
                            />
                        </div>

                        <div class="flex justify-end gap-6">
                            <button
                                type="button"
                                class="transparent-button w-36"
                                @click="toggle"
                            >
                                {{ $t('pos.common.cart.order_note_form.cancel_btn_title') }}
                            </button>

                            <button
                                type="submit"
                                class="primary-button w-36"
                            >
                                {{ $t('pos.common.cart.order_note_form.proceed_btn_title') }}
                            </button>
                        </div>
                    </v-form>
                </template>
            </modal>
        </Teleport>
    </div>
</template>

<script setup>
    import { useI18n } from 'vue-i18n';
    import { useOutlet } from '@src/composable/outlet';
    import { onMounted, ref, toRaw, inject, computed, getCurrentInstance, onBeforeUnmount } from 'vue';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { useMutation } from '@vue/apollo-composable';
    import { CREATE_CART, ADD_TO_CART, UPDATE_ITEM, DELETE_ITEM } from '@src/graphql/cart';
    import { APPLY_COUPON, REMOVE_COUPON } from '@src/graphql/cart';
    
    /**
     * General use imports
     */
    const emitter = inject('emitter');
    const DB = useIndexedDB();
    const { formatPrice } = useOutlet();
    const { t } = useI18n();
    const { appContext } = getCurrentInstance();
    const isOnline = computed(() => appContext.config.globalProperties.$isOnline.value);

    /**
     * State management
     */
    const showMore = ref({});

    const showHide = (index) => {
        showMore.value[index] = !showMore.value[index];
    };

    /**
     * Cart management
     */
    const carts = ref([]);
    const currentCart = ref({});

    const getCart = async () => {        
        await DB.getAllItems('carts').then((data) => {
            carts.value = data;
            
            currentCart.value = data?.find(cart => cart.isCurrentCart);

            setCurrentCustomer();
        });
    };

    /**
     * Customer management
     */
    const currentCustomer = ref({});

    const setCurrentCustomer = async () => {        
        if (currentCart.value?.id) {
            DB.getItem('customers', 'all').then((data) => {
                currentCustomer.value = data.customers.find((customer) => customer.id === currentCart.value.customerId);
            });
        } else {
            DB.getCurrentCustomer().then((data) => {
                currentCustomer.value = data;
            });
        }
    };

    /**
     * Add to cart
     */
    const { mutate: storeCartItem } = useMutation(ADD_TO_CART);

    const addToCart = async (productData) => {        
        if (! currentCart.value?.id) {
            emitter.emit('add_flash', {
                type: 'warning',
                message: t('pos.common.cart.no_cart_selected'),
            });

            return;
        }

        productData = {
            ...productData,
            cartId: parseInt(currentCart.value.id),
        };

        if (isOnline.value) {
            storeCartItem({ input: productData }).then(response => {
                const cartResponse = response.data.storeCartItem;

                if (cartResponse.success) {
                    DB.updateItem('carts', {
                        ...toRaw(currentCart.value),
                        ...cartResponse.cart
                    }).then(() => {
                        emitter.emit('add_flash', {
                            type: 'success',
                            message: cartResponse.message,
                        });

                        getCart();
                    });
                } else {
                    emitter.emit('add_flash', {
                        type: 'error',
                        message: cartResponse.message,
                    });
                }
            });
        } else {            
            const cart = await DB.getItem('carts', currentCart.value.id);

            const product = await DB.getItem('products', 'all').then((result) => {
                return result.products.find((item) => item.id === productData.productId);
            });

            const alreadyExists = cart.items.find((item) => item.sku === product.sku);

            if (alreadyExists) {
                alreadyExists.quantity += 1;
            } else {
                cart.items.push({
                    quantity: 1,
                    sku: product.sku,
                    type: product.type,
                    name: product.name,
                    price: product.price,
                    formattedPrice: {
                        price: formatPrice(product.price),
                    },
                    productId: product.id,
                });
            }

            const totalAmount = cart.items.reduce((acc, item) => acc + (item.price * item.quantity), 0);

            const updatedCart = {
                ...cart,
                formattedPrice: {
                    subTotal: formatPrice(totalAmount),
                    grandTotal: formatPrice(totalAmount),
                },
                grandTotal: totalAmount,
            };

            await DB.updateItem('carts', updatedCart).then(() => {
                getCart();
            });

            emitter.emit('add_flash', {
                type: 'success',
                message: t('pos.common.cart.product_add_success'),
            });
        }
    };

    /**
     * Update cart item
     */
    const { mutate:updateCartItem } = useMutation(UPDATE_ITEM);
    
    const updateCartItemQty = async (item) => {
        if (isOnline.value) {
            const input = {
                cartId: parseInt(currentCart.value.id),
                qty: [{
                    cartItemId: item.id,
                    customPrice: parseFloat(item.customPrice),
                    quantity: parseInt(item.quantity),
                }]
            };

            updateCartItem({ input }).then(response => {
                const data = response.data.updateCartItem;

                if (data?.success) {
                    DB.updateItem('carts', {
                        ...toRaw(currentCart.value),
                        ...response.data.updateCartItem.cart
                    }).then(() => {
                        emitter.emit('add_flash', {
                            type: 'success',
                            message: data.message,
                        });

                        getCart();
                    });
                }
            });
        } else {
            const cart = await DB.getItem('carts', currentCart.value.id);

            const cartItem = cart.items.find((cartItem) => cartItem.sku === item.sku);

            cartItem.quantity = item.quantity;

            cart.formattedPrice = {
                subTotal: formatPrice(cart.items.reduce((acc, item) => acc + (item.price * item.quantity), 0)),
                grandTotal: formatPrice(cart.items.reduce((acc, item) => acc + (item.price * item.quantity), 0)),
            };

            await DB.updateItem('carts', cart).then(() => {
                getCart();
            });

            emitter.emit('add_flash', {
                type: 'success',
                message: t('pos.common.cart.update_success'),
            });
        }
    };

    /**
     * Get additional discount percentage
     */
    const getAdditionalDiscountPercentage = (item) => {
        if (! isOnline.value) {
            return 0;
        }

        const finalPrice = parseFloat(item.product.priceHtml.finalPrice);
        const customPrice = parseFloat(item.customPrice);

        if (isNaN(customPrice) || customPrice <= 0) {
            return 0;
        }

        const percentage = ((finalPrice - customPrice) / finalPrice) * 100;

        return percentage;
    };

    /**
     * Apply additional discount
     */
    const applyAdditionalDiscount = (item) => {      
        checkOnlineStatus();

        const finalPrice = parseFloat(item.product.priceHtml.finalPrice);
        const percentage = parseFloat(document.getElementById('discount').value);

        if (isNaN(percentage) || percentage <= 0) {
            item.customPrice = 0;
        }

        item.customPrice = finalPrice - ((percentage / 100) * finalPrice);

        updateCartItemQty(item);
    };

    /**
     * Set current cart
     */
    const setCurrentCart = async (id) => {
        toRaw(carts.value)?.forEach(async (cart) => {            
            if (cart.id === id) {
                await DB.updateItem('carts', {
                    ...cart,
                    isCurrentCart: true,
                });
            } else {
                await DB.updateItem('carts', {
                    ...cart,
                    isCurrentCart: false,
                });
            }
        });

        getCart();
    };


    /**
     * Remove cart item
     */
    const { mutate:deleteCartItem } = useMutation(DELETE_ITEM);

    const removeCartItem = async (item) => {
        if (isOnline.value) {
            const input = {
                cartItemId: item.id,
                cartId: parseInt(currentCart.value.id),
            };

            deleteCartItem({ input: input }).then(response => {
                const data = response.data.deleteCartItem;

                if (data.success) {
                    if (data.cart === null) {
                        DB.deleteItem('carts', currentCart.value.id).then(() => {
                            getCart();
                        });

                        currentCart.value = {};
                    } else {
                        DB.updateItem('carts', {
                            ...toRaw(currentCart.value),
                            ...data.cart
                        }).then(() => {
                            getCart();
                        });
                    }

                    emitter.emit('add_flash', {
                        type: 'success',
                        message: data.message,
                    });
                } else {
                    emitter.emit('add_flash', {
                        type: 'error',
                        message: data.message,
                    });
                }
            });
        } else {
            const cart = await DB.getItem('carts', currentCart.value.id);

            cart.items = cart.items.filter((cartItem) => cartItem.sku !== item.sku);

            cart.formattedPrice = {
                subTotal: formatPrice(cart.items.reduce((acc, item) => acc + (item.price * item.quantity), 0)),
                grandTotal: formatPrice(cart.items.reduce((acc, item) => acc + (item.price * item.quantity), 0)),
            };

            await DB.updateItem('carts', cart).then(() => {
                getCart();
            });

            emitter.emit('add_flash', {
                type: 'success',
                message: t('pos.common.cart.product_remove_success'),
            });
        }
    };

    /**
     * Apply coupon
     */
    const couponCode = ref('');
    const couponModal = ref(false);

    const { mutate:applyCoupon } = useMutation(APPLY_COUPON);

    const submitCouponForm = async () => {
        checkOnlineStatus();

        const input = {
            code: couponCode.value,
            cartId: parseInt(currentCart.value.id),
        };

        couponModal.value.toggle();

        couponCode.value = '';

        applyCoupon({ input }).then(response => {
            if (response.data.applyCartCoupon.success) {
                DB.updateItem('carts', {
                    ...toRaw(currentCart.value),
                    ...response.data.applyCartCoupon.cart
                }).then(() => {
                    getCart();
                });

                emitter.emit('add_flash', {
                    type: 'success',
                    message: response.data.applyCartCoupon.message,
                });
            } else {
                emitter.emit('add_flash', {
                    type: 'error',
                    message: response.data.applyCartCoupon.message,
                });
            }
        });
    };

    /**
     * Remove coupon
     */
    const { mutate:removeCoupon } = useMutation(REMOVE_COUPON);

    const removeCartCoupon = () => {
        emitter.emit('open_confirm_modal', {
            agree: async () => {
                removeCoupon({ cartId: currentCart.value.id }).then(response => {
                    if (response.data.removeCartCoupon.success) {
                        emitter.emit('add_flash', {
                            type: 'success',
                            message: response.data.removeCartCoupon.message,
                        });

                        DB.updateItem('carts', {
                            ...toRaw(currentCart.value),
                            ...response.data.removeCartCoupon.cart
                        }).then(() => {
                            getCart();
                        });
                    }
                });
            }
        })
    };

    /**
     * Submit order note form
     */
    const orderNote = ref('');
    const orderNoteModal = ref(false);

    const submitOrderNoteForm = () => {
        DB.updateItem('carts', {
            ...toRaw(currentCart.value),
            note: orderNote.value,
        }).then((result) => {
            if (result) {
                emitter.emit('add_flash', {
                    type: 'success',
                    message: t('pos.common.cart.order_note_form.create_success'),
                });

                getCart();
            } else {
                emitter.emit('add_flash', {
                    type: 'error',
                    message: t('pos.common.flash_messages.error_message'),
                });
            }

            orderNote.value = '';

            orderNoteModal.value.toggle();
        });
    };


    /**
     * Remove order note
     */
     const removeOrderNote = () => {
        emitter.emit('open_confirm_modal', {
            agree: () => {
                DB.updateItem('carts', {
                    ...toRaw(currentCart.value),
                    note: '',
                }).then((result) => {
                    if (result) {
                        emitter.emit('add_flash', {
                            type: 'success',
                            message: t('pos.common.cart.order_note_form.remove_success'),
                        });

                        getCart();
                    } else {
                        emitter.emit('add_flash', {
                            type: 'error',
                            message: t('pos.common.flash_messages.error_message'),
                        });
                    }
                });
            }
        });
    };

    /**
     * Hold order
     */
    const holdOrder = () => {        
        emitter.emit('open_confirm_modal', {
            agree: () => {
                DB.addItem('hold_orders', {
                    ...toRaw(currentCart.value),
                    isCurrentCart: false,
                }).then(result => {
                    if (result) {
                        emitter.emit('add_flash', {
                            type: 'success',
                            message: t('pos.common.cart.hold_order_success'),
                        });

                        DB.deleteItem('carts', currentCart.value.id).then(() => {
                            getCart();
                        });
                    } else {
                        emitter.emit('add_flash', {
                            type: 'error',
                            message: t('pos.common.flash_messages.error_message'),
                        });
                    }
                });
            }
        });
    };

    /**
     * Create new cart
     */
    const { mutate: createCart } = useMutation(CREATE_CART);

    const createNewCart = async () => {
        const currentCustomer = await DB.getCurrentCustomer();        

        if (! currentCustomer?.id) {
            emitter.emit('add_flash', {
                type: 'warning',
                message: t('pos.common.cart.no_customer_selected'),
            });

            return;
        }

        const alreadyExists = carts.value.find((cart) => cart.customerId === currentCustomer.id);

        if (alreadyExists) {
            emitter.emit('add_flash', {
                type: 'error',
                message: t('pos.common.cart.customer_exists'),
            });

            return;
        }

        if (isOnline.value) {
            createCart({ customerId: currentCustomer.id }).then(async (response) => {
                const { createCart } = response.data;

                if (createCart.success) {
                    emitter.emit('add_flash', {
                        type: 'success',
                        message: createCart.message,
                    });

                    DB.getAllItems('carts').then((data) => {
                        data.forEach(async (cart) => {
                            await DB.updateItem('carts', {
                                ...cart,
                                isCurrentCart: false,
                            });
                        });
                    });

                    await DB.addItem('carts', {
                        ...createCart.cart,
                        isCurrentCart: true,
                        note: '',
                    }).then(() => {
                        getCart();                    
                    });
                } else {
                    emitter.emit('add_flash', {
                        type: 'error',
                        message: createCart.message,
                    });
                }
            });
        } else {
            DB.getAllItems('carts').then((data) => {
                data.forEach(async (cart) => {
                    await DB.updateItem('carts', {
                        ...cart,
                        isCurrentCart: false,
                    });
                });
            });

            const cart = {
                customerId: currentCustomer.id,
                customerEmail: currentCustomer.email,
                customerFirstName: currentCustomer.firstName,
                customerLastName: currentCustomer.lastName,
                items: [],
                formattedPrice: {
                    subTotal: formatPrice('0.00'),
                    grandTotal: formatPrice('0.00'),
                },
                grandTotal: 0,
                isCurrentCart: true,
                note: '',
            };

            await DB.addItem('carts', cart).then(() => {
                getCart();
            });
        }
    };

    /**
     * Remove cart
     */
    const removeCart = () => {        
        emitter.emit('open_confirm_modal', {
            agree: () => {
                DB.deleteItem('carts', currentCart.value.id).then(() => {
                    emitter.emit('add_flash', {
                        type: 'success',
                        message: t('pos.common.cart.remove_success'),
                    });
                    
                    getCart();
                });
            }
        });
    };

    /**
     * Check online status
     */
    const checkOnlineStatus = () => {
        if (! isOnline.value) {
            emitter.emit('add_flash', {
                type: 'error',
                message: t('pos.common.flash_messages.offline_error'),
            });
        }
    };

    /**
     * Global events
     */
    const registerEvents = () => {
        emitter.on('add_to_cart', addToCart);

        emitter.on('customer_updated', setCurrentCustomer);
    };

    /**
     * Lifecycle hooks
     */
    onMounted(async () => {
        registerEvents();

        getCart();
    });

    onBeforeUnmount(() => {
        emitter.off('add_to_cart');

        emitter.off('customer_updated');
    });
</script>