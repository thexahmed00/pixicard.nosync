<template>
    <v-form ref="productViewForm">
        <form
            class="grid gap-6"
            @submit="submitForm($event)"
        >
            <Bundle
                v-if="product?.type === 'bundle'"
                :product="product"
            />

            <Configurable
                v-else-if="product?.type === 'configurable'"
                :product="product"
            />

            <Downloadable
                v-else-if="product?.type === 'downloadable'"
                :product="product"
            />

            <Grouped
                v-else-if="product?.type === 'grouped'"
                :product="product"
            />

            <div class="flex justify-between gap-2.5">
                <template v-if="displayQuantityChanger">
                    <quantity-changer
                        name="quantity"
                        :value="1"
                    />
                </template>

                <button
                    type="submit"
                    class="transparent-button w-full"
                >
                    <span class="icon-cart text-2xl"></span>

                    {{ $t('pos.home.product.view.add_to_cart') }}
                </button>
            </div>
        </form>
    </v-form>
</template>

<script setup>
    import { ref, computed, inject } from 'vue';
    import QuantityChanger from '@components/secured/common/QuantityChanger.vue';
    import Bundle from '@components/secured/home/product/type/Bundle.vue';
    import Configurable from '@components/secured/home/product/type/Configurable.vue';
    import Downloadable from '@components/secured/home/product/type/Downloadable.vue';
    import Grouped from '@components/secured/home/product/type/Grouped.vue';

    /**
     * Define the props.
     */
    const props = defineProps({
        product: {
            type: Object,
            required: true,
        },
        productModal: {
            type: Object,
            required: true,
        },
    });

    const emitter = inject('emitter');

    const displayQuantityChanger = computed(() => !(['downloadable', 'grouped'].includes(props.product.type)));

    /**
     * Submit the form.
     */
    const productViewForm = ref(null);
    
    const submitForm = (event) => {
        event.preventDefault();

        productViewForm.value.validate().then(result => {            
            if (result.valid) {
                let params = new FormData(event.target);

                const formData = {};

                params.forEach((value, key) => {
                    if (
                        key.includes('[')
                        && key.includes(']')
                    ) {
                        let baseKey = key.split('[')[0];
                        let index = key.match(/\[(.*?)\]/)[1];

                        formData[baseKey] = formData[baseKey] || {};
                        formData[baseKey][index] = formData[baseKey][index] || [];

                        formData[baseKey][index].push(Number(value));
                    } else {
                        formData[key] = Number(value);
                    }
                });

                addItemToCart(formData);
            }
        });
    };

    /**
     * Add item to cart.
     */
    const addItemToCart = (data) => {
        let productData = {};        

        if (props.product.type === 'configurable') {
            productData = {
                productId: props.product.id,
                quantity: data.quantity,
                superAttribute: [
                    {
                        attributeId: parseInt(data.super_attribute[23]),
                        attributeOptionId: parseInt(data.super_attribute[24]),
                    }
                ],
                selectedConfigurableOption: data.selected_configurable_option,
            };
        } else if (props.product.type === 'downloadable') {            
            productData = {
                productId: props.product.id,
                quantity: 1,
                links:  Object.values(data.links).flat(),
            };
        } else if (props.product.type === 'grouped') {
            let qty = Object.keys(data.qty).map(key => ({
                productId: Number(key),
                quantity: data.qty[key][0]
            }));

            productData = {
                productId: props.product.id,
                quantity: 1,
                qty: qty,
            };
        } else if (props.product.type === 'bundle') {
            const bundleOptions = Object.keys(data.bundle_options).map(key => ({
                bundleOptionId: Number(key),
                bundleOptionProductId: data.bundle_options[key],
                qty: data.bundle_option_qty[key][0],
            }));

            productData = {
                productId: props.product.id,
                quantity: data.quantity,
                bundleOptions: bundleOptions,
            };
        }
        
        emitter.emit('add_to_cart', productData);

        props.productModal.toggle();
    };
</script>
