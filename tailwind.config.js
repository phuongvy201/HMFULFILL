module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./resources/**/*.css",
    ],
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                primary: "#3C50E0",
                secondary: "#80CAEE",
            },
            fontFamily: {
                satoshi: ["Satoshi", "sans-serif"],
            },
        },
    },
    plugins: [require("@tailwindcss/forms")],
};
