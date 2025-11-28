let mix = require('laravel-mix');

mix.setPublicPath('../../');

mix.js('fluentform.js', 'assets/admin/fluentform.js').vue({ version: 3 });
