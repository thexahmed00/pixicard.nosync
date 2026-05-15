<template>
    <div>
        <div
            v-if="outlet?.receipt"
            class="p-2 mx-2 max-w-[140mm] mb-5 hidden"
            ref="printableContent"
        >
            <div
                v-if="
                    outlet.receipt.displayLogo
                    || outlet.receipt.displayOutletName
                "
                class="flex flex-col items-center border-b border-gray-300 pb-4"
            >
                <img
                    v-if="outlet.receipt.displayLogo"
                    :src="outlet.receipt.logoUrl"
                    class="rounded"
                    :style="`
                        width: ${outlet.receipt.logoWidth};
                        height: ${outlet.receipt.logoHeight};
                    `"
                    :alt="outlet.receipt.logoAlt"
                />

                <div
                    v-if="outlet.receipt.displayOutletName"
                    class="mt-2"
                >
                    <h2 class="text-lg font-bold">{{ outletOrder.outlet.name }}</h2>
                </div>
            </div>

            <div
                v-if="outlet.receipt.displayOutletAddress"
                class="border-b border-gray-300 py-4"
            >
                <div>
                    <h2 class="text-base font-bold">
                        {{ $t('pos.orders.print.contact_info') }}
                    </h2>

                    <p class="text-base font-bold text-gray-600 leading-tight">
                        Address: {{ `${outlet?.address}, ${outlet?.city}, ${outlet?.state}, ${outlet?.country}, ${outlet?.postcode}` }} <br />

                        {{ $t('pos.orders.print.email') }}: {{ outlet.email }}<br />

                        {{ $t('pos.orders.print.phone') }}: {{ outlet.phone }}<br />

                        {{ $t('pos.orders.print.website') }}: {{ outlet.website }} <br />

                        {{ $t('pos.orders.print.customer_care') }}: {{ outlet.customerCareNumber }}
                    </p>
                </div>
            </div>

            <div
                v-html="outlet.receipt.headerContent"
                class="border-b border-gray-300 py-4 text-gray-600"
            >
            </div>

            <table class="w-full border-collapse py-4">
                <tbody>
                    <tr
                        v-if="outlet.receipt.displayDate"
                        class="border-b border-gray-300"
                    >
                        <td class="p-2">
                            {{ $t('pos.orders.print.date') }}
                        </td>

                        <td class="p-2">
                            {{ outletOrder?.order?.createdAt }}
                        </td>
                    </tr>

                    <tr
                        v-if="outlet?.receipt?.displayOrderId"
                        class="border-b border-gray-300"
                    >
                        <td class="p-2">
                            {{ outlet.receipt.orderIdLabel ?? $t('pos.orders.print.order_id') }}
                        </td>
                        
                        <td class="p-2">
                            #{{ outletOrder?.order?.id }}
                        </td>
                    </tr>

                    <tr
                        v-if="outlet?.receipt?.displayCashierName"
                        class="border-b border-gray-300"
                    >
                        <td class="p-2">
                            {{ outlet.receipt.cashierLabel ?? $t('pos.orders.print.cashier') }}
                        </td>
                        
                        <td class="p-2">
                            {{ `${outletOrder?.outlet?.agent?.firstName} ${outletOrder?.outlet?.agent?.lastName}` }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-if="outlet.receipt.displayCustomerName"
                class="border-b border-gray-300 py-4"
            >
                <div>
                    <h2 class="text-base font-bold">
                        {{ $t('pos.orders.print.customer') }}
                    </h2>

                    <p class="text-base font-bold text-gray-600 leading-tight">
                        {{ $t('pos.orders.print.name') }} {{ outletOrder?.order?.customer?.name }} <br />

                        {{ $t('pos.orders.print.email') }}: {{ outletOrder?.order?.customerEmail }}<br />

                        {{ $t('pos.orders.print.phone') }}: {{ outletOrder?.order?.customer?.phone }}<br />
                    </p>
                </div>
            </div>

            <div class="max-w-[140mm]">
                <div class="grid grid-cols-4 bg-gray-200 text-sm font-bold p-2">
                    <div>{{ $t('pos.orders.print.item') }}</div>

                    <div>{{ $t('pos.orders.print.qty') }}</div>

                    <div>{{ $t('pos.orders.print.price') }}</div>

                    <div>{{ $t('pos.orders.print.total') }}</div>
                </div>

                <div
                    v-for="(item, index) in outletOrder?.order?.items"
                    :key="index"
                    class="grid grid-cols-4 border-b border-gray-300 p-2"
                >
                    <div>{{ item.name }}</div>

                    <div>{{ item.qtyOrdered }}</div>

                    <div>{{ item?.formattedPrice?.price }}</div>

                    <div>{{ item?.formattedPrice?.baseTotal }}</div>
                </div>

                <div
                    v-if="outletOrder?.order"
                    class="py-4 border-t border-gray-300"
                >
                    <div class="grid grid-cols-4 bg-gray-100 p-2 font-medium">
                        <div></div><div></div>

                        <div>{{ $t('pos.orders.print.total_qty') }}</div>

                        <div>{{ outletOrder?.order?.totalQtyOrdered }}</div>
                    </div>

                    <div
                        v-if="outlet.receipt.displaySubTotal"
                        class="grid grid-cols-4 bg-gray-100 p-2 font-medium"
                    >
                        <div></div><div></div>

                        <div>{{ outlet.receipt.subTotalLabel ?? $t('pos.orders.print.subtotal') }}</div>

                        <div>{{ outletOrder?.order?.formattedPrice?.subTotal }}</div>
                    </div>

                    <div
                        v-if="outlet.receipt.displayDiscount"
                        class="grid grid-cols-4 bg-gray-100 p-2 font-medium"
                    >
                        <div></div><div></div>

                        <div>{{ outlet.receipt.discountLabel ?? $t('pos.orders.print.discount') }}</div>

                        <div>{{ outletOrder?.order?.formattedPrice?.discountAmount }}</div>
                    </div>

                    <div
                        v-if="outlet.receipt.displayTax"
                        class="grid grid-cols-4 bg-gray-100 p-2 font-medium"
                    >
                        <div></div><div></div>

                        <div>{{ outlet.receipt.taxLabel ?? $t('pos.orders.print.tax') }}</div>

                        <div>{{ outletOrder?.order?.formattedPrice?.taxAmount }}</div>
                    </div>

                    <div class="grid grid-cols-4 bg-gray-100 p-2 font-medium">
                        <div></div><div></div>

                        <div>{{ outlet?.receipt?.grandTotal ?? $t('pos.orders.print.grand_total') }}</div>

                        <div>{{ outletOrder?.order?.formattedPrice?.grandTotal }}</div>
                    </div>

                    <div
                        v-if="outlet.receipt.displayChangeAmount"
                        class="grid grid-cols-4 bg-gray-100 p-2 font-medium"
                    >
                        <div></div><div></div>
                        
                        <div>{{ outlet.receipt.creditChangeLabel ?? $t('pos.orders.print.change') }}</div>

                        <div>{{ outletOrder?.customerCredit?.changeAmount }}</div>
                    </div>
                </div>
            </div>

            <div
                v-if="
                    outlet.receipt.showOrderBarcode
                    && outletOrder?.barcodeUrl
                "
                class="flex flex-col items-center border-b border-gray-300 pb-4"
            >
                <img
                    :src="outletOrder.barcodeUrl"
                    class="max-w-full"
                />
            </div>

            <div class="py-4 text-sm text-gray-600">
                <p v-html="outlet.receipt.footerContent"></p>
            </div>
        </div>

        <button
            type="button"
            class="secondary-button w-full"
            @click="printInvoice"
        >
            <span class="icon-print text-2xl"></span>

            {{ $t('pos.orders.print.btn_title') }}
        </button>
    </div>
</template>

<script setup>
    import { computed, ref, onBeforeMount } from 'vue';
    import { useIndexedDB } from '@src/composable/indexed-db';

    const props = defineProps({
        outletOrder: {
            type: Object,
            required: true
        }
    });
    

    /**
     * Get the indexed db instance.
     */
    const DB = useIndexedDB();

    const agent = ref({});
    const configurations = ref([]);

    onBeforeMount(async () => {
        const agents = await DB.getAllItems('agents');

        await DB.getItem('configurations', 1).then((data) => {
            configurations.value = data.configurations;            
        });

        agent.value = agents[0] || {};        
    });

    const outlet = computed(() => agent.value?.outlet || {});
    
    /**
     * Method to print invoice.
     */
    const printableContent = ref(null);

    const printInvoice = () => {        
        /**
         * Get the html content to print.
         */
        const printableHtml = printableContent.value.innerHTML;

        /**
         * Get the styles and html content to print.
         */
        let stylesHtml = '';
        for (const node of [...document.querySelectorAll('link[rel="stylesheet"], style')]) {
            stylesHtml += node.outerHTML;
        }

        /**
         * Open a new window and print the content.
         */
        const printWindow = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');

        printWindow.document.write(
            `<!DOCTYPE html>
                <html>
                    <head>
                        <title>${outlet?.receipt?.title || 'POS Receipt'}</title>
                        ${stylesHtml}
                    </head>
                    <body>
                        ${printableHtml}
                    </body>
                </html>
            `);

        printWindow.document.close();
        printWindow.focus();
        
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 100);
    };
</script>