<?php
 
/*
Plugin Name: Featured Background Image
Plugin URI: 
Description: Creates an additional meta-box for assigning a featured background image to posts and pages
Author: Oscar Ciutat
Version: 1.0.0
Author URI: http://oscarciutat.com/code/
*/


/**
 * Plugin initialization
 */
function fbi_init() {
	load_plugin_textdomain( 'featured-background-image', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'init', 'fbi_init' );


/**
 * Adds a meta box to the post editing screen
 */
function fbi_add_meta_boxes() {
	add_meta_box( 'fbi-background-image', __( 'Featured Background Image', 'featured-background-image' ), 'fbi_meta_callback', array( 'post', 'page' ), 'side' );
}
add_action( 'add_meta_boxes', 'fbi_add_meta_boxes' );


/**
 * Outputs the content of the meta box
 */
function fbi_meta_callback( $post ) {
	wp_nonce_field( basename( __FILE__ ), 'fbi_nonce' );
	$fbi_image = get_post_meta( $post->ID, '_fbi-background-image', true);

?>
	<div id="fbi-thumbnail" class="<?php if ( empty ( $fbi_image ) ) echo 'hide'; ?>">
		<img id="fbi-thumbnail" src="<?php echo $fbi_image; ?>" class="attachment-post-thumbnail" ></a>
	</div>
	<input type="hidden" name="fbi-image" id="fbi-image" value="<?php if ( !empty ( $fbi_image ) ) echo $fbi_image; ?>" />
	<p>
		<a href="#" id="fbi-set-image" class="<?php if ( !empty ( $fbi_image ) ) echo 'hide'; ?>"><?php _e( 'Set featured background image', 'featured-background-image' ); ?></a>
		<a href="#" id="fbi-remove-image" class="<?php if ( empty ( $fbi_image ) ) echo 'hide'; ?>" ><?php _e( 'Remove featured background image', 'featured-background-image' ); ?></a>
	</p>
<?php

}


/**
 * Saves the custom meta input
 */
function fbi_save_post( $post_id ) {
 
	// Checks save status
	$is_autosave = wp_is_post_autosave( $post_id );
	$is_revision = wp_is_post_revision( $post_id );
	$is_valid_nonce = ( isset( $_POST[ 'fbi_nonce' ] ) && wp_verify_nonce( $_POST[ 'fbi_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
	// Exits script depending on save status
	if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
		return;
	}

	// Checks for input and saves if needed
	if( isset( $_POST[ 'fbi-image' ] ) ) {
		update_post_meta( $post_id, '_fbi-background-image', $_POST[ 'fbi-image' ] );
	}

}
add_action( 'save_post', 'fbi_save_post' );


/**
 * Loads admin styles and scripts
 */
function fbi_admin_enqueue_scripts() {
	global $typenow;
	if( $typenow == 'post' || $typenow == 'page' ) {
		
		wp_enqueue_style( 'featured-background-image-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
		
		wp_enqueue_media();
 
		// Registers and enqueues the required javascript.
		wp_register_script( 'featured-background-image', plugin_dir_url( __FILE__ ) . 'featured-background-image.js', array( 'jquery' ) );
		wp_localize_script( 'featured-background-image', 'meta_image',
			array(
				'title' => __( 'Featured Background Image', 'featured-background-image' ),
				'button' => __( 'Set featured background image', 'featured-background-image' ),
			)
		);
        
		wp_enqueue_script( 'featured-background-image' );
	}
}
add_action( 'admin_enqueue_scripts', 'fbi_admin_enqueue_scripts' );


/**
 * Loads frontend styles and scripts
 */
function fbi_enqueue_scripts() {
	global $post;

	wp_enqueue_style( 'featured-background-image-style', plugin_dir_url( __FILE__ ) . 'style.css' );
	
	$fbi_image = get_post_meta( $post->ID, '_fbi-background-image', true);
	if ( !empty ( $fbi_image ) ) {
		$custom_css = '
			body {
				background-image: url("' . $fbi_image . '");
			}
		';
		wp_add_inline_style( 'featured-background-image-style', $custom_css );
	}
	
}
add_action( 'wp_enqueue_scripts', 'fbi_enqueue_scripts' );


?>