let mix = require('laravel-mix');
const AutoImport = require("unplugin-auto-import/webpack");
const {ElementPlusResolver} = require("unplugin-vue-components/resolvers");
const Components = require("unplugin-vue-components/webpack");
var path = require('path');

mix.webpackConfig({
    module: {
        rules: [{
            test: /\.mjs$/,
            resolve: {fullySpecified: false},
            include: /node_modules/,
            type: "javascript/auto"
        }]

    },
    plugins: [
        AutoImport({
            resolvers: [ElementPlusResolver()],
        }),
        Components({
            resolvers: [ElementPlusResolver()],
            directives: false
        }),
    ],
    resolve: {
        extensions: ['.js', '.vue', '.json'],
        alias: {
            '@': path.resolve(__dirname, 'resources/admin'),
            '@common': path.resolve(__dirname, 'resources/common'),
        }
    }
});

mix.js('resources/admin/app.js', 'assets/admin').vue({version: 3})
    .copy('resources/Blocks/images', 'assets/Blocks/images')
    .js('resources/admin/global_admin', 'assets/admin')
    .js('resources/Blocks/fluent-booking-index.js', 'assets/admin')
    .sass('resources/scss/admin.scss', 'admin/admin.css')
    .sass('resources/scss/saas.scss', 'public/saas.css')
    .sass('resources/scss/saas_public.scss', 'public/saas_public.css')
    .sass('resources/scss/saas_admin.scss', 'public/saas_admin.css')
    .copy('resources/images', 'assets/images')
    .copy('resources/public/Payments/stripe-checkout.js', 'assets/public/js/stripe-checkout.js')
    .setPublicPath('assets');
