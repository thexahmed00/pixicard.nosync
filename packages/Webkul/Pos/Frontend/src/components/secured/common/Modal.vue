<template>
    <transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 transform scale-90"
        enter-to-class="opacity-100 transform scale-100"
        leave-active-class="transition ease-in duration-300"
        leave-from-class="opacity-100 transform scale-100"
        leave-to-class="opacity-0 transform scale-90"
    >
        <div
            v-if="isOpen"
            class="fixed inset-0 z-[10003] overflow-y-auto bg-[#0003] backdrop-blur-xs"
        >
            <div class="flex min-h-full items-end justify-center p-4">
                <div class="absolute top-1/2 z-[999] w-full max-w-[576px] -translate-y-1/2 rounded-2xl bg-white max-sm:w-[96%]">
                    <div class="grid gap-4 p-6">
                        <p class="text-2xl font-semibold text-dark">
                            <slot
                                name="header"
                                :toggle="toggle"
                            />
                        </p>

                        <slot
                            name="content"
                            :toggle="toggle"
                        />
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
    import { ref, watch } from 'vue';

    const isOpen = ref(false);

    const open = () => {
        isOpen.value = true;
    };

    const close = () => {
        isOpen.value = false;
    };

    const toggle = () => {
        isOpen.value = !isOpen.value;
    };

    watch(isOpen, (value) => {
        if (value) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'auto';
        }
    });

    defineExpose({
        isOpen,
        open,
        close,
        toggle,
    });
</script>
