const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | The application ships no JavaScript. Styles are compiled by
 | `node build-css.mjs`, which uses the Tailwind v4 API directly.
 |
 */

mix.postCss('resources/css/app.css', 'public/css', [
    //
]);
