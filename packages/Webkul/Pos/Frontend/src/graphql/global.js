import { gql } from '@apollo/client/core';

export const FETCH_GLOBAL_DATA = gql`
    query fetchGlobalData {
        fetchGlobalData {
            locales {
                code
                name
                direction
            }
            currencies {
                id
                code
                name
                symbol
                decimal
                groupSeparator
                decimalSeparator
                currencyPosition
            }
            configurations {
                code
                value
            }
            countries {
                id
                code
                name
            }
            countryStates {
                countryCode
                states {
                    id
                    countryId
                    countryCode
                    code
                    defaultName
                }
            }
            baseCurrency
            defaultLocale
        }
    }
`;
