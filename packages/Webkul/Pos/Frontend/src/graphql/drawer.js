import { gql } from '@apollo/client/core';

export const OPEN_DRAWER = gql`
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
`;

export const GET_DRAWER = gql`
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
`;

export const CLOSE_DRAWER = gql`
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
`;

export const GET_TODAY_SALE = gql`
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
`;

export const GET_SALE_HISTORY = gql`
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
`;
