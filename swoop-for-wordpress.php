<?php
/*
Plugin Name: Swoop for Wordpress
Description: Allows you and your users to log in without a password!
Version: 2.0
Author: Swoop
Author URI: https://swoopnow.com
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

/*
This file is part of Swoop for Wordpress.

Swoop for Wordpress is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
any later version.

Swoop for Wordpress is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
*/

include_once( plugin_dir_path( __FILE__ ) . '/includes/swoop_core.php' );
include_once( plugin_dir_path( __FILE__ ) . '/includes/options.php' );

$swoop = new SwoopCore(__FILE__);
