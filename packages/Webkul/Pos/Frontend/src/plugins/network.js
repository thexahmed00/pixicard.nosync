/**
 * This plugin provides a reactive property to track network status
 */
import { ref } from 'vue';

export default {
    install(app) {
        /**
         * Reactive property to track network status
         */
        const isOnline = ref(navigator.onLine);

        /**
         * Update network status
         */
        const updateNetworkStatus = () => {
            isOnline.value = navigator.onLine;
        }

        /**
         * Listen for network status changes
         * and update the reactive property accordingly
         */
        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);

        /**
         * Add the reactive property to the app instance
         * so that it can be accessed globally
         */
        app.config.globalProperties.$isOnline = isOnline;

        /**
         * Remove network status listeners
         * when the app is unmounted or destroyed
         */
        app.config.globalProperties.$removeNetworkListeners = () => {
            window.removeEventListener('online', updateNetworkStatus);
            window.removeEventListener('offline', updateNetworkStatus);
        };
    }
};
  