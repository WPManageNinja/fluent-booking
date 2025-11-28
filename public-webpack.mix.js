let mix = require('laravel-mix');
require('laravel-mix-svelte');

mix.webpackConfig({
    resolve: {
        extensions: ['.js', '.mjs', '.svelte'],
        alias: {
            './locale': require.resolve('./node_modules/date-picker-svelte/dist/locale.js'),
        },
    },
});

mix.js('resources/public/app.js', 'assets/public/js')
    .svelte({
        dev: true,
        compilerOptions: {
            dev: !mix.inProduction()
        }
    })
    .options({autoprefixer: false});

mix.js('resources/public/widget.js', 'assets/public/js')
    .svelte({
        dev: true,
        compilerOptions: {
            dev: !mix.inProduction()
        }
    })
    .options({autoprefixer: false});

mix.js('resources/public/fluentform.js', 'assets/public/js')
    .svelte({
        dev: true,
        compilerOptions: {
            dev: !mix.inProduction()
        }
    })
    .options({autoprefixer: false});

mix.js('resources/public/fluentform-conversational.js', 'assets/public/js')
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
            dev: !mix.inProduction()
        }
    })
    .options({
        autoprefixer: false,
        processCssUrls: false
    });

mix.js('resources/public/public-manage-meeting.js', 'assets/public/js');

mix.js('resources/public/Team/team_app.js', 'assets/public/js');

mix.js('resources/public/Team/calendar_app.js', 'assets/public/js');

mix.js('resources/public/Booking/bookings.js', 'assets/public/js');

mix.disableNotifications();
