import { createApp, h } from 'vue';

/**
 * Import the main CSS file
 */
import '@src/assets/css/app.css';

/**
 * Import the main App component
 */
import App from '@src/App.vue';
import Router from '@src/router';

/**
 * Import the plugins
 */
import I18n from '@src/plugins/i18n';
import Emitter from '@src/plugins/emitter';
import Network from '@src/plugins/network';
import VeeValidate from '@src/plugins/vee-validate';
import apolloClient from '@src/plugins/apolloClient';

/**
 * Import the global components
 */
import Date from '@components/secured/common/Date.vue';
import DateTime from '@components/secured/common/DateTime.vue';
import Cart from '@components/secured/common/Cart.vue';
import Modal from '@components/secured/common/Modal.vue';
import Drawer from '@components/secured/common/Drawer.vue';
import Button from '@components/secured/common/Button.vue';

/**
 * Create the app
 */
const app = createApp({
    render: () => h(App),
});

/**
 * Register the plugins
 */
[
    Emitter,
    Router,
    I18n,
    Network,
    VeeValidate,
    apolloClient,
].forEach(plugin => app.use(plugin));


/**
 * Register the global components
 */
app.component('date', Date)
   .component('date-time', DateTime)
   .component('cart', Cart)
   .component('modal', Modal)
   .component('drawer', Drawer)
   .component('Button', Button);

/**
 * Mount the app
 */
app.mount('#app');
