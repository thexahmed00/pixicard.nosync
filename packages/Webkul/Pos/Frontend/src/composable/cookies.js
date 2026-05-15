/**
 * Get all the cookies or a specific cookie
 */
export function useCookies() {
    /**
     * Get all the cookies
     * @returns {Object}
     */
    const getAll = () => {
        const cookies = document.cookie.split(';').map(cookie => cookie.trim());

        const cookieObject = {};

        cookies.forEach(cookie => {
            let [key, value] = cookie.split('=');

            cookieObject[key] = value;
        });

        return cookieObject;
    };

    /**
     * Get a specific cookie
     * @param {String} name
     * @returns {String}
     */
    const get = (name) => {
        const cookies = getAll();

        return cookies[name] || null;
    };

    /**
     * Set a cookie
     * @param {String} name
     * @param {String} value
     * @returns {void}
     */
    const set = (name, value) => {
        let date = new Date();

        date.setFullYear(date.getFullYear() + 1);

        let expires = date.toUTCString();

        document.cookie = `${name}=${value || ''}; path=/; expires=${expires};`;
    };

    /**
     * Remove a cookie
     * @param {String} name
     * @returns {void}
     */
    const remove = (name) => {
        document.cookie = `${name}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT;`;
    }

    return {
        set,
        get,
        remove,
        getAll
    };
}
