let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */



// Custom Config
// Note: Dont compile sass here for it will empty the library.
mix.webpackConfig({
     // Since we're using mix, leave this empty
     // entry: {},
     // Expose file as library
     output: {
        // library: ['lib', '[name]']
        library: 'lib'
     }
});

mix.js('resources/js/admin/consolidated/date_range_multiple.js', 'public/js/admin/consolidated/date_range_multiple.min.js')
    .js('resources/js/admin/consolidated/date_range.js', 'public/js/admin/consolidated/date_range.min.js');

mix.styles([
        'public/bower_components/select2/dist/css/select2.min.css',
        'public/bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css',
        'public/bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css',
        'public/bower_components/datatables-responsive/css/dataTables.responsive.css',
        'public/bower_components/bootstrap-multiselect/dist/css/bootstrap-multiselect.css',
        'public/css/admin/consolidated_chart.min.css',
        'resources/css/admin/consolidated_graph.css'
    ], 'public/css/admin/consolidated/date_range_multiple.min.css')
    .styles([
        'public/bower_components/select2/dist/css/select2.min.css',
        'public/bower_components/select2-bootstrap-theme/dist/select2-bootstrap.min.css',
        'public/bower_components/bootstrap-multiselect/dist/css/bootstrap-multiselect.css',
        'public/css/admin/consolidated_chart.min.css',
        'resources/css/admin/consolidated_graph.css'
    ], 'public/css/admin/consolidated/date_range.min.css');
