import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/ts/app.ts'],
            refresh: true,
            fonts: [
                // Geometric sans for interface and body copy; a text serif for
                // editorial headings, which is what gives the site its
                // institutional rather than start-up register.
                bunny('DM Sans', { weights: [400, 500, 600, 700] }),
                bunny('Source Serif 4', { weights: [400, 600, 700] }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
