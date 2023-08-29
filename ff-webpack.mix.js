let mix = require('laravel-mix');
const path = require("path");

mix.alias({
    '@': path.join(__dirname, '../fluentform/resources/assets/admin'),
});

console.log(path.join(__dirname, '../fluent-hr/node_modules'));

mix.webpackConfig({
    resolve: {
        modules: [
            path.resolve(path.join(__dirname, '../fluent-hr/node_modules'))
        ]
    }
});

mix.js('resources/FluentFormEditor/fluentform.js', 'assets/admin')
    .vue({version: 2})
    .setPublicPath('assets');
