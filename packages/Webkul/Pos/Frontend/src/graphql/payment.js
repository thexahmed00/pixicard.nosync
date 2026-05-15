import { gql } from '@apollo/client/core';

export const PROCESS_PAYMENT = gql`
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
`;