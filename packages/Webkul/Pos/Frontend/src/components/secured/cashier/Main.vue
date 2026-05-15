<template>
    <div>
        <div class="mt-4 grid gap-4 xl:w-[738px] 2xl:w-[828px]">
            <div class="flex gap-2.5 rounded-lg bg-white py-2 max-sm:text-center md:h-12 md:px-3">
                <div
                    v-on:click="activeTab = index"
                    :class="[activeTab === index ? 'bg-light text-primary !border-primary' : 'text-dark']"
                    class="flex cursor-pointer items-center rounded-lg border-2 border-transparent px-3 py-2 text-base font-medium"
                    v-for="(component, index) in components"
                    :key="index"
                >
                    {{ $t(`pos.cashier.${index}.title`) }}
                </div>
            </div>
        </div>

        <KeepAlive>
            <component :is="components[activeTab]" />
        </KeepAlive>
    </div>
</template>

<script setup>
    import { ref } from 'vue';
    import CashDrawer from '@components/secured/cashier/CashDrawer.vue';
    import TodaySale from '@components/secured/cashier/TodaySale.vue';
    import SaleHistory from '@components/secured/cashier/SaleHistory.vue';

    const components = {
        'cash_drawer': CashDrawer,
        'today_sale': TodaySale,
        'sale_history': SaleHistory,
    };

    const activeTab = ref('cash_drawer');
</script>
