<template>
    <div class="flex flex-col gap-y-1.5">
        <p
            v-if="name"
            class="text-lg font-semibold"
        >
            {{ name }}
        </p>

        <div class="flex items-center gap-x-3 whitespace-nowrap">
            <div
                class="flex h-2 w-full overflow-hidden rounded-full bg-gray-200"
                role="progressbar"
                aria-valuenow="100"
                aria-valuemin="0"
                aria-valuemax="100"
            >
                <div
                    class="flex flex-col justify-center overflow-hidden whitespace-nowrap rounded-full bg-blue-600 text-center text-xs text-white transition duration-500"
                    :style="`width: ${progress}%`"
                >
                </div>
            </div>

            <div class="text-end">
                <span
                    class="ms-auto flex size-4 shrink-0 items-center justify-center rounded-full text-white"
                    :class="[progress === 100 ? 'bg-green-500' : 'bg-greyish']"
                >
                    <svg
                        class="size-3 shrink-0"
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
    defineProps({
        progress: {
            type: Number,
            required: true,
        },

        name: {
            type: String,
            required: false,
        },
    });
</script>