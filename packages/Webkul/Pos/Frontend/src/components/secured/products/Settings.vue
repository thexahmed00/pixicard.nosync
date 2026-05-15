<template>
    <v-form
        class="md:box-shadow grid gap-6 rounded-lg bg-white md:p-4 xl:w-[738px] 2xl:w-[828px]"
        @submit="submitForm"
    >
        <div class="grid content-start gap-1">
            <label
                for="minimum_qty"
                class="text-base font-semibold leading-5 text-dark"
            >
                {{ $t('pos.products.settings.minimum_qty') }}
            </label>

            <v-field
                type="text"
                name="minimum_qty"
                class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                rules="required|numeric|min_value:10|max_value:1000"
                v-model="lowStockQty"
                :label="$t('pos.products.settings.minimum_qty')"
                placeholder="10"
            />

            <v-error-message
                name="minimum_qty"
                class="text-sm text-red-500"
            />
        </div>

        <div>
            <button
                type="submit"
                class="primary-button w-full px-7 py-4 md:w-[280px]"
            >
                <span class="icon-check text-2xl"></span>

                {{ $t('pos.products.settings.save_btn_title') }}
            </button>
        </div>
    </v-form>
</template>

<script setup>
    import { ref, watchEffect, inject, computed, getCurrentInstance } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useMutation } from '@vue/apollo-composable';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { PROFILE_UPDATE } from '@src/graphql/settings';


    /**
     * General Variables
     */
    const DB = useIndexedDB();
    const { t } = useI18n();
    const emitter = inject('emitter');
    const { appContext } = getCurrentInstance();
    const isOnline = computed(() => appContext.config.globalProperties.$isOnline.value);


    /**
     * Form Variables
     */
    const lowStockQty = ref(null);

    /**
     * Set the form values
     */
    const agent = ref(null);

    watchEffect(() => {
        DB.getAllItems('agents', 1).then((result) => {
            agent.value = result[0];

            if (agent.value) {
                lowStockQty.value = agent.value.outlet.lowStockQty || null;
            }
        });
    });

    /**
     * Update Min Qty
     */
    const { mutate: updateProfile } = useMutation(PROFILE_UPDATE);

    const submitForm = async () => {
        if (! isOnline.value) {
            emitter.emit('add_flash', {
                type: 'warning',
                message: t('pos.common.flash_messages.offline_error'),
            });

            return;
        }

        const input = {
            firstName: agent.value.firstName,
            lastName: agent.value.lastName,
            email: agent.value.email,
            lowStockQty: parseInt(lowStockQty.value),
        };       

        updateProfile({ input }).then((response) => {
            const updateProfile = response.data?.updateProfile;

            if (updateProfile?.success === true) {                
                DB.updateItem('agents', updateProfile?.agent);

                emitter.emit('add_flash', {
                    type: 'success',
                    message: t('pos.products.settings.save_success'),
                });
            } else if (updateProfile?.success === false) {
                emitter.emit('add_flash', {
                    type: 'error',
                    message: t('pos.common.flash_messages.error_message'),
                });
            }
        });       
    }
</script>