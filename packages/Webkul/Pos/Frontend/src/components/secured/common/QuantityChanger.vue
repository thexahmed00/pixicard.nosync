<template>
    <div class="flex justify-between gap-2.5">
        <div class="transparent-button min-w-[114px] justify-between">
            <span 
                class="icon-minus cursor-pointer text-2xl"
                @click="decrease"
            >
            </span>

            <p class="select-none text-center">
                {{ quantity }}
            </p>
            
            <span 
                class="icon-plus cursor-pointer text-2xl"
                @click="increase"
            >
            </span>
        </div>

        <v-field
            type="hidden"
            :name="name"
            v-model="quantity"
        />
    </div>
</template>

<script setup>
    import { ref, watch } from 'vue';

    /**
     * Define the props.
     */
    const props = defineProps({
        name: String,
        value: Number
    });

    const emit = defineEmits(['change']);

    const quantity = ref(props.value);

    /**
     * Watch the value prop.
     */
    watch(() => props.value, (value) => {
        quantity.value = value;
    });

    /**
     * Increase the quantity of the product.
     */
    const increase = () => {
        emit('change', ++quantity.value);
    };

    /**
     * Decrease the quantity of the product.
     */
    const decrease = () => {
        if (quantity.value > 1) {
            emit('change', --quantity.value);
        }
    };
</script>
