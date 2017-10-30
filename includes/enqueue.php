<?php

if ( !defined( 'ABSPATH' ) ) exit;

function rsbp_enqueue_css() {
	wp_enqueue_style('rsbp', RSBP_URL . '/assets/rsbp.css', array(), RSBP_VERSION );
}
add_action( 'wp_enqueue_scripts', 'rsbp_enqueue_css' );