import { createRouter, createWebHistory } from "vue-router";
import I18n from '@src/plugins/i18n';

const routes = [
    {
        path: "/",
        component: () => import("./components/anonymous/Login.vue"),
        meta: { title_key: 'pos.login_form.title' },
    },
    {
        path: "/:pathMatch(.*)*",
        component: () => import("./components/anonymous/pageNotFound.vue"),
    },
    {
        path: "/home",
        component: () => import("./components/secured/layouts/Main.vue"),
        meta: {
            requiresAuth: true,
            title_key: 'pos.home.title'
        },
        children: [
            {
                path: "/home",
                component: () => import("./components/secured/home/Main.vue"),
                meta: {
                    requiresAuth: true,
                    title_key: 'pos.home.title'
                },
            },
            {
                path: "/customers",
                component: () => import("./components/secured/customers/Main.vue"),
                meta: {
                    requiresAuth: true,
                    title_key: 'pos.customers.title'
                },
            },
            {
                path: "/customers/create",
                component: () => import("./components/secured/customers/Create.vue"),
                meta: {
                    requiresAuth: true,
                    title_key: 'pos.customers.create.title'
                },
            },
            {
                path: "/customers/edit/:id",
                component: () => import("./components/secured/customers/Edit.vue"),
                meta: {
                    requiresAuth: true,
                    title_key: 'pos.customers.edit.title'
                },
            },
            {
                path: "/cashier",
                component: () => import("./components/secured/cashier/Main.vue"),
                meta: {
                    requiresAuth: true,
                    title_key: 'pos.cashier.title'
                },
            },
            {
                path: "/orders",
                component: () => import("./components/secured/orders/Main.vue"),
                meta: {
                    requiresAuth: true,
                    title_key: 'pos.orders.title'
                },
            },
            {
                path: "/products",
                component: () => import("./components/secured/products/Main.vue"),
                meta: {
                    requiresAuth: true,
                    title_key: 'pos.products.title'
                },
            },
            {
                path: "/reports",
                component: () => import("./components/secured/Reports.vue"),
                meta: {
                    requiresAuth: true,
                    title_key: 'pos.reports.title'
                },
            },
            {
                path: "/settings",
                component: () => import("./components/secured/settings/Main.vue"),
                meta: {
                    requiresAuth: true,
                    title_key: 'pos.settings.title'
                },
            },
            {
                path: "/payment/:id",
                component: () => import("./components/secured/Payment.vue"),
                meta: {
                    requiresAuth: true,
                    title_key: 'pos.payment.title'
                },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    if (to?.meta?.title_key) {
        document.title = I18n.global.t(to.meta.title_key);
    }

    const accessToken = localStorage.getItem('accessToken');

    if (
        to.matched.some(record => record.meta.requiresAuth)
        && ! accessToken
    ) {
        next('/');
    } else if (
        to.path === '/'
        && accessToken
    ) {
        next('/home');
    } else {
        next();
    }
});

export default router;
