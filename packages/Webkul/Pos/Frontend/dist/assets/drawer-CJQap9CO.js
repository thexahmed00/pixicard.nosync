import{g as e}from"./index-C5hIcM1_.js";const a=e`
    mutation openDrawer (
        $input: drawerInput!
    ) {
        openDrawer (
            input: $input
        ) {
            success
            errors
            message
        }
    }
`,r=e`
    query getDrawerDetails {
        getDrawerDetails {
            openingAmount
            cashPaymentSale
            otherPaymentSale
            expectedDrawerAmount
            differenceAmount
            remark
        }
    }
`,o=e`
    mutation closeDrawer (
        $input: drawerInput!
    ) {
        closeDrawer (
            input: $input
        ) {
            success
            errors
            message
        }
    }
`,n=e`
    query getTodaySales (
        $orderId: Int,
        $time: String,
        $orderTotal: Int,
        $paymentMode: String
    ) {
        getTodaySales (
            orderId: $orderId,
            time: $time,
            orderTotal: $orderTotal
            paymentMode: $paymentMode
        ) {
            openingAmount
            cashPaymentSale
            otherPaymentSale
            orders {
                orderId
                orderTotal
                paymentMode
                createdAt
            }
        }
    }
`,s=e`
    query getSaleHistory (
        $date: String,
        $cashPayment: Int,
        $otherPayment: Int,
        $totalSale: Int
    ) {
        getSaleHistory (
            date: $date,
            cashPayment: $cashPayment,
            otherPayment: $otherPayment,
            totalSale: $totalSale
        ) {
            createdAt
            cashPayment
            otherPayment
            totalSale
            drawerNote
        }
    }
`;export{o as C,r as G,a as O,n as a,s as b};
