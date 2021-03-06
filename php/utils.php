<?php
namespace TenUp\SwiftStream\v1_0_0\Utils;

/**
 * Set up any required hooks in the namespace
 */
function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init',                               $n( 'register_scripts' ),           10, 1 );
	add_filter( 'wp_get_attachment_image_attributes', $n( 'get_attachment_placeholder' ), 10, 2 );
}

/**
 * Register our scripts with WordPress
 */
function register_scripts() {
	if ( defined( 'SWIFTSTREAM_PATH' ) ) {
		wp_register_script( 'lazy-loader', SWIFTSTREAM_PATH . 'js/imageLoader.js', array( 'jquery' ), '1.0.0', true );
	}
}

/**
 * Retrieve an image placeholder to represent an attachment.
 *
 * @param int    $attachment_id
 * @param string $size
 * @param bool   $icon
 *
 * @return array|bool
 */
function get_placeholder_image_src( $attachment_id, $size, $icon = false ) {
	if ( false === strpos( $size, '-ph' ) ) {
		$size .= '-ph';
	}

	return wp_get_attachment_image_src( $attachment_id, $size, $icon );
}

/**
 * Filter the array of image attributes to replace the src with its placeholder and relegate the real image to
 * a data attribute for lazy loading.
 *
 * @param array    $attr
 * @param \WP_Post $attachment
 *
 * @return array
 */
function get_attachment_placeholder( $attr, $attachment ) {
	if ( is_admin() ) {
		return $attr;
	}

	// Get the image size
	$class = $attr['class'];
	$size = str_replace( 'attachment-', '', $class );

	// Get the placeholder image
	$placeholder = get_placeholder_image_src( $attachment->ID, $size );

	if ( strpos( $placeholder[0], '-ph.' ) !== false ) {
		$attr['data-lazy'] = $attr['src'];
		$attr['src'] = $placeholder[0];

		// If we're swapping images, we need to set up our scripts
		if ( defined( 'SWIFTSTREAM_PATH' ) ) {
			wp_enqueue_script( 'lazy-loader' );
		}
	}

	return $attr;
}