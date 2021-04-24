<?php
/*
Plugin Name: Zibal myCRED
Version: 1.0
Description: افزونه درگاه پرداخت زیبال برای افزونه myCred 
Plugin URI: http://zibal.ir
Author: Yahya Kangi
Author URI: https://github.com/YahyaKng
Text Domain: zibal-mycred
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0
 */
function zibal_mycred_load_textdomain() {
    load_plugin_textdomain( 'zibal-mycred', FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'init', 'zibal_mycred_load_textdomain' );

require_once( plugin_dir_path( __FILE__ ) . 'class-mycred-gateway-zibal.php' );
