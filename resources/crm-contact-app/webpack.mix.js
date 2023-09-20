let mix = require('laravel-mix');
var path = require('path');

mix.webpackConfig({
    module: {

    },
    output: {
        // publicPath: Mix.isUsing('hmr') ? '/' : '/wp-content/plugins/fluent-pipeline/assets/'
    },
    plugins: [

    ],
    resolve: {
        extensions: ['.js', '.vue', '.json'],
        alias: {
            'ROOT': path.join(__dirname, '../../../fluent-crm/resources/admin/Pieces'),
            '@': path.join(__dirname, '../../../fluent-crm/resources/admin')
        }
    }
});

mix.options({ processCssUrls: false });

mix.js('app.js', 'assets/js/fluent-crm-in-calendar.js').vue({version: 2})
    .setPublicPath('../../assets/')
