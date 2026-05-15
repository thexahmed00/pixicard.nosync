<template>
    <div class="mt-4 grid gap-3">
        <div class="flex items-center justify-between">
            <div class="skeleton h-8 w-36"></div>

            <div class="skeleton h-12 w-28 rounded-lg"></div>
        </div>

        <div class="rounded-lg bg-white md:p-4">
            <div class="grid gap-4 py-2 max-sm:hidden">
                <div
                    class="flex justify-between px-4 py-2"
                    v-for="index in 5"
                    :key="index"
                >
                    <div class="skeleton h-5 w-44"></div>

                    <div class="skeleton h-5 w-24"></div>

                    <div class="skeleton h-5 w-24"></div>

                    <div class="skeleton h-5 w-20"></div>

                    <div class="skeleton h-5 w-10"></div>
                </div>
            </div>

            <div class="grid gap-2.5 md:hidden">
                <div
                    v-for="index in 5"
                    :key="index"
                    class="rounded-lg px-2.5 py-4"
                >
                    <div class="flex flex-col gap-y-1">
                        <div class="flex justify-between gap-x-2.5">
                            <div class="skeleton h-5 w-44"></div>

                            <div class="skeleton h-5 w-44"></div>
                        </div>

                        <div class="flex justify-between gap-x-2.5">
                            <div class="skeleton h-5 w-24"></div>

                            <div class="skeleton h-5 w-24"></div>
                        </div>

                        <div class="flex justify-between gap-x-2.5">
                            <div class="skeleton h-5 w-24"></div>

                            <div class="skeleton h-5 w-24"></div>
                        </div>

                        <div class="flex justify-between gap-x-2.5">
                            <div class="skeleton h-5 w-20"></div>

                            <div class="skeleton h-5 w-20"></div>
                        </div>

                        <div class="flex justify-between gap-x-2.5">
                            <div class="skeleton h-5 w-10"></div>

                            <div class="skeleton h-5 w-10"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>