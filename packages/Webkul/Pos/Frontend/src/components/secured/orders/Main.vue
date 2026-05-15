<template>
    <div class="my-4 flex flex-col gap-4">
        <div
            :class="[activeTab != 'hold' ? 'xl:w-[738px] 2xl:w-[828px]' : '']"
            class="flex gap-2.5 rounded-lg bg-white py-2 max-sm:text-center md:h-12 md:px-3"
        >
            <div
                v-on:click="activeTab = index"
                :class="[activeTab === index ? 'bg-light text-primary !border-primary' : 'text-dark']"
                class="flex cursor-pointer items-center rounded-lg border-2 border-transparent px-3 py-2 text-base font-medium"
                v-for="(component, index) in components"
                :key="index"
            >
                {{ $t(`pos.orders.${index}.title`) }}
            </div>
        </div>

        <component :is="components[activeTab]" />
    </div>
</template>

<script setup>
    import { ref } from 'vue';
    import History from '@components/secured/orders/History.vue';
    import Hold from '@components/secured/orders/Hold.vue';
    import Offline from '@components/secured/orders/Offline.vue';

    const components = {
        history: History,
        hold: Hold,
        offline: Offline,
    };

    const activeTab = ref('history');
</script>