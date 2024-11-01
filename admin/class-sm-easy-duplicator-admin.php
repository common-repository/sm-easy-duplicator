<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://profiles.wordpress.org/shailu25/
 * @since      1.0.0
 *
 * @package    Sm_Easy_Duplicator
 * @subpackage Sm_Easy_Duplicator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sm_Easy_Duplicator
 * @subpackage Sm_Easy_Duplicator/admin
 * @author     Shail Mehta <shailmehta25@gmail.com>
 */
class Sm_Easy_Duplicator_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		 * defined in Sm_Easy_Duplicator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sm_Easy_Duplicator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sm-easy-duplicator-admin.css', array(), $this->version, 'all' );

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
		 * defined in Sm_Easy_Duplicator_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sm_Easy_Duplicator_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sm-easy-duplicator-admin.js', array( 'jquery' ), $this->version, false );

	}
    
    /* Define Constant */
    const ACTION = 'sm_easy_duplicator';

    /**
     * Check if current user can clone
     *
     * @return bool
     */
    public static function can_clone() {
        return current_user_can('edit_posts');
    }

    /**
     * Add clone link in row actions
     *
     * @param array $actions
     * @param \WP_Post $post
     * @return array
     */
  public static function sm_easy_add_row_actions($actions, $post) {
		if (self::can_clone()) {
			$nonce = wp_create_nonce(self::ACTION);
			$url = esc_url(self::get_url($post->ID));
			// translators: %s is the placeholder for the post title.
			$title = sprintf(esc_attr__('Clone - %s', 'sm-easy-duplicator'), esc_attr($post->post_title));

			$actions[self::ACTION] = sprintf(
				'<a href="%1$s" title="%2$s" class="sm-easy-duplicator-clone-link" data-nonce="%4$s"><span class="screen-reader-text">%2$s</span>%3$s</a>',
				$url,
				$title,
				esc_html__('Clone', 'sm-easy-duplicator'),
				$nonce
			);
		}

		return $actions;
}

    /**
     * Duplicate requested post
     */
	public static function sm_easy_duplicate_thing() {
		if ( ! self::can_clone() ) {
			return;
		}

		// Verify the nonce
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), self::ACTION ) ) {
			wp_die( 'Security check failed' );
		}

		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

		if ( is_null( ( $post = get_post( $post_id ) ) ) ) {
			return;
		}

		$post = sanitize_post( $post, 'db' );
		$duplicated_post_id = self::duplicate_post( $post );

		if ( ! is_wp_error( $duplicated_post_id ) ) {
			self::sm_easy_duplicate_taxonomies( $post, $duplicated_post_id );
			self::sm_easy_duplicate_meta_entries( $post, $duplicated_post_id );

			// Set the status of the cloned post to draft
			wp_update_post( [ 'ID' => $duplicated_post_id, 'post_status' => 'draft' ] );
		}

		// Redirect back to the post list
		$post_type = $post->post_type;
		$redirect  = admin_url( "edit.php?post_type=$post_type" );
		wp_safe_redirect( $redirect );
		die();
	}
   
    /**
     * Get clone URL with required query params
     *
     * @param $post_id
     * @return string
     */
    public static function get_url($post_id) {
       return esc_url(
		wp_nonce_url(
			add_query_arg(
				[
					'action' => self::ACTION,
					'post_id' => $post_id,
				],
				admin_url('admin.php')
			),
			self::ACTION
		)
);
    }

    /**
     * Clone post
     */
    protected static function duplicate_post($post) {
       global $wpdb;

    $current_user = wp_get_current_user();
    $table_name = $wpdb->prefix . 'posts';

    $duplicated_post_args = [
        'post_status' => 'draft',
        'post_type' => $post->post_type,
        'post_author' => $current_user->ID,
        'post_parent' => $post->post_parent,
        'post_excerpt' => $post->post_excerpt,
        'post_content' => $post->post_content,
        'post_password' => $post->post_password,
        'comment_status' => $post->comment_status,
        // translators: %s is the placeholder for the post title.
        'post_title' => sprintf(__('Clone - %s', 'sm-easy-duplicator'), $post->post_title),
    ];

    $wpdb->insert($table_name, $duplicated_post_args);

    return $wpdb->insert_id;
    }

    /**
     * Copy post taxonomies to cloned post
     *
     * @param $post
     * @param $duplicated_post_id
     */
    protected static function sm_easy_duplicate_taxonomies($post, $duplicated_post_id) {
        global $wpdb;

        $taxonomies = get_object_taxonomies($post->post_type);
        if (!empty($taxonomies) && is_array($taxonomies)) {
            foreach ($taxonomies as $taxonomy) {
                $terms = wp_get_object_terms($post->ID, $taxonomy, ['fields' => 'slugs']);
                if (!empty($terms)) {
                    $term_taxonomy_table = $wpdb->prefix . 'term_taxonomy';
                    $term_relationships_table = $wpdb->prefix . 'term_relationships';

                    foreach ($terms as $term_slug) {
                   $term = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT term_id FROM %s WHERE taxonomy = %s AND term_id IN (SELECT term_taxonomy_id FROM %s WHERE object_id = %d)",
						$term_taxonomy_table,
						$taxonomy,
						$term_relationships_table,
						$post->ID
					));
                        if ($term) {
                            wp_set_object_terms($duplicated_post_id, intval($term->term_id), $taxonomy, true);
                        }
                    }
                }
            }
        }
    }

    /**
     * Copy post meta entries to cloned post
     *
     * @param $post
     * @param $duplicated_post_id
     */
    protected static function sm_easy_duplicate_meta_entries($post, $duplicated_post_id) {
        global $wpdb;

        $entries = $wpdb->get_results(
            $wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", $post->ID)
        );

        if (is_array($entries)) {
            foreach ($entries as $entry) {
                $meta_key = $entry->meta_key;
                $meta_value = $entry->meta_value;

                // Use prepare to properly sanitize and escape data
                $wpdb->insert(
                    $wpdb->prefix . 'postmeta',
                    [
                        'post_id' => $duplicated_post_id,
                        'meta_key' => $meta_key,
                        'meta_value' => $meta_value,
                    ],
                    ['%d', '%s', '%s']
                );
            }
        }
    }

}