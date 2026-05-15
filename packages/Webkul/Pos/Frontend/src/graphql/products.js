import { gql } from '@apollo/client/core';

export const GET_REQUESTED_PRODUCTS = gql`
    query getRequestedProducts($query: String!) {
        getRequestedProducts(query: $query) {
            id
            name
            comment
            requestedQuantity
            requestStatus
            createdAt
            updatedAt
        }
    }
`;

export const GET_LOW_STOCK_PRODUCTS = gql`
    query getLowStockProducts {
        getLowStockProducts {
            id
            sku
            type
            name
            quantity
            price
            priceHtml
            images {
                id
                url
            }
        }
    }
`;

export const REQUEST_PRODUCT_QTY = gql`
    mutation requestProductQty ($input: requestProductQtyInput!) {
        requestProductQty (input: $input) {
            success
            message
        }
    }
`;

export const CREATE_PRODUCT = gql`
    mutation createOutletProduct ($input: createOutletProductInput!) {
        createOutletProduct (input: $input) {
            success
            errors
            message
            product {
                id
                sku
                type
                name
            }
        }
    }
`;
