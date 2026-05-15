<x-marketplace::seller.layouts>
    <x-slot:title>
        @lang('pos::app.admin.users.users.index.title')
    </x-slot>

   <div class="flex justify-start max-lg:hidden"><div class="flex items-center gap-x-3.5"><div class="mb-2.5 flex justify-start max-lg:hidden"><div class="flex items-center gap-x-3.5"><nav aria-label=""><ol class="flex"><li class="flex items-center gap-x-2.5 text-base font-medium"><a > Profile </a><span class="mr-2.5 text-base text-[#727272] after:content-['/']"></span></li><li class="mr-2.5 flex items-center gap-x-2.5 text-base font-medium text-[#727272] after:content-['/'] after:last:hidden" aria-current="page"> Agents </li></ol></nav></div></div></div></div>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-2xl font-medium">
                @lang('pos::app.admin.users.users.index.title')
            </h2>
        </div>

        <a href="{{ route('seller.pos_users.create') }}" class="btn primary-button">
            @lang('pos::app.admin.users.users.index.create-btn')
        </a>
    </div>

    {!! view_render_event('bagisto.seller.pos_users.list.before') !!}

    <!-- Datagrid -->
    <x-shop::datagrid
        :src="route('seller.pos_users.index')"
        :isMultiRow="true"
    >

<template #header="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
            </template>

            <template v-else>
                <div class="row grid grid-cols-[2fr_1fr_1fr] grid-rows-1 items-center border-b px-4 py-2.5 dark:border-gray-800">
                    <div
                        class="flex select-none items-center gap-2.5"
                        v-for="(columnGroup, index) in [['outlet_name', 'user_email', 'full_name'], ['user_id', 'user_name', 'user_status'], ['user_image']]"
                    >
                       

                        <p class="text-gray-600 dark:text-gray-300">
                            <span class="[&>*]:after:content-['_/_']">
                                <template v-for="column in columnGroup">
                                    <span
                                        class="after:content-['/'] last:after:content-['']"
                                        :class="{
                                            'font-medium text-gray-800 dark:text-white': applied.sort.column == column,
                                            'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                        }"
                                        @click="
                                            available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)): {}
                                        "
                                    >
                                        @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                    </span>
                                </template>
                            </span>

                            <i
                                class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1.5 rtl:mr-1.5"
                                :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                v-if="columnGroup.includes(applied.sort.column)"
                            ></i>
                        </p>
                    </div>
                </div>
            </template>
        </template>

        <template #body="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
            </template>

            <template v-else>
                <div
                    class="row grid grid-cols-[2fr_1fr_1fr] grid-rows-1 border-b px-4 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-for="record in available.records"
                >
                    <!-- Outlet Name, Eamil -->
                    <div class="flex gap-2.5">
                      
                        <div class="flex flex-col gap-1.5">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @{{ record.outlet_name }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @{{ record.user_email }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @{{ record.full_name }}
                            </p>
                        </div>
                    </div>

                    <!-- User ID, Name, Status -->
                    <div class="flex flex-col gap-1.5">
                        <p class="text-gray-600 dark:text-gray-300">
                            @{{ "@lang('pos::app.admin.users.users.index.datagrid.id-value')".replace(':id', record.user_id) }}
                        </p>

                        <p class="text-gray-600 dark:text-gray-300">
                            @{{ record.user_name }}
                        </p>

                        <p :class="[record.user_status ? 'label-active': 'label-info']">
                            @{{ record.user_status ? "@lang('pos::app.admin.users.users.index.datagrid.status.options.active')" : "@lang('pos::app.admin.users.users.index.datagrid.status.options.disable')" }}
                        </p>
                    </div>

                    <!-- Image -->
                    <div class="flex items-center justify-between gap-x-4">
                        <!--  -->
                        <div class="relative">
                            <template v-if="record.user_image">
                                <img style="max-width: 65px;max-height: 65px;"
                                    class="max-h-[65px] min-h-[65px] min-w-[65px] max-w-[65px] rounded"
                                    :src="`{{ Storage::url('') }}${record.user_image}`"
                                />
                            </template>
    
                            <template v-else>
                                
                             No Image   
                               
                            </template>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <a :href="record.actions.find(action => action.method === 'GET').url">
                                <span
                                    :class="record.actions.find(action => action.method === 'GET')?.icon"
                                    class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 max-sm:place-self-center"
                                    :title="record.actions.find(action => action.method === 'GET')?.title"
                                >
                                </span>
                            </a>
                            
                            <a @click="performAction(record.actions.find(action => action.method === 'POST'))">
                                <span
                                    :class="record.actions.find(action => action.method === 'POST')?.icon"
                                    class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                    :title="record.actions.find(action => action.method === 'POST')?.title"
                                >
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </template>



       
    </x-shop::datagrid>

    {!! view_render_event('bagisto.seller.pos_users.list.after') !!}
</x-marketplace::seller.layouts>
