<template>
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:w-[738px] 2xl:w-[828px]">
        <div
            class="box-shadow flex flex-col gap-2 rounded-lg bg-white p-3"
            v-for="(index) in 12"
            :key="index"
        >
            <div class="skeleton aspect-square rounded sm:max-w-[150px] md:max-w-[210px]"></div>

            <div class="flex h-full flex-col justify-between gap-2">
                <div class="flex flex-col items-center gap-1">
                    <div class="flex flex-col gap-2">
                        <div class="skeleton h-4 w-28"></div>
                    
                        <div class="skeleton h-4 w-28"></div>
                    </div>

                    <div class="skeleton h-5 w-14"></div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="skeleton h-4 w-8"></div>

                    <div class="skeleton h-10 w-10 rounded-md"></div>
                </div>
            </div>
        </div>
    </div>
</template>
