<<<<<<< HEAD
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
=======
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e

export default defineConfig({
    plugins: [
        laravel({
<<<<<<< HEAD
            input: ['resources/css/app.css', 'resources/js/app.js'],
=======
            input: ["resources/css/app.css", "resources/js/app.js"],
>>>>>>> 71a2c8679310540abde2d94046e1d0cb72124e9e
            refresh: true,
        }),
        tailwindcss(),
    ],
});
