<?php
/**
 * Plugin Name: Rather Simple Featured Background Image
 * Plugin URI:
 * Update URI: false
 * Version: 1.0
 * Requires at least: 5.3
 * Requires PHP: 7.4
 * Author: Oscar Ciutat
 * Text Domain: rather-simple-featured-background-image
 * Description: Creates an additional meta-box for assigning a featured background image to posts and pages
 * License: GPLv2 or later
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package rather_simple_featured_background_image
 */

/**
 * Core class used to implement the plugin.
 */
class Rather_Simple_Featured_Background_Image {

	/**
	 * Plugin instance
	 *
	 * @var object $instance
	 */
	protected static $instance = null;

	/**
	 * Access this plugin’s working instance
	 *
	 * @return object of this class
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Used for regular plugin work.
	 *
	 * @return  void
	 */
	public function plugin_setup() {

		$this->includes();

		add_action( 'init', array( $this, 'load_language' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
	}

	/**
	 * Constructor. Intentionally left empty and public.
	 *
	 * @see plugin_setup()
	 */
	public function __construct() {}

	/**
	 * Includes required core files used in admin and on the frontend.
	 */
	protected function includes() {}

	/**
	 * Load language
	 */
	public function load_language() {
		load_plugin_textdomain( 'rather-simple-featured-background-image', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Adds a meta box to the post editing screen
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'fbi-background-image',
			__( 'Featured Background Image', 'rather-simple-featured-background-image' ),
			array( $this, 'background_image_meta_box_callback' ),
			'',
			'side',
			'low'
		);
	}

	/**
	 * Outputs the content of the meta box
	 *
	 * @param WP_Post $post  The post object.
	 */
	public function background_image_meta_box_callback( $post ) {
		$fbi_image  = get_post_meta( $post->ID, '_fbi_image', true );
		$repeat     = get_post_meta( $post->ID, '_fbi_repeat', true );
		$position_x = get_post_meta( $post->ID, '_fbi_position_x', true );
		$position_y = get_post_meta( $post->ID, '_fbi_position_y', true );
		$attachment = get_post_meta( $post->ID, '_fbi_attachment', true );
		$size       = get_post_meta( $post->ID, '_fbi_size', true );

		$repeat     = ! empty( $repeat ) ? $repeat : 'no-repeat';
		$position_x = ! empty( $position_x ) ? $position_x : '50';
		$position_y = ! empty( $position_y ) ? $position_y : '50';
		$attachment = ! empty( $attachment ) ? $attachment : 'scroll';
		$size       = ! empty( $size ) ? $size : 'cover';

		wp_nonce_field( basename( __FILE__ ), 'fbi_nonce' );
		?>
		<div id="fbi-thumbnail" class="
		<?php
		if ( empty( $fbi_image ) ) {
			echo 'hide';
		}
		?>
		">
			<a href="#" id="fbi-update-image"><img src="<?php echo esc_url( $fbi_image ); ?>" class="thumbnail" /></a>
		</div>
		<input type="hidden" name="fbi-image" id="fbi-image" value="
		<?php
		if ( ! empty( $fbi_image ) ) {
			echo esc_attr( $fbi_image );
		}
		?>
		" />
		<p>
			<a href="#" id="fbi-set-image" class="
			<?php
			if ( ! empty( $fbi_image ) ) {
				echo 'hide';
			}
			?>
			"><?php _e( 'Set featured background image', 'rather-simple-featured-background-image' ); ?></a>
			<p id="fbi-image-desc" class="
			<?php
			if ( empty( $fbi_image ) ) {
				echo 'hide';
			}
			?>
			"><?php _e( 'Click the image to edit or update' ); ?></p>
			<a href="#" id="fbi-remove-image"  class="
			<?php
			if ( empty( $fbi_image ) ) {
				echo 'hide';
			}
			?>
			"><?php _e( 'Remove featured background image', 'rather-simple-featured-background-image' ); ?></a>
		</p>

		<?php
		/* Set up an array of allowed values for the repeat option. */
		$repeat_options = array(
			'no-repeat' => __( 'No Repeat', 'rather-simple-featured-background-image' ),
			'repeat'    => __( 'Repeat', 'rather-simple-featured-background-image' ),
			'repeat-x'  => __( 'Repeat Horizontally', 'rather-simple-featured-background-image' ),
			'repeat-y'  => __( 'Repeat Vertically', 'rather-simple-featured-background-image' ),
		);
		/* Set up an array of allowed values for the position-x option. */
		$position_x_options = array(
			'0'   => __( '0% (Left)', 'rather-simple-featured-background-image' ),
			'25'  => __( '25%', 'rather-simple-featured-background-image' ),
			'50'  => __( '50% (Center)', 'rather-simple-featured-background-image' ),
			'75'  => __( '75%', 'rather-simple-featured-background-image' ),
			'100' => __( '100% (Right)', 'rather-simple-featured-background-image' ),
		);
		/* Set up an array of allowed values for the position-x option. */
		$position_y_options = array(
			'0'   => __( '0% (Top)', 'rather-simple-featured-background-image' ),
			'25'  => __( '25%', 'rather-simple-featured-background-image' ),
			'50'  => __( '50% (Center)', 'rather-simple-featured-background-image' ),
			'75'  => __( '75%', 'rather-simple-featured-background-image' ),
			'100' => __( '100% (Bottom)', 'rather-simple-featured-background-image' ),
		);
		/* Set up an array of allowed values for the attachment option. */
		$attachment_options = array(
			'scroll' => __( 'Scroll', 'rather-simple-featured-background-image' ),
			'fixed'  => __( 'Fixed', 'rather-simple-featured-background-image' ),
			'local'  => __( 'Local', 'rather-simple-featured-background-image' ),
		);
		/* Set up an array of allowed values for the size option. */
		$size_options = array(
			'auto'    => __( 'Auto', 'rather-simple-featured-background-image' ),
			'cover'   => __( 'Cover', 'rather-simple-featured-background-image' ),
			'contain' => __( 'Contain', 'rather-simple-featured-background-image' ),
		);
		?>

		<div id="fbi-background-options">

			<p>
				<label for="fbi-repeat"><?php _e( 'Repetition', 'rather-simple-featured-background-image' ); ?></label>
				<select class="widefat" name="fbi-repeat" id="fbi-repeat">
				<?php foreach ( $repeat_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $repeat, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

			<p>
				<label for="fbi-position-x"><?php _e( 'Horizontal Position', 'rather-simple-featured-background-image' ); ?></label>
				<select class="widefat" name="fbi-position-x" id="fbi-position-x">
				<?php foreach ( $position_x_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $position_x, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

			<p>
				<label for="fbi-position-y"><?php _e( 'Vertical Position', 'rather-simple-featured-background-image' ); ?></label>
				<select class="widefat" name="fbi-position-y" id="fbi-position-y">
				<?php foreach ( $position_y_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $position_y, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

			<p>
				<label for="fbi-attachment"><?php _e( 'Attachment', 'rather-simple-featured-background-image' ); ?></label>
				<select class="widefat" name="fbi-attachment" id="fbi-attachment">
				<?php foreach ( $attachment_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $attachment, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

			<p>
				<label for="fbi-size"><?php _e( 'Size', 'rather-simple-featured-background-image' ); ?></label>
				<select class="widefat" name="fbi-size" id="fbi-size">
				<?php foreach ( $size_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $size, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

		</div>

		<?php
	}

	/**
	 * Saves the custom meta input
	 *
	 * @param integer $post_id  The post ID.
	 * @param WP_Post $post     The post object.
	 * @param boolean $update   Whether this is an existing post being updated.
	 */
	public function save_post( $post_id, $post, $update ) {

		// Checks save status.
		$is_autosave    = wp_is_post_autosave( $post_id );
		$is_revision    = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( ! isset( $_POST['fbi_nonce'] ) || wp_verify_nonce( wp_unslash( $_POST['fbi_nonce'] ), basename( __FILE__ ) ) ) ? 'true' : 'false';

		// Exits script depending on save status.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		if ( $update ) {

			// Checks for input and saves if needed.
			if ( isset( $_POST['fbi-image'] ) ) {
				update_post_meta( $post_id, '_fbi_image', wp_unslash( $_POST['fbi-image'] ) );
			}

			$allowed_repeat     = array( 'no-repeat', 'repeat', 'repeat-x', 'repeat-y' );
			$allowed_position_x = array( '0', '25', '50', '75', '100' );
			$allowed_position_y = array( '0', '25', '50', '75', '100' );
			$allowed_attachment = array( 'scroll', 'fixed', 'local' );
			$allowed_size       = array( 'auto', 'cover', 'contain' );

			/* Make sure the values have been white-listed. Otherwise, set an empty string. */
			$repeat     = in_array( $_POST['fbi-repeat'], $allowed_repeat, true ) ? wp_unslash( $_POST['fbi-repeat'] ) : '';
			$position_x = in_array( $_POST['fbi-position-x'], $allowed_position_x, true ) ? wp_unslash( $_POST['fbi-position-x'] ) : '';
			$position_y = in_array( $_POST['fbi-position-y'], $allowed_position_y, true ) ? wp_unslash( $_POST['fbi-position-y'] ) : '';
			$attachment = in_array( $_POST['fbi-attachment'], $allowed_attachment, true ) ? wp_unslash( $_POST['fbi-attachment'] ) : '';
			$size       = in_array( $_POST['fbi-size'], $allowed_size, true ) ? wp_unslash( $_POST['fbi-size'] ) : '';

			/* Set up an array of meta keys and values. */
			$meta = array(
				'_fbi_repeat'     => $repeat,
				'_fbi_position_x' => $position_x,
				'_fbi_position_y' => $position_y,
				'_fbi_attachment' => $attachment,
				'_fbi_size'       => $size,
			);

			/* Loop through the meta array and add, update, or delete the post metadata. */
			foreach ( $meta as $meta_key => $new_meta_value ) {
				$meta_value = get_post_meta( $post_id, $meta_key, true );
				if ( $new_meta_value && '' === $meta_value ) {
					add_post_meta( $post_id, $meta_key, $new_meta_value, true );
				} elseif ( $new_meta_value && $new_meta_value !== $meta_value ) {
					update_post_meta( $post_id, $meta_key, $new_meta_value );
				} elseif ( '' === $new_meta_value && $meta_value ) {
					delete_post_meta( $post_id, $meta_key, $meta_value );
				}
			}
		}
	}

	/**
	 * Loads admin styles and scripts
	 */
	public function admin_enqueue_scripts() {
		global $typenow;
		// if( $typenow == 'post' || $typenow == 'page' ) {

			wp_enqueue_style(
				'rather-simple-featured-background-image-admin',
				plugin_dir_url( __FILE__ ) . 'assets/css/admin.css',
				array(),
				filemtime( plugin_dir_path( __FILE__ ) . '/assets/css/admin.css' )
			);

			wp_enqueue_media();

			// Registers and enqueues the required javascript.
			wp_register_script(
				'rather-simple-featured-background-image',
				plugin_dir_url( __FILE__ ) . 'assets/js/backend.js',
				array(),
				filemtime( plugin_dir_path( __FILE__ ) . '/assets/js/backend.js' ),
				true
			);
			wp_localize_script(
				'rather-simple-featured-background-image',
				'meta_image',
				array(
					'title'  => __( 'Featured Background Image', 'rather-simple-featured-background-image' ),
					'button' => __( 'Set featured background image', 'rather-simple-featured-background-image' ),
				)
			);

			wp_enqueue_script( 'rather-simple-featured-background-image' );
		// }
	}

	/**
	 * Loads frontend styles and scripts
	 */
	public function enqueue_scripts() {
		global $post;

		if ( is_singular() ) {

			wp_enqueue_style(
				'rather-simple-featured-background-image',
				plugin_dir_url( __FILE__ ) . 'style.css',
				array(),
				filemtime( plugin_dir_path( __FILE__ ) . '/style.css' )
			);

			$fbi_image  = get_post_meta( $post->ID, '_fbi_image', true );
			$repeat     = get_post_meta( $post->ID, '_fbi_repeat', true );
			$position_x = get_post_meta( $post->ID, '_fbi_position_x', true );
			$position_y = get_post_meta( $post->ID, '_fbi_position_y', true );
			$attachment = get_post_meta( $post->ID, '_fbi_attachment', true );
			$size       = get_post_meta( $post->ID, '_fbi_size', true );

			$repeat     = ! empty( $repeat ) ? $repeat : 'no-repeat';
			$position_x = ! empty( $position_x ) ? $position_x : '50';
			$position_y = ! empty( $position_y ) ? $position_y : '50';
			$attachment = ! empty( $attachment ) ? $attachment : 'scroll';
			$size       = ! empty( $size ) ? $size : 'cover';

			$selector = apply_filters( 'fbi_selector', 'body' );

			if ( ! empty( $fbi_image ) ) {
				$custom_css = wp_strip_all_tags( $selector ) . ' {
                        background-image: url("' . $fbi_image . '");
                        background-repeat: ' . $repeat . ';
                        background-position: ' . $position_x . '% ' . $position_y . '%;
                        background-attachment: ' . $attachment . ';
                        background-size: ' . $size . ';
                    }
                ';
				wp_add_inline_style( 'rather-simple-featured-background-image', $custom_css );
			}
		}
	}
}

add_action( 'plugins_loaded', array( Rather_Simple_Featured_Background_Image::get_instance(), 'plugin_setup' ) );
