export default {
    content: [
        "./index.html",
        "./src/**/*.{vue,js}",
    ],

    theme: {
        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1440px",
            1180: "1180px",
            1060: "1060px",
            991: "991px",
            868: "868px",
        },

        extend: {
            colors: {
                'primary': '#42557D',
                'limeGreen': '#09AA29',
                'dark': '#171826',
                'light': '#ECEEF2',
                'extraLight': '#FAFAFA',
                'greyish': '#9F9F9E',
            },
            
            blur: {
                xs: '2px',
            },
        },
    },

    plugins: [],
};
