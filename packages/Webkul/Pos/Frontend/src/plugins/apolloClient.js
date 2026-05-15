import { ApolloClient, createHttpLink, InMemoryCache } from '@apollo/client/core'
import { DefaultApolloClient } from '@vue/apollo-composable'
import { setContext } from '@apollo/client/link/context';
import { useCookies } from '@src/composable/cookies';

export default {
    install: (app) => {
        /**
         * Get the APP_URL from the environment variables
         */
        const env = import.meta.env;
        const APP_URL = env.VITE_APP_URL;

        /**
         * Get the cookies composable
         */
        const cookies = useCookies();

        /**
         * Create a context link to add headers to each request
         */
        const authLink = setContext((_, { headers }) => {
            const accessToken = localStorage.getItem('accessToken');

            return {
                headers: {
                    ...headers,
                    'Authorization': accessToken ? `Bearer ${accessToken}` : null,
                    'x-locale': JSON.parse(cookies.get('locale'))?.code || null,
                    'x-currency': JSON.parse(cookies.get('currency'))?.code || null,
                }
            };
        });

        /**
         * Create a new HttpLink instance
         */
        const httpLink = createHttpLink({
            uri: `${APP_URL}/graphql`,
        });

        /**
         * Create a new Apollo Client instance
         */
        const apolloClient = new ApolloClient({
            link: authLink.concat(httpLink),
            cache: new InMemoryCache({
                addTypename: false,
            }),
        });

        app.provide(DefaultApolloClient, apolloClient);
    }
};
