import { useCookies } from '@src/composable/cookies';

const cookies = useCookies();

/**
 * Outlet composable.
 */
export function useOutlet() {
    /**
     * Format the given date.
     * 
     * @param {string} date - The date to be formatted.
     * @returns {string} - The formatted date as a string.
     */
    const formatDate = (date) => {
        const options = {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        };

        let locale = JSON.parse(cookies.get('locale'))?.code ?? 'en';

        return new Date(date).toLocaleDateString(locale, options);
    };

    /**
     * Format the given price with currency symbol.
     *
     * @param {number} price - The price to be formatted.
     * @returns {string} - The formatted price as a string.
     */
    const formatPrice = (price) => {
        let locale = JSON.parse(cookies.get('locale'))?.code ?? 'en';

        locale = locale === 'ar' ? 'ar-SA' : locale.replace(/([a-z]{2})_([A-Z]{2})/g, '$1-$2');

        const currency = JSON.parse(cookies.get('currency'));

        const symbol = currency.symbol !== '' ? currency.symbol : currency.code;

        if (! currency.currency_position) {
            return new Intl.NumberFormat(locale, {
                style: "currency",
                currency: currency.code,
            }).format(price);
        }

        const formatter = new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency.code,
            minimumFractionDigits: currency.decimal ?? 2
        });

        const formattedCurrency = formatter.formatToParts(price)
            .map(part => {
                switch (part.type) {
                    case 'currency':
                        return '';

                    case 'group':
                        return currency.group_separator === ''
                            ? part.value
                            : currency.group_separator;

                    case 'decimal':
                        return currency.decimal_separator === ''
                            ? part.value
                            : currency.decimal_separator;

                    default:
                        return part.value;
                }
            })
            .join('');

        switch (currency.currency_position) {
            case 'left':
                return symbol + formattedCurrency;

            case 'left_with_space':
                return symbol + ' ' + formattedCurrency;

            case 'right':
                return formattedCurrency + symbol;

            case 'right_with_space':
                return formattedCurrency + ' ' + symbol;

            default:
                return formattedCurrency;
        }
    };

    return {
        formatDate,
        formatPrice,
    };
}
