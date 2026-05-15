import{g as e}from"./index-C5hIcM1_.js";const s=e`
    query getCustomers($page: Int!, $first: Int!) {
        getCustomers(page: $page, first: $first) {
            paginatorInfo {
                count
                currentPage
                lastPage
                total
            }
            data {
                id
                firstName
                lastName
                name
                gender
                dateOfBirth
                email
                phone
                image
                imageUrl
                status
                channelId
                isVerified
                isSuspended
                createdAt
                updatedAt
                addresses {
                    id
                    addressType
                    address
                    city
                    state
                    country
                    postcode
                    createdAt
                    updatedAt
                }
            }
        }
    }
`,a=e`
    mutation createPosCustomer (
        $input: createCustomerInput!
    ) {
        createPosCustomer (
            input: $input
        ) {
            success
            errors
            message
            customer {
                id
                firstName
                lastName
                name
                gender
                dateOfBirth
                email
                phone
                image
                imageUrl
                status
                channelId
                isVerified
                isSuspended
                createdAt
                updatedAt
                addresses {
                    id
                    addressType
                    address
                    city
                    state
                    country
                    postcode
                    createdAt
                    updatedAt
                }
            }
        }
    }
`,d=e`
    mutation updatePosCustomer (
        $input: updateCustomerInput!
    ) {
        updatePosCustomer (
            input: $input
        ) {
            success
            errors
            message
            customer {
                id
                firstName
                lastName
                name
                gender
                dateOfBirth
                email
                phone
                image
                imageUrl
                status
                channelId
                isVerified
                isSuspended
                createdAt
                updatedAt
                addresses {
                    id
                    addressType
                    address
                    city
                    state
                    country
                    postcode
                    createdAt
                    updatedAt
                }
            }
        }
    }
`,r=e`
    mutation deletePosCustomer($id: ID!) {
        deletePosCustomer(id: $id) {
            success
            message
        }
    }
`;export{s as C,r as D,d as U,a};
