<template>
    <div>
        <div class="hidden gap-4 py-2 xl:grid">
            <div
                class="flex px-4 py-2"
                v-for="index in 15"
                :key="index"
            >
                <div class="skeleton h-5 w-16"></div>

                <div class="skeleton ml-28 h-5 w-36"></div>

                <div class="skeleton ml-44 h-5 w-24"></div>
            </div>
        </div>

        <div class="grid gap-2.5 xl:hidden">
            <div
                class="rounded-lg px-2.5 py-4"
                v-for="index in 5"
                :key="index"
            >
                <div class="flex flex-col gap-y-1">
                    <div class="flex justify-between gap-x-2.5">
                        <div class="skeleton h-5 w-16"></div>

                        <div class="skeleton h-5 w-24"></div>
                    </div>

                    <div class="skeleton h-5 w-36"></div>
                </div>
            </div>
        </div>
    </div>
</template>