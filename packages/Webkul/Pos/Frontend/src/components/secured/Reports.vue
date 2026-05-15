<template>
    <div class="my-4 grid gap-4">
        <div class="grid gap-4">
            <div class="flex gap-x-4 gap-y-2 max-sm:flex-col">
                <div class="flex gap-2.5 rounded-lg bg-white py-2 md:h-12 md:px-3">
                    <div
                        v-on:click="activeTab = tab"
                        :class="[activeTab === tab ? 'bg-light text-primary !border-primary' : 'text-dark']"
                        class="flex cursor-pointer items-center rounded-lg border-2 border-transparent px-3 py-2 text-base font-medium"
                        v-for="(tab, index) in tabs"
                        :key="index"
                    >
                        {{ $t(`pos.reports.${tab}`) }}
                    </div>
                </div>

                <div class="flex gap-2.5 rounded-lg bg-white py-2 md:h-12 md:px-3">
                    <date>
                        <v-field
                            type="text"
                            name="start_date"
                            id="start_date"
                            class="hover:border-light-500 focus:border-light-500 w-full cursor-pointer rounded-lg border-2 border-dark px-2 py-1 text-dark transition-all max-sm:py-2"
                            v-model="startDate"
                        />
                    </date>

                    <div class="flex items-center">
                        -
                    </div>

                    <date>
                        <v-field
                            type="text"
                            name="end_date"
                            id="end_date"
                            class="hover:border-light-500 focus:border-light-500 w-full cursor-pointer rounded-lg border-2 border-dark px-2 py-1 text-dark transition-all max-sm:py-2"
                            v-model="endDate"
                        />
                    </date>
                </div>
            </div>
        </div>

        <template v-if="loading">
            <GraphSkeleton />
        </template>

        <template v-else>
            <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3">
                <div
                    class="box-shadow rounded-lg bg-white p-4"
                    v-for="(chart, index) in charts"
                    :key="index"
                >
                    <div class="flex justify-between">
                        <div class="grid gap-1.5">
                            <div class="text-lg font-medium leading-6 text-greyish">
                                {{ chart?.name }}
                            </div>

                            <div class="text-3xl font-semibold text-dark">
                                <template v-if="['ordersCount', 'avgItemsPerOrder'].includes(index)">
                                    {{ chart.total }}
                                </template>

                                <template v-else>
                                    {{ formatPrice(chart.total) }}
                                </template>
                            </div>
                        </div>

                        <template v-if="chart.goingUp">
                            <div class="flex h-10 items-center self-end rounded-lg bg-[#EDF3FF] px-2.5">
                                <span class="text-lg font-medium leading-6 text-[#2B6FFF]">
                                    {{ chart.progress }}
                                </span>

                                <span class="icon-arrow-up text-2xl text-[#2B6FFF]"></span>
                            </div>
                        </template>

                        <template v-else>
                            <div class="flex h-10 items-center self-end rounded-lg bg-[#FFE8E5] px-2.5">
                                <span class="text-lg font-medium leading-6 text-[#F83015]">
                                    {{ chart.progress }}
                                </span>

                                <span class="icon-arrow-down text-2xl text-[#F83015]"></span>
                            </div>
                        </template>
                    </div>

                    <Chart
                        :series="chart?.series"
                        :labels="chart?.labels"
                    />
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
    import { ref, computed, watch } from 'vue';
    import Chart from '@components/secured/common/Chart.vue';
    import { useQuery } from '@vue/apollo-composable';
    import { GET_SALE_REPORT } from '@src/graphql/reports';
    import GraphSkeleton from '@skeletons/reports/Graph.vue';
    import { useOutlet } from '@src/composable/outlet';

    /**
     * General variables
     */
    const { formatPrice } = useOutlet();
    const tabs = ['day', 'week', 'month'];
    const activeTab = ref('day');
    const startDate = ref(new Date().toISOString().split('T')[0]);
    const endDate = ref(new Date().toISOString().split('T')[0]);

    /**
     * Get the chart data
     */
    const { result, loading, refetch } = useQuery(GET_SALE_REPORT, {
        startDate: startDate.value,
        endDate: endDate.value,
    });

    const charts = computed(() => result.value?.getSaleReport.reports ?? []);

    /**
     * Watchers to refetch the data
     */
    watch([startDate, endDate], () => {
        refetch({
            startDate: startDate.value,
            endDate: endDate.value,
        });
    });

    watch(activeTab, (value) => {
        if (value == 'day') {
            startDate.value = new Date().toISOString().split('T')[0];
        } else if (value == 'week') {
            startDate.value = new Date(new Date().setDate(new Date().getDate() - 7)).toISOString().split('T')[0];
        } else if (value == 'month') {
            startDate.value = new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0];
        }

        endDate.value = new Date().toISOString().split('T')[0];

        refetch({
            startDate: startDate.value,
            endDate: endDate.value,
        });
    });
</script>