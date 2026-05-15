<template>
    <div
        v-if="loading"
        class="flex justify-center items-center h-screen"
    >
        <div class="flex gap-2">
            <div class="w-5 h-5 rounded-full animate-pulse bg-blue-600"></div>
            <div class="w-5 h-5 rounded-full animate-pulse bg-blue-600"></div>
            <div class="w-5 h-5 rounded-full animate-pulse bg-blue-600"></div>
        </div>
    </div>

    <div
        v-else
        class="mx-auto"
    >
        <div class="mt-12 grid justify-center gap-10 max-w-[370px] mx-auto">
            <div class="grid justify-items-center gap-2.5">
                <div class="flex h-[74px] w-[74px] items-center justify-center rounded-full bg-white">
                    <template v-if="getConfigData('pos_logo')">
                        <img
                            :src="getConfigData('pos_logo')"
                            alt="POS Logo"
                            width="34"
                            height="46"
                        />
                    </template>

                    <template v-else>
                        <img
                            src="/src/assets/images/logo.png"
                            alt="POS Logo"
                            width="34"
                            height="46"
                        />
                    </template>
                </div>

                <h1 class="text-3xl font-semibold text-dark truncate max-w-full">
                    {{ getConfigData('heading_on_login') ?? $t('pos.title') }}
                </h1>
            </div>

            <v-form v-slot="{ handleSubmit }">
                <form
                    class="grid gap-6 rounded-2xl bg-white p-9"
                    @submit="handleSubmit($event, submitForm)"
                >
                    <h2 class="text-2xl font-semibold leading-7 truncate">
                        {{ getConfigData('sub_heading_on_login') ?? $t('pos.login_form.title') }}
                    </h2>

                    <div class="grid gap-1">
                        <label
                            for="username"
                            class="text-base font-semibold leading-5 text-dark"
                        >
                            {{ $t('pos.login_form.user_name') }}
                        </label>

                        <v-field
                            type="text"
                            name="username"
                            id="username"
                            class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded-md border border-greyish px-2.5 py-3 transition-all"
                            rules="required"
                            :placeholder="$t('pos.login_form.user_name_placeholder')"
                        />

                        <v-error-message
                            name="username"
                            class="text-sm text-red-500"
                        />
                    </div>

                    <div class="grid gap-1">
                        <label
                            for="password"
                            class="text-base font-semibold leading-5 text-dark"
                        >
                            {{ $t('pos.login_form.password') }}
                        </label>

                        <div class="relative">
                            <v-field
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                id="password"
                                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded-md border border-greyish py-3 pl-2.5 pr-10 transition-all"
                                rules="required"
                                :placeholder="$t('pos.login_form.password_placeholder')"
                            />

                            <span
                                :class="showPassword ? 'icon-eye' : 'icon-eye-off'"
                                class="absolute right-2.5 top-3.5 cursor-pointer text-2xl leading-6 opacity-60"
                                @click="showPassword = !showPassword"
                            >
                            </span>
                        </div>

                        <v-error-message
                            name="password"
                            class="text-sm text-red-500"
                        />
                    </div>

                    <label
                        for="remember"
                        class="flex items-center gap-2 text-base font-normal leading-5 text-dark"
                    >
                        <v-field
                            type="checkbox"
                            name="remember"
                            id="remember"
                            class="peer hidden"
                            value="false"
                        />

                        <span class="icon-un-checked peer-checked:icon-checked cursor-pointer rounded-md text-2xl"></span>
                        
                        {{ $t('pos.login_form.remember_password') }}
                    </label>

                    <button
                        type="submit"
                        class="primary-button"
                    >
                        {{ $t('pos.login_form.login_btn_title') }}
                    </button>
                </form>
            </v-form>
        </div>

        <footer class="mx-32">
            <div class="mb-5 mt-10 flex flex-col gap-3.5 text-center">
                <p
                    class="text-sm font-normal leading-4 text-dark"
                    v-html="getConfigData('footer_content') ?? $t('pos.footer.warning')"
                >
                </p>

                <p class="text-sm font-normal leading-4 text-dark">
                    {{ getConfigData('footer_note') ?? $t('pos.footer.copyright') }}
                </p>
            </div>
        </footer>

        <FlashMessage />
    </div>
</template>

<script setup>
    import { ref, watch, inject } from 'vue';
    import { useCookies } from '@src/composable/cookies';
    import { useQuery, useMutation } from '@vue/apollo-composable';
    import { FETCH_GLOBAL_DATA } from '@src/graphql/global';
    import { LOGIN } from '@src/graphql/session';
    import { useRouter } from 'vue-router';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import FlashMessage from '@components/shared/FlashMessage.vue';

    /**
     * General Variables
     */
    const router = useRouter();
    const DB = useIndexedDB();
    const emitter = inject('emitter');
    const cookies = useCookies();

    /**
     * Local State
     */
    const showPassword = ref(false);

    /**
     * Fetch Global Data
     */
    const configurations = ref([]);

    const { result, loading } = useQuery(FETCH_GLOBAL_DATA);

    watch(() => result?.value?.fetchGlobalData, (data) => {
        if (data) {            
            configurations.value = data.configurations;

            if (Boolean(parseInt(getConfigData('status'))) == false) {                
                router.push({ path: '/404' });
            }

            DB.updateItem('configurations', {
                id: 1,
                configurations: data.configurations,
            });

            DB.updateItem('locales_currencies', {
                id: 1,
                currencies: data.currencies,
                locales: data.locales,
            });

            DB.updateItem('countries_states', {
                id: 1,
                countries: data.countries,
                countryStates: data.countryStates,
            });

            cookies.set('locale', JSON.stringify(data.locales.find((locale) => locale.code === data.defaultLocale)));

            cookies.set('currency', JSON.stringify(data.currencies.find((currency) => currency.code === data.baseCurrency)));
        }
    });

    const { mutate: login } = useMutation(LOGIN);

    const submitForm = (params, { setErrors, resetForm }) => {
        login({
            input: {
                username: params.username,
                password: params.password,
                remember: Boolean(params.remember),
            },
        }).then(async(response) => {
            const agentLogin = response?.data?.agentLogin;

            if (agentLogin?.success === true) {
                localStorage.setItem('accessToken', agentLogin.accessToken);
                localStorage.setItem('loginMessage', agentLogin.message);

                DB.deleteAllItems('agents');

                DB.addItem('agents', agentLogin.agent);

                cookies.remove('products_fetched');
                cookies.remove('customers_fetched');
                cookies.remove('orders_fetched');
                cookies.remove('categories_fetched');

                router.push({ path: '/home' });
            } else if (agentLogin.success === false) {
                if (agentLogin.errors) {
                    setErrors(JSON.parse(agentLogin.errors));
                } else {
                    emitter.emit('add_flash', {
                        type: 'error',
                        message: agentLogin.message,
                    });
                }
            }
        });
    };

    /**
     * Get Configurations
     */
    const getConfigData = (code) => {
        return configurations.value.find((config) => config.code === `pos.settings.general.${code}`)?.value;
    };
</script>
