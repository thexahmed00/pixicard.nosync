import{g as t}from"./index-C5hIcM1_.js";const u=t`
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
`,r=t`
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
`,s=t`
    mutation requestProductQty ($input: requestProductQtyInput!) {
        requestProductQty (input: $input) {
            success
            message
        }
    }
`,c=t`
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
`;export{c as C,r as G,s as R,u as a};
