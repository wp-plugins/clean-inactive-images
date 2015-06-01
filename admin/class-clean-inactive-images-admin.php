<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://idx.is
 * @since      1.0.0
 *
 * @package    Clean_Inactive_Images
 * @subpackage Clean_Inactive_Images/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Clean_Inactive_Images
 * @subpackage Clean_Inactive_Images/admin
 * @author     Bruno Rodrigues <bruno@idx.is>
 */
class Clean_Inactive_Images_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * @var string $post_type The post type to search.
	 */
	private $post_type;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Clean_Inactive_Images_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clean_Inactive_Images_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/clean-inactive-images-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Clean_Inactive_Images_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clean_Inactive_Images_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/clean-inactive-images-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_options_page() {
		add_submenu_page(
			'options-general.php',
			'Clean Inactive Images',
			'Clean Inactive Images',
			'administrator',
			'clean-inactive-images', array(
			$this,
			'render_options_page'
		) );
	}

	public function render_options_page() {
		require 'partials/clean-inactive-images-admin-display.php';
	}

	/**
	 * Called via AJAX
	 */
	public function get_uploaded_images() {
		$output         = '';
		$base_directory = wp_upload_dir();
		$base_directory = $base_directory['basedir'];

		$used_images = $this->get_used_images();
		$used_images = $this->get_all_image_thumbs( $used_images, $base_directory );
		$used_images = $this->clean_all_images_path( $used_images );
		$output .= count( $used_images ) . ' used images found <br>';

		$all_images = $this->clean_all_images_path( $this->get_all_images( $base_directory ) );
		$output .= count( $all_images ) . ' images found in uploads folder <br>';

		$unused = array_diff( $all_images, $used_images );

		$output .= $this->delete_images( $unused, $base_directory ) . ' items removed';

		echo $output;
		die;
	}

	private function find_all_dirs( $start ) {
		return array_diff( scandir( $start ), array( '..', '.' ) );

//		$dirStack = [ $start ];
//		while ( $dir = array_shift( $dirStack ) ) {
//			$ar = glob( $dir . '/*', GLOB_ONLYDIR | GLOB_NOSORT );
//			if ( ! $ar ) {
//				continue;
//			}
//
//			$dirStack = array_merge( $dirStack, $ar );
//			foreach ( $ar as $DIR ) {
//				yield $DIR;
//			}
//		}
	}

	/**
	 * @param $base_directory
	 *
	 * @return array
	 */
	private function get_all_images( $base_directory ) {
		$all_images = [ ];
		$years      = $this->find_all_dirs( $base_directory );
		foreach ( $years as $year ) {
			$path   = $base_directory . '/' . $year;
			$months = $this->find_all_dirs( $path );
			foreach ( $months as $month ) {
				$path       = $base_directory . '/' . $year . '/' . $month;
				$all_images = array_merge( $all_images, glob( $path . '/{*.jpg, *.jpeg, *.png}', GLOB_BRACE ) );
			}
		}

		return $all_images;
	}

	private function get_used_images() {
		$attach_ids = [ ];
		$attach_ids = $this->get_used_in_media_gallery();

		$img_paths = [ ];
		foreach ( $attach_ids as $img_id ) {
			$src         = wp_get_attachment_image_src( $img_id, 'full' );
			$img_paths[] = $src[0];
		}

		return $img_paths;
	}

	private function clean_image_path( $path ) {
		return substr( $path, strpos( $path, 'uploads/' ) + strlen( 'uploads/' ) );
	}

	private function clean_all_images_path( $all ) {
		$tmp = $all;
		$all = [ ];
		foreach ( $tmp as $image ) {
			$all[] = $this->clean_image_path( $image );
		}

		return $all;
	}

	/**
	 * @param $image
	 * @param $base_directory
	 *
	 * @return array
	 */
	public function get_image_thumbs( $image, $base_directory ) {
		$image      = $this->clean_image_path( $image );
		$image_name = substr( $image, 0, strrpos( $image, '.' ) );

		$all_images   = glob( $base_directory . '/' . $image_name . '-*.*' );
		$all_images[] = $base_directory . '/' . $image;

		return $all_images;

	}

	private function get_all_image_thumbs( $used_images, $base_directory ) {
		$all_thumbs = [ ];
		foreach ( $used_images as $img ) {
			$thumbnails = $this->get_image_thumbs( $img, $base_directory );
			$all_thumbs = array_merge( $all_thumbs, $thumbnails );
		}

		return $all_thumbs;
	}

	private function get_used_in_media_gallery() {
		global $wpdb;
		$attachment_ids = [ ];
		$selected_post_type = get_option( 'cii_post_type' );
		$post_type      = empty( $selected_post_type ) ? get_option( 'cii_post_type' ) : 'post';
		$sql            = "SELECT post_content	FROM wp_posts where post_type = '" . $post_type . "' and (post_status = 'publish' or post_status = 'draft')";
		$all_content    = $wpdb->get_results( $sql );
		foreach ( $all_content as $content ) {
			if ( ! empty( $content ) ) {
				preg_match( '/\[gallery.*ids=.(.*).\]/', $content->post_content, $images_ids );
				if ( ! empty( $images_ids ) && isset( $images_ids[1] ) ) {
					$images_ids = explode( ",", $images_ids[1] );
					foreach ( $images_ids as $id ) {
						if ( ! empty( $id ) ) {
							if ( ! in_array( $id, $attachment_ids ) ) {
								$attachment_ids[] = $id;
							}
						}
					}
				}
			}
		}

		return $attachment_ids;
	}

	public function register_plugin_settings() {
		register_setting( 'cii_settings_group', 'cii_post_type' );
	}

	/**
	 * @param $unused
	 * @param $base_directory
	 *
	 * @return string
	 */
	private function delete_images( $unused, $base_directory ) {
		foreach ( $unused as $image ) {
			$image = $base_directory . '/' . $image;
			if ( is_file( $image ) ) {
				$img_id = $this->get_image_id( $image );
				wp_delete_attachment( $img_id );
				@unlink( $image );
			}
		}

		return count( $unused );
	}

	private function get_image_id( $image ) {
		global $wpdb;
		$image = $this->clean_image_path( $image );
		$sql   = "select ID from wp_posts where guid like '%{$image}%';";

		return $wpdb->get_var( $sql );
	}

}
