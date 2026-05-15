<template>
    <div class="fixed top-16 z-[1000] hidden h-full bg-white p-2 shadow-[-1px_0px_0px_0px_rgba(0,0,0,0.1)_inset] xl:block">
        <div class="journal-scroll h-[calc(80vh-100px)] overflow-auto">
            <nav class="grid w-full">
                <router-link
                    v-for="(menu, index) in menus"
                    :key="index"
                    :to="menu.path"
                    :class="[$route.path.split('/')[1] === menu.name ? 'text-primary border-2 border-primary bg-light' : '']"
                    class="grid h-20 w-20 cursor-pointer place-items-center content-center rounded-lg text-greyish hover:text-primary"
                >
                    <i
                        class="icon text-2xl"
                        :class="menu.icon"
                    >
                    </i>

                    <span class="text-center text-[12px] font-medium leading-4">
                        {{ $t(`pos.layout.sidebar.${menu.name}`) }}
                    </span>
                </router-link>
            </nav>
        </div>

        <div class="mt-4 grid items-center gap-1">
            <div class="flex items-center justify-center">
                <template v-if="agent?.imageUrl">
                    <img
                        :src="agent.imageUrl"
                        class="h-10 w-10 cursor-pointer rounded-full"
                        alt="profile image"
                    >
                </template>

                <template v-else>
                    <img
                        src="/src/assets/images/user-placeholder.png"
                        class="h-10 w-10 cursor-pointer rounded-full"
                        alt="profile image"
                    >
                </template>
            </div>

            <div
                class="grid h-20 w-20 cursor-pointer place-items-center content-center rounded-lg text-greyish hover:text-primary"
                @click="logout()"
            >
                <i class="icon icon-logout text-2xl"></i>

                <span class="text-[12px] font-medium leading-4">
                    {{ $t('pos.layout.sidebar.logout') }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, watchEffect } from 'vue';
    import { useCookies } from '@src/composable/cookies';
    import { useRouter } from 'vue-router';
    import { useMutation } from '@vue/apollo-composable';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { LOGOUT } from '@src/graphql/session';


    /**
     * General use variables
     */
    const DB = useIndexedDB();
    const cookie = useCookies();

    /**
     * Get Agent Profile
     */
    const agent = ref({});

    watchEffect(async () => {
        const agents = await DB.getAllItems('agents');

        agent.value = agents[0];
    });


    /**
     * Logout
     */
    const { mutate:agentLogout } = useMutation(LOGOUT);

    const router = useRouter();

    const logout = async () => {
        try {
            const data = await agentLogout();

            if (data?.data?.agentLogout?.success) {
                localStorage.removeItem('accessToken');

                cookie.remove('products_fetched');
                cookie.remove('customers_fetched');
                cookie.remove('orders_fetched');
                cookie.remove('categories_fetched');

                router.push({path: '/'});
            }
        } catch (err) {}
    };

    /**
     * Menu Items
     */
    const menus = ref([
        {
            name: 'home',
            path: '/home',
            icon: 'icon-home',
        },
        {
            name: 'customers',
            path: '/customers',
            icon: 'icon-customer',
        },
        {
            name: 'cashier',
            path: '/cashier',
            icon: 'icon-cashier',
        },
        {
            name: 'orders',
            path: '/orders',
            icon: 'icon-cart',
        },
        {
            name: 'products',
            path: '/products',
            icon: 'icon-product',
        },
        {
            name: 'reports',
            path: '/reports',
            icon: 'icon-reports',
        },
        {
            name: 'settings',
            path: '/settings',
            icon: 'icon-settings',
        },
    ]);
</script>
