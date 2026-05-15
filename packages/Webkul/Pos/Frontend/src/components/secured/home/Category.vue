<template>
    <span
        @click="handleClick"
        :class="[isActive ? 'bg-light text-primary !border-primary' : 'text-dark']"
        class="flex h-8 cursor-pointer items-center whitespace-nowrap rounded-lg border-2 border-transparent px-3 py-2 text-base font-medium"
    >
        {{ category.name }}
    </span>
</template>

<script setup>
    import { computed } from 'vue';

    /**
     * Props
     */
    const props = defineProps(['category', 'activeCategoryId']);

    /**
     * Computed
     */
    const isActive = computed(() => {
        return props.activeCategoryId === props.category.id;
    });

    /**
     * Emits
     */
    const emit = defineEmits(['click']);

    /**
     * Methods
     */
    const handleClick = () => {
        emit('click', props.category.id);
    };
</script>
