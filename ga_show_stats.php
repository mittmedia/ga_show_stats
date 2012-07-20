<?php
/*
Plugin Name: GA show stats
Plugin URI: https://github.com/mittmedia/ga_show_stats
Description: Setup statistic tags for OAS
Version: 1.0.0
Author: Fredrik Sundström
Author URI: https://github.com/fredriksundstrom
License: MIT
*/

/*
Copyright (c) 2012 Fredrik Sundström

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/

require_once( 'wp_mvc/init.php' );

$ga_show_stats_app = new \WpMvc\Application();

$ga_show_stats_app->init( 'GaShowStats', WP_PLUGIN_DIR . '/ga_show_stats' );

// WP: Add pages
add_action( "admin_menu", "ga_show_stats" );
function ga_show_stats()
{
  add_menu_page( 'Statistics', 'Statistics', 'Editor', 'ga_show_stats', 'ga_show_stats_page');
}

function ga_show_stats_page()
{
  global $ga_show_stats_app;

  $ga_show_stats_app->ga_show_stats_controller->index();
}

add_filter('admin_head', 'ga_show_stats_add_scripts_and_styles');
function ga_show_stats_add_scripts_and_styles() {
  if (isset( $_GET['page'] ) && $_GET['page'] == 'ga_show_stats') {
    echo '<script type="text/javascript" src="http://code.highcharts.com/highcharts.js"></script>';
  }
}
