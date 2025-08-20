/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

const mix = require('laravel-mix');
const path = require('path');

mix.js('resources/js/app.js', 'public/js')
   .vue({ version: 2 }) // Specify Vue version 2
   .css('resources/css/app.css', 'public/css');

mix.webpackConfig({
    ignoreWarnings: [
        warning => typeof warning.message === 'string' &&
          warning.message.includes('color-adjust to print-color-adjust')
      ],
      
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm.js', // Use the full build of Vue
        },
    },
});