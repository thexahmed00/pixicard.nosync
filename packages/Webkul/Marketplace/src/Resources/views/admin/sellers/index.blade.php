<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('marketplace::app.admin.sellers.index.title')
    </x-slot:title>

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <p class="py-2.5 text-xl font-bold text-gray-800 dark:text-white">
            @lang('marketplace::app.admin.sellers.index.title')
        </p>
        <div class="flex items-center gap-x-2.5">
            <v-create-sellers-form>
                <button class="primary-button">
                    @lang('marketplace::app.admin.sellers.index.add-btn')
                </button>
            </v-create-sellers-form>
        </div>
    </div>

    {!! view_render_event('marketplace.admin.sellers.list.before') !!}

    <!-- DataGrid -->
    <x-admin::datagrid
        src="{{ route('admin.marketplace.sellers.index') }}"
        :isMultiRow="true"
        ref="seller_data"
    >
        @php
            $hasPermission = bouncer()->hasPermission('marketplace.sellers.edit') || bouncer()->hasPermission('marketplace.sellers.delete');
        @endphp

        <!-- DataGrid Header -->
        <template #header="{ isLoading, available, applied, selectAll, sort, performAction }">
            <template v-if="! isLoading">
                <div class="row grid grid-cols-[2fr_1fr_1fr] grid-rows-1 items-center border-b px-4 py-2.5 dark:border-gray-800">
                    <div
                        class="flex select-none items-center gap-2.5"
                        v-for="(columnGroup, index) in [['name', 'email', 'url'], ['created_at', 'is_approved', 'id'], ['wallet_balance', 'credit_request_date'], ['assign_product', 'flags']]"
                    >
                        @if ($hasPermission)
                            <label class="flex w-max cursor-pointer select-none items-center gap-1" for="mass_action_select_all_records" v-if="! index">
                                <input
                                    type="checkbox"
                                    id="mass_action_select_all_records"
                                    class="peer hidden"
                                    :checked="['all', 'partial'].includes(applied.massActions.meta.mode)"
                                    @change="selectAll"
                                />
                                <span class="icon-uncheckbox cursor-pointer rounded-md text-2xl" :class="[applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checked peer-checked:text-blue-600' : (applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-partial peer-checked:text-blue-600' : '')]"></span>
                            </label>
                        @endif
                        <p class="text-gray-600 dark:text-gray-300">
                            <span class="[&>*]:after:content-['_/_']">
                                <template v-for="column in columnGroup">
                                    <span
                                        class="after:content-['/'] last:after:content-['']"
                                        :class="{
                                            'text-gray-800 dark:text-white font-medium': applied.sort.column == column,
                                            'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(c => c.index === column)?.sortable,
                                        }"
                                        @click="available.columns.find(c => c.index === column)?.sortable ? sort(available.columns.find(c => c.index === column)) : {}"
                                    >
                                        @{{ available.columns.find(c => c.index === column)?.label }}
                                    </span>
                                </template>
                            </span>
                            <i class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1 rtl:mr-1" :class="[applied.sort.order === 'asc' ? 'icon-down-stat' : 'icon-up-stat']" v-if="columnGroup.includes(applied.sort.column)"></i>
                        </p>
                    </div>
                </div>
            </template>
            <template v-else>
                <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
            </template>
        </template>

        <!-- DataGrid Body -->
        <template #body="{ isLoading, available, applied, selectAll, sort, performAction }">
            <template v-if="! isLoading">
                <div
                    class="row grid grid-cols-[2fr_1fr_1fr] grid-rows-1 border-b px-4 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-for="record in available.records"
                >
                    <!-- Column 1: Seller Info -->
                    <div class="flex gap-2.5">
                        @if ($hasPermission)
                            <input
                                type="checkbox"
                                :id="`mass_action_select_record_${record.id}`"
                                :value="record.id"
                                class="peer hidden"
                                v-model="applied.massActions.indices"
                            />
                            <label class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600" :for="`mass_action_select_record_${record.id}`"></label>
                        @endif
                        <div class="flex flex-col gap-1.5">
                            <p class="text-base font-semibold text-gray-800 dark:text-white" v-text="record.name"></p>
                            <p class="text-gray-600 dark:text-gray-300" v-text="record.email"></p>
                            <a class="text-sm text-blue-600 underline" target="_blank" :href="`{{ route('shop.marketplace.sellers.show', '') }}/${record.url}`">
                                @{{ record.url }}
                            </a>
                        </div>
                    </div>

                    <!-- Column 2: Status and IDs -->
                    <div class="flex flex-col gap-1.5">
                        <p class="text-gray-600 dark:text-gray-300" v-text="record.created_at"></p>
                        <div class="flex gap-2">
                            <p class="text-gray-600 dark:text-gray-300" v-html="record.is_approved"></p>
                            <p class="text-gray-600 dark:text-gray-300" v-if="record.is_suspended">
                                <label class="label-canceled py-1">@lang('marketplace::app.admin.sellers.index.datagrid.suspended')</label>
                            </p>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300">
                            @{{ "@lang('marketplace::app.admin.sellers.index.datagrid.seller-id')".replace(':seller_id', record.id) }}
                        </p>
                    </div>

                    <!-- Column 3: Wallet, Credit Request, Actions -->
                    <div class="flex items-center justify-between gap-x-4">
                        <div class="flex flex-col gap-1.5">
                            <!-- Wallet -->
                            <div class="flex items-center gap-x-2">
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Wallet:</p>
                                <div>
                                    <p class="text-gray-800 dark:text-white" v-html="record.wallet_balance"></p>
                                    {{-- START: CORRECTED BUTTON CLASS --}}
                                    <a 
                                        :href="`{{ route('admin.marketplace.sellers.wallet', '') }}/${record.id}`" 
                                        class="secondary-button text-sm"
                                    >
                                        View
                                    </a>
                                    {{-- END: CORRECTED BUTTON CLASS --}}
                                </div>
                            </div>

                            <!-- Credit Request -->
                            <div class="mt-1.5 flex items-center gap-x-2">
                                <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Credit Request:</p>
                                <p class="font-semibold text-orange-500" v-html="record.credit_request_date"></p>
                            </div>

                            <!-- Assign Product & Flags -->
                             <div class="mt-2 flex flex-col gap-1.5">
                                @if (bouncer()->hasPermission('marketplace.sellers.assign_product'))
                                    <a :href="`{{ route('admin.marketplace.sellers.products.search', '') }}/${record.id}`" class="secondary-button w-fit text-sm">
                                        @lang('marketplace::app.admin.sellers.index.datagrid.add-product')
                                    </a>
                                @endif
                                <a :href="`{{ route('admin.marketplace.sellers.flags.index', '') }}/${record.id}`" class="text-sm text-blue-600 underline">
                                    @{{ "@lang('marketplace::app.admin.sellers.index.datagrid.total-flags')".replace(':count', record.flags) }}
                                </a>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center">
                             @if (bouncer()->hasPermission('marketplace.sellers.edit'))
                                <a :href="record.actions.find(action => action.icon === 'icon-exit')?.url" target="_blank">
                                    <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 ltr:ml-1 rtl:mr-1" :class="record.actions.find(action => action.icon === 'icon-exit')?.icon" :title="record.actions.find(action => action.icon === 'icon-exit')?.title"></span>
                                </a>
                            @endif
                            <a v-for="action in record.actions.filter(action => action.icon != 'icon-exit')" @click="performAction(action)">
                                <span class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 ltr:ml-1 rtl:mr-1" :class="action.icon" :title="action.title"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
            <template v-else>
                <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
            </template>
        </template>
    </x-admin::datagrid>

    {!! view_render_event('marketplace.admin.sellers.list.after') !!}

    @pushOnce('scripts')
        <script type="text/x-template" id="v-create-sellers-form-template">
            @if (bouncer()->hasPermission('marketplace.sellers.create'))
                <button
                    class="primary-button"
                    @click="$refs.sellerCreateModal.toggle()"
                >
                    @lang('marketplace::app.admin.sellers.index.add-btn')
                </button>
            @endif

            <x-admin::form v-slot="{ meta, errors, handleSubmit }" as="div">
                <form @submit="handleSubmit($event, create)">
                    <x-admin::modal ref="sellerCreateModal">
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @lang('marketplace::app.admin.sellers.index.create.title')
                            </p>
                        </x-slot:header>
                        <x-slot:content>
                            {{-- Form content remains unchanged --}}
                        </x-slot:content>
                        <x-slot:footer>
                            <div class="flex items-center gap-x-2.5">
                                <button class="primary-button">
                                    @lang('marketplace::app.admin.sellers.index.create.save-btn')
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-create-sellers-form', {
                template: '#v-create-sellers-form-template',
                // Component logic remains unchanged
            });
        </script>
    @endPushOnce
</x-admin::layouts>

