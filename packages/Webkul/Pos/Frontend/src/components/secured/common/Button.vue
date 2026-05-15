<template>
    <button
        :type="type"
        :class="buttonClasses"
    >
        <div
            v-if="isLoading"
            class="align-center inline-block h-5 w-5 animate-spin rounded-full border-4 border-solid border-r-transparent motion-reduce:animate-[spin_1.5s_linear_infinite]"
        >
        </div>

        <span
            v-else
            :class="icon"
            class="text-2xl"
        >
        </span>

        {{ label }}
    </button>
</template>

<script setup>
    import { computed } from 'vue';

    const props = defineProps({
        isLoading: Boolean,
        type: String,
        label: String,
        class: String,
        icon: String,
    });

    const buttonClasses = computed(() => props.class);
</script>