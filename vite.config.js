import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    // server: {
    //     host: true,          // allows external access
    //     port: 8090,          // use your preferred port
    //     hmr: {
    //         host: '192.168.5.82', // e.g. 192.168.1.10
    //     },
    // },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [`resources/views/**/*`],
        }),
        tailwindcss({
        }),
    ],

    server: {
        cors: true,
    },
});