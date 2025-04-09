import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/index.js", "resources/css/app.css"],
            refresh: true,
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            "~": "/node_modules",
        },
    },
});
