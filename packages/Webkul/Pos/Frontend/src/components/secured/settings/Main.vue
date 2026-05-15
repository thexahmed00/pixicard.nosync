<template>
    <div>
        <div class="my-4 grid gap-4 xl:w-[738px] 2xl:w-[828px]">
            <div class="flex gap-2.5 rounded-lg bg-white py-2 md:h-12 md:px-3">
                <div
                    v-on:click="activeTab = index"
                    :class="[activeTab === index ? 'bg-light text-primary !border-primary' : 'text-dark']"
                    class="flex cursor-pointer items-center rounded-lg border-2 border-transparent px-3 py-2 text-base font-medium"
                    v-for="(component, index) in components"
                    :key="index"
                >
                    {{ $t(`pos.settings.${index}.title`) }}
                </div>
            </div>

            <KeepAlive>
                <component :is="components[activeTab]" />
            </KeepAlive>
        </div>
    </div>
</template>

<script setup>
    import { ref } from 'vue';
    import Account from '@components/secured/settings/Account.vue';
    import Other from '@components/secured/settings/Other.vue';

    const components = {
        account: Account,
        other: Other,
    };

    const activeTab = ref('account');
</script>