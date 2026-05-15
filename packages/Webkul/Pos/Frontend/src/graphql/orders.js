import { gql } from '@apollo/client/core';

export const ORDERS = gql`
    query getOrders ($page: Int!, $first: Int!) {
        getOrders (page: $page, first: $first) {
            paginatorInfo {
                count
                currentPage
                lastPage
                total
            }
            data {
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

export const SYNC_ORDER = gql`
    mutation syncOrder ($input: syncOrderInput!) {
        syncOrder (input: $input) {
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
