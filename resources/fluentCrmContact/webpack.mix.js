let mix = require('laravel-mix');

mix.js('app.js', 'assets/admin/fluent-crm-in-calendar.js').vue({version: 2})
    .setPublicPath('../../assets/')
