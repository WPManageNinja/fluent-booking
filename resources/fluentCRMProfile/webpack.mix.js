let mix = require('laravel-mix');
var path = require('path');

mix.setPublicPath('../../');

mix.js('fluentcrm.js', 'assets/admin').vue({ version: 2 });
