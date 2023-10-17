let mix = require('laravel-mix');
require('laravel-mix-svelte');

mix.js('resources/public/app.js', 'assets/public/js')
    .svelte({
        dev: true,
        compilerOptions: {
            dev: true
        }
    })
    .options({autoprefixer: false});

mix.js('resources/public/widget.js', 'assets/public/js')
    .svelte({
        dev: true,
        compilerOptions: {
            dev: true
        }
    })
    .options({autoprefixer: false});

mix.js('resources/public/fluentform.js', 'assets/public/js')
    .svelte({
        dev: true,
        compilerOptions: {
            dev: true
        }
    })
    .options({autoprefixer: false});

mix.js('resources/public/ExtendedPhone/phone-field.js', 'assets/public/js')
    .svelte({
        dev: true,
        compilerOptions: {
            dev: true,
        }
    })
    .options({
        autoprefixer: false,
        processCssUrls: false
    });

mix.js('resources/public/public-manage-meeting.js', 'assets/public/js');

mix.disableNotifications();
