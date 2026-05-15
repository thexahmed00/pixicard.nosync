<template>
    <v-form v-slot="{ handleSubmit }">
        <form
            class="md:box-shadow grid gap-6 rounded-lg bg-white md:p-4"
            @submit="handleSubmit($event, submitForm)"
        >
            <div class="grid items-start gap-8 md:grid-cols-2 md:gap-2.5">
                <div class="grid content-start gap-1">
                    <label
                        for="first_name"
                        class="text-base font-semibold leading-5 text-dark"
                    >
                        {{ $t('pos.settings.account.first_name') }}
                    </label>

                    <v-field
                        type="text"
                        name="first_name"
                        id="first_name"
                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                        rules="required"
                        :label="$t('pos.settings.account.first_name')"
                        :placeholder="$t('pos.settings.account.first_name')"
                        v-model="agent.firstName"
                    />

                    <v-error-message
                        name="first_name"
                        class="text-sm text-red-500"
                    />
                </div>

                <div class="grid content-center gap-1">
                    <label
                        for="last_name"
                        class="text-base font-semibold leading-5 text-dark"
                    >
                        {{ $t('pos.settings.account.last_name') }}
                    </label>

                    <v-field
                        type="text"
                        name="last_name"
                        id="last_name"
                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                        rules="required"
                        :label="$t('pos.settings.account.last_name')"
                        :placeholder="$t('pos.settings.account.last_name')"
                        v-model="agent.lastName"
                    />

                    <v-error-message
                        name="last_name"
                        class="text-sm text-red-500"
                    />
                </div>
            </div>

            <div class="grid items-start gap-8 md:grid-cols-2 md:gap-2.5">
                <div class="grid content-center gap-1">
                    <label
                        for="email"
                        class="text-base font-semibold leading-5 text-dark"
                    >
                        {{ $t('pos.settings.account.email') }}
                    </label>

                    <v-field
                        type="email"
                        name="email"
                        id="email"
                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                        rules="required|email"
                        :placeholder="$t('pos.settings.account.email')"
                        v-model="agent.email"
                    />

                    <v-error-message
                        name="email"
                        class="text-sm text-red-500"
                    />
                </div>

                <div class="grid content-start gap-1">
                    <label
                        for="old_password"
                        class="text-base font-semibold leading-5 text-dark"
                    >
                        {{ $t('pos.settings.account.old_password') }}
                    </label>

                    <v-field
                        type="password"
                        name="old_password"
                        id="old_password"
                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                        rules="max:16"
                        :label="$t('pos.settings.account.old_password')"
                        :placeholder="$t('pos.settings.account.old_password')"
                    />

                    <v-error-message
                        name="old_password"
                        class="text-sm text-red-500"
                    />
                </div>
            </div>

            <div class="grid items-start gap-8 md:grid-cols-2 md:gap-2.5">
                <div class="grid content-start gap-1">
                    <label
                        for="new_password"
                        class="text-base font-semibold leading-5 text-dark"
                    >
                        {{ $t('pos.settings.account.new_password') }}
                    </label>

                    <v-field
                        type="password"
                        name="new_password"
                        id="new_password"
                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                        rules="max:16"
                        :label="$t('pos.settings.account.new_password')"
                        :placeholder="$t('pos.settings.account.new_password')"
                    />

                    <v-error-message
                        name="new_password"
                        class="text-sm text-red-500"
                    />
                </div>

                <div class="grid content-center gap-1">
                    <label
                        for="confirm_password"
                        class="text-base font-semibold leading-5 text-dark"
                    >
                        {{ $t('pos.settings.account.confirm_password') }}
                    </label>

                    <v-field
                        type="password"
                        name="new_password_confirmation"
                        id="new_password_confirmation"
                        class="text-light-600 hover:border-light-500 focus:border-light-500 w-full rounded border border-greyish px-2.5 py-3 transition-all"
                        rules="max:16"
                        :label="$t('pos.settings.account.confirm_password')"
                        :placeholder="$t('pos.settings.account.confirm_password')"
                    />

                    <v-error-message
                        name="new_password_confirmation"
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

                    {{ $t('pos.settings.account.update_btn_title') }}
                </button>
            </div>
        </form>
    </v-form>
</template>

<script setup>
    import { ref, watchEffect, inject, getCurrentInstance, computed } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { useMutation } from '@vue/apollo-composable';
    import { useIndexedDB } from '@src/composable/indexed-db';
    import { PROFILE_UPDATE } from '@src/graphql/settings';


    /**
     * General use variables
     */
    const { t } = useI18n();
    const DB = useIndexedDB();
    const emitter = inject('emitter');
    const { appContext } = getCurrentInstance();
    const isOnline = computed(() => appContext.config.globalProperties.$isOnline.value);


    /**
     * Get Profile
     */
    const agent = ref({});

    watchEffect(async () => {
        const agents = await DB.getAllItems('agents');

        agent.value = agents[0];
    });


    /**
     * Update Profile
     */
    const { mutate:updateProfile } = useMutation(PROFILE_UPDATE);

    const submitForm = async (params, { setErrors, resetForm }) => {        
        if (! isOnline.value) {
            emitter.emit('add_flash', {
                type: 'warning',
                message: t('pos.common.flash_messages.offline_error'),
            });

            return;
        }

        const input = {
            firstName: params.first_name,
            lastName: params.last_name,
            email: params.email,
            oldPassword: params.old_password || '',
            newPassword: params.new_password || '',
            newPasswordConfirmation: params.new_password_confirmation || '',
        };

        updateProfile({ input }).then(async (response) => {
            const { updateProfile } = response.data;

            if (updateProfile?.success === true) {
                resetForm();
                
                const updatedAgent = updateProfile.agent;                

                await DB.updateItem('agents', updatedAgent).then(() => {
                    agent.value = updatedAgent;
                });

                emitter.emit('add_flash', {
                    type: 'success',
                    message: updateProfile?.message,
                });
            } else if (updateProfile?.success === false) {
                if (updateProfile?.errors) {                    
                    setErrors(JSON.parse(updateProfile.errors));
                } else {
                    emitter.emit('add_flash', {
                        type: 'error',
                        message: updateProfile?.message,
                    });
                }
            }
        });
    };
</script>
