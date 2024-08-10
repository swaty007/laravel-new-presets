import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import glob from "glob";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                ...glob.sync("resources/css/*/*.*"),
                ...glob.sync("resources/css/*.*"),
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/js"),
        }
    },
    build: {
        rollupOptions: {
            output:{
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        return id.toString().split('node_modules/')[1].split('/')[0].toString();
                    }
                }
            }
        },
        minify: true,
    },
});
