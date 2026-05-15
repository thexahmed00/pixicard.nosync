import{g as t}from"./index-C5hIcM1_.js";const a=t`
    mutation confirmPayment (
        $input: confirmPaymentInput!
    ) {
        confirmPayment (
            input: $input
        ) {
            success
            message
            outletOrder {
                barcodeUrl
                outlet {
                    name
                    email
                    phone
                    agent {
                        email
                        firstName
                        lastName
                    }
                }
                customerCredit {
                    changeAmount
                    tenderedAmount
                }
                order {
                    id
                    customerFirstName
                    customerLastName
                    customerEmail
                    totalQtyOrdered
                    discountAmount
                    taxAmount
                    subTotal
                    grandTotal
                    createdAt
                    customer {
                        name
                        phone
                    }
                    formattedPrice {
                        discountAmount
                        taxAmount
                        subTotal
                        grandTotal
                    }
                    items {
                        sku
                        name
                        qtyOrdered
                        price
                        baseTotal
                        formattedPrice {
                            price
                            baseTotal
                        }
                    }
                }
            }
        }
    }
`;export{a as P};
