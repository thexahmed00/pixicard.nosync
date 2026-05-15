<template>
    <transition-group
        tag="div"
        name="flash-group"
        :enter-from-class="isMobileOrTab ? 'translate-y-full opacity-0' : 'ltr:translate-x-full rtl:-translate-x-full opacity-0'"
        enter-active-class="transform transition duration-300 ease-in-out"
        :enter-to-class="isMobileOrTab ? 'translate-y-0 opacity-100' : 'ltr:translate-x-0 rtl:-translate-x-0 opacity-100'"
        :leave-from-class="isMobileOrTab ? 'translate-y-0 opacity-100' : 'ltr:translate-x-0 rtl:-translate-x-0 opacity-100'"
        leave-active-class="transform transition duration-300 ease-in-out"
        :leave-to-class="isMobileOrTab ? 'translate-y-0 scale-95 opacity-0' : 'ltr:translate-x-full rtl:-translate-x-full opacity-0'"
        class="fixed z-[10003] grid gap-2.5 justify-items-end"
        :class="isMobileOrTab ? 'bottom-5 justify-center px-5' : 'top-5 ltr:right-5 rtl:left-5'"
    >
        <div
            :style="style[flashMessage.type]"
            class="mb-2 flex w-full justify-between gap-12 rounded-lg p-3 shadow-md"
            v-for="flashMessage in flashMessages"
            :key="flashMessage.uid"
        >
            <div class="flex items-center gap-2.5">
                <span
                    :class="style[flashMessage.type].icon"
                    class="cursor-pointer text-2xl leading-6"
                >
                </span>

                <span class="text-base leading-5">
                    {{ flashMessage.message }}
                </span>
            </div>
            
            <span
                class="cursor-pointer whitespace-nowrap text-base leading-6 underline"
                @click="removeMessage(flashMessage)"
            >
                {{ $t('pos.common.flash_messages.close') }}
            </span>
        </div>
    </transition-group>
</template>

<script setup>
    import { ref, onMounted, inject, onBeforeUnmount } from 'vue';
    import { useWindowWidth } from '@src/composable/window';

    const uid = ref(0);
    const flashMessages = ref([]);
    const emitter = inject('emitter');
    const { isMobileOrTab } = useWindowWidth();    

    const style = ref({
        info: {
            background: '#0284C7',
            color: '#FFFFFF',
            icon: 'icon-info'
        },
        success: {
            background: '#059669',
            color: '#FFFFFF',
            icon: 'icon-check'
        },
        warning: {
            background: '#FACC15',
            color: '#1F2937',
            icon: 'icon-warning'
        },
        error: {
            background: '#EF4444',
            color: '#FFFFFF',
            icon: 'icon-cancel'
        },
    });

    const addMessage = (message) => {
        message.uid = uid.value++;
        flashMessages.value.push(message);

        setTimeout(() => {
            removeMessage(message)
        }, 5000);
    };

    const removeMessage = (message) => {
        let index = flashMessages.value.indexOf(message);

        if (index != -1) {
            flashMessages.value.splice(index, 1);
        }
    };

    onMounted(() => {
        emitter.on('add_flash', addMessage);
    });

    onBeforeUnmount(() => {
        emitter.off('add_flash');
    });
</script>