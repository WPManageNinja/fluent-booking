let mix = require('laravel-mix');
require('laravel-mix-svelte');

mix.js('resources/public/app.js', 'assets/public/js')
    .svelte({
        dev: true
    })
    .options({ autoprefixer: false });

mix.disableNotifications();
