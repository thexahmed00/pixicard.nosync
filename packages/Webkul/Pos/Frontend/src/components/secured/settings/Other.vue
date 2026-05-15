<template>
    <v-form
        class="md:box-shadow grid gap-6 rounded-lg bg-white md:p-4"
        @submit="submitForm($event)"
    >
        <div class="grid items-start gap-8 md:grid-cols-2 md:gap-2.5">
            <div class="grid content-start gap-1">
                <label
                    for="locale"
                    class="text-base font-semibold leading-5 text-dark"
                >
                    {{ $t('pos.settings.other.locale') }}
                </label>

                <v-field
                    as="select"
                    name="locale"
                    id="locale"
                    class="custom-select text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish bg-white px-2.5 py-3 transition-all"
                    rules="required"
                    v-model="form.locale"
                >
                    <option value="">
                        {{ $t('pos.settings.other.select_locale') }}
                    </option>

                    <option
                        v-for="(locale, index) in locales"
                        :key="index"
                        :value="locale.code"
                    >
                        {{ locale.name }}
                    </option>
                </v-field>

                <v-error-message
                    name="locale"
                    class="text-sm text-red-500"
                />
            </div>

            <div class="grid content-center gap-1">
                <label
                    for="currency"
                    class="text-base font-semibold leading-5 text-dark"
                >
                    {{ $t('pos.settings.other.currency') }}
                </label>

                <v-field
                    as="select"
                    name="currency"
                    id="currency"
                    class="custom-select text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish bg-white px-2.5 py-3 transition-all"
                    rules="required"
                    v-model="form.currency"
                >
                    <option value="">
                        {{ $t('pos.settings.other.select_currency') }}
                    </option>

                    <option
                        v-for="(currency, index) in currencies"
                        :key="index"
                        :value="currency.code"
                    >
                        {{ currency.name }}
                    </option>
                </v-field>

                <v-error-message
                    name="currency"
                    class="text-sm text-red-500"
                />
            </div>
        </div>

        <div>
            <button
                type="submit"
                class="primary-button w-full px-7 py-4 md:w-[280px]"
            >
                <span class="icon-check text-2xl"></span>

                {{ $t('pos.settings.other.save_btn_title') }}
            </button>
        </div>
    </v-form>
</template>

<script setup>
    import { useI18n } from 'vue-i18n';
    import { ref, watchEffect, inject } from 'vue';
    import { useCookies } from '@src/composable/cookies';
    import { useIndexedDB } from '@src/composable/indexed-db';

    /**
     * General use variables
     */
    const { t } = useI18n();
    const DB = useIndexedDB();
    const emitter = inject('emitter');
    const cookies = useCookies();

    /**
     * Fetch locales and currencies
     */
    const locales = ref([]);
    const currencies = ref([]);

    watchEffect(async () => {
        const localesCurrencies = await DB.getItem('locales_currencies', 1);

        locales.value = localesCurrencies?.locales ?? [];

        currencies.value = localesCurrencies?.currencies ?? [];
    });

    /**
     * Form state
     */
    const form = ref({
        locale: JSON.parse(cookies.get('locale'))?.code ?? 'en',
        currency: JSON.parse(cookies.get('currency'))?.code ?? 'USD'
    });

    /**
     * Submit form
     */
    const submitForm = async (params) => {
        let currentLocale = locales.value.find(locale => locale.code === params.locale);

        let currentCurrency = currencies.value.find(currency => currency.code === params.currency);

        cookies.set('locale', JSON.stringify(currentLocale));

        cookies.set('currency', JSON.stringify(currentCurrency));

        emitter.emit('add_flash', {
            type: 'success',
            message: t('pos.settings.save_success')
        });
    }
</script>
