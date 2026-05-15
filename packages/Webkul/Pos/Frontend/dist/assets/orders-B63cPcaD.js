import{g as t}from"./index-C5hIcM1_.js";const a=t`
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
`,r=t`
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
`;export{a as O,r as S};
