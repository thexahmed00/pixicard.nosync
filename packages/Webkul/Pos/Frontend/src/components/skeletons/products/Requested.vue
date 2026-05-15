<template>
    <div class="grid w-full content-start gap-2">
        <div
            class="grid gap-4 rounded-lg p-6 max-sm:p-4"
            v-for="(index) in 3"
            :key="index"
        >
            <div class="flex justify-between gap-y-2.5 max-sm:flex-col md:items-center">
                <div class="skeleton h-5 w-32"></div>

                <div class="flex justify-between gap-6">
                    <div class="skeleton h-5 w-14"></div>

                    <div class="skeleton h-5 w-[60px]"></div>

                    <div class="skeleton h-5 w-[166px]"></div>
                </div>
            </div>

            <div class="skeleton h-10 w-full"></div>
        </div>
    </div>
</template>