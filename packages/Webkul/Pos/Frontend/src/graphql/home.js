import { gql } from '@apollo/client/core';

export const PRODUCTS = gql`
    query getOutletProducts ($page: Int!, $first: Int!) {
        getOutletProducts (page: $page, first: $first) {
            paginatorInfo {
                count
                currentPage
                lastPage
                total
            }
            data {
                id
                sku
                type
                name
                quantity
                price
                convertedPrice
                barcode
                priceHtml
                images {
                    id
                    url
                }
                categories {
                    id
                    name
                    parentId
                    children {
                        id
                        name
                        parentId
                    }
                }
                variantConfigurations
                downloadableSamples {
                    id
                    url
                    file
                    fileName
                    fileUrl
                    type
                    sortOrder
                    productId
                    createdAt
                    updatedAt
                    translations {
                        id
                        locale
                        title
                        productDownloadableSampleId
                    }
                }
                downloadableLinks {
                    id
                    title
                    price
                    url
                    file
                    fileUrl
                    fileName
                    type
                    sampleUrl
                    sampleFile
                    sampleFileName
                    sampleFileUrl
                    sampleType
                    sortOrder
                    productId
                    downloads
                    translations {
                        id
                        locale
                        title
                        productDownloadableLinkId
                    }
                }
                groupedProducts {
                    id
                    qty
                    sortOrder
                    productId
                    associatedProductId
                    associatedProduct {
                        id
                        type
                        name
                        attributeFamilyId
                        sku
                        parentId
                        isSaleable
                        priceHtml {
                            minPrice
                            priceHtml
                        }
                    }
                }
                options {
                    id
                    label
                    type
                    isRequired
                    sortOrder
                    products {
                        id
                        qty
                        name
                        productId
                        isDefault
                        sortOrder
                        inStock
                        inventory
                        price {
                            regular {
                                price
                                formattedPrice
                            }
                            final {
                                price
                                formattedPrice
                            }
                        }
                    }
                }
            }
        }
    }
`;

export const CATEGORIES = gql`
    query getOutletCategories ($page: Int!, $first: Int!) {
        getOutletCategories (page: $page, first: $first) {
            paginatorInfo {
                count
                currentPage
                lastPage
                total
            }
            data {
                id
                slug
                name
                parentId
                children {
                    id
                    slug
                    name
                    parentId
                    children {
                        id
                        slug
                        name
                        parentId
                    }
                }
            }
        }
    }
`;
