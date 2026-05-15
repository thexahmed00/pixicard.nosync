<template>
    <div class="mt-4 flex items-end justify-between gap-2.5 border-b border-zinc-200 pb-2">
        <div class="grid w-full gap-1">
            <!-- Dropdown Options Container -->
            <label
                class="text-base font-semibold leading-5 text-dark"
                :class="{ 'required': option.isRequired }"
            >
                {{ option.label }}
            </label>

            <template v-if="option.type == 'select'">
                <v-field
                    as="select"
                    :name="`bundle_options[${option.id}][]`"
                    class="custom-select text-light-600 hover:border-light-500 focus:border-light-500 h-12 w-full rounded border border-greyish bg-white px-2.5 py-3 transition-all"
                    :rules="{'required': option.isRequired}"
                    v-model="selectedProduct"
                    :label="option.label"
                >
                    <option
                        value="0"
                        v-if="! option.isRequired"
                    >
                        {{ $t('pos.home.product.view.type.bundle.none') }}
                    </option>

                    <option
                        v-for="(product, index) in option.products"
                        :key="index"
                        :value="product.id"
                    >
                        {{ `${product.name} + ${product.price.final.formattedPrice}` }}
                    </option>
                </v-field>
            </template>
            
            <template v-if="option.type == 'radio'">
                <div class="grid gap-2 max-sm:gap-1">
                    <!-- None radio option if option is not required -->
                    <div
                        class="flex select-none gap-x-4"
                        v-if="! option.isRequired"
                    >
                        <v-field
                            type="radio"
                            :name="`bundle_options[${option.id}][]`"
                            :for="`bundle_options[${option.id}][${index}]`"
                            :id="`bundle_options[${option.id}][${index}]`"
                            value="0"
                            v-model="selectedProduct"
                            :rules="{'required': option.isRequired}"
                            :label="option.label"
                        />

                        <label
                            class="cursor-pointer text-zinc-500 max-sm:text-sm"
                            :for="`bundle_options[${option.id}][${index}]`"
                        >
                            {{ $t('pos.home.product.view.type.bundle.none') }}
                        </label>
                    </div>

                    <!-- Options -->
                    <div
                        class="flex select-none items-center gap-x-4 max-sm:gap-x-1.5"
                        v-for="(product, index) in option.products"
                        :key="index"
                    >
                        <v-field
                            type="radio"
                            :name="`bundle_options[${option.id}][]`"
                            :for="`bundle_options[${option.id}][${index}]`"
                            :id="`bundle_options[${option.id}][${index}]`"
                            :value="product.id"
                            v-model="selectedProduct"
                            :rules="{'required': option.isRequired}"
                            :label="option.label"
                        />

                        <label
                            class="cursor-pointer text-zinc-500 max-sm:text-sm"
                            :for="`bundle_options[${option.id}][${index}]`"
                        >
                            {{ product.name }}

                            <span class="text-black">
                                {{ `+ ${product.price.final.formattedPrice}` }}
                            </span>
                        </label>
                    </div>
                </div>
            </template>

            <template v-if="option.type == 'multiselect'">
                <v-field
                    as="multiselect"
                    :name="`bundle_options[${option.id}][]`"
                    class="custom-select text-light-600 hover:border-light-500 focus:border-light-500 h-12 w-full rounded border border-greyish bg-white px-2.5 py-3 transition-all"
                    :rules="{'required': option.isRequired}"
                    v-model="selectedProduct"
                    :label="option.label"
                >
                    <option
                        value="0"
                        v-if="! option.isRequired"
                    >
                        {{ $t('pos.home.product.view.type.bundle.none') }}
                    </option>

                    <option
                        v-for="(product, index) in option.products"
                        :key="index"
                        :value="product.id"
                        :selected="value && value.includes(product.id)"
                    >
                        {{ `${product.name} + ${product.price.final.formattedPrice}` }}
                    </option>
                </v-field>
            </template>

            <template v-if="option.type == 'checkbox'">
                <div class="grid gap-2">
                    <!-- Options -->
                    <div
                        class="flex select-none items-center gap-x-4 max-sm:gap-x-1.5"
                        v-for="(product, index) in option.products"
                        :key="index"
                    >
                        <v-field
                            type="checkbox"
                            :name="`bundle_options[${option.id}][]`"
                            :for="`bundle_options[${option.id}][${index}]`"
                            :id="`bundle_options[${option.id}][${index}]`"
                            :value="product.id"
                            v-model="selectedProduct"
                            :rules="{'required': option.isRequired}"
                            :label="option.label"
                        />

                        <label
                            class="cursor-pointer text-zinc-500 max-sm:text-sm"
                            :for="`bundle_options[${option.id}][${index}]`"
                        >
                            {{ product.name }}

                            <span class="text-black">
                                {{ `+ ${product.price.final.formattedPrice}` }}
                            </span>
                        </label>
                    </div>
                </div>
            </template>

            <v-error-message
                :name="`bundle_options[${option.id}][]`"
                class="text-sm text-red-500"
            />
        </div>

        <template v-if="['select', 'radio'].includes(option.type)">
            <quantity-changer
                :name="`bundle_option_qty[${option.id}]`"
                :value="productQty"
                @change="qtyUpdated($event)"
            />
        </template>
    </div>
</template>

<script setup>
    import { ref, computed, onMounted, watch } from 'vue';
    import QuantityChanger from '@components/secured/common/QuantityChanger.vue';

    /**
     * Define the props.
     */
    const props = defineProps(['option']);

    /**
     * Define the data properties.
     */
    const selectedProduct = ref(
        props.option.type === 'checkbox' || props.option.type === 'multiselect'
        ? []
        : null
    );

    /**
     * On mounted.
     */
    onMounted(() => {
        props.option.products.forEach((product) => {
            if (product.isDefault) {
                if (
                    props.option.type === 'checkbox'
                    || props.option.type === 'multiselect'
                ) {
                    selectedProduct.value.push(product.id);
                } else {
                    selectedProduct.value = product.id;
                }
            }
        });
    });
    
    /**
     * Product qty.
     */
    const productQty = computed(() => {
        let qty = 0;

        props.option.products.forEach((product) => {
            if (selectedProduct.value === product.id) {
                qty = product.qty;
            }
        });

        return qty;
    });

    /**
     * Watch the selected product.
     */
    const emit = defineEmits(['onProductSelected']);

    watch(selectedProduct, (value) => {
        emit('onProductSelected', value);
    });

    /**
     * Product qty updated.
     */
    const qtyUpdated = (qty) => {
        const product = props.option.products.find((data) => data.id === selectedProduct.value);

        if (product) {
            product.qty = qty;
        }
    };
</script>