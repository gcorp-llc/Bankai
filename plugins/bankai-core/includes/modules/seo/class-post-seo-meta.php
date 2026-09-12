<?php
/**
 * Post Editor SEO Meta Box Handler.
 *
 * @package BankaiCore
 * @author  GCORP LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

/**
 * Class Bankai_Post_SEO_Meta
 */
class Bankai_Post_SEO_Meta {

	/**
	 * Register Meta Box hooks.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_box' ) );
	}

	/**
	 * Add SEO Meta Box to Post and Page editors.
	 */
	public static function add_meta_box() {
		$post_types = array( 'post', 'page' );
		foreach ( $post_types as $pt ) {
			add_meta_box(
				'bankai_seo_meta_box',
				esc_html__( 'تنظیمات سئو Bankai', 'bankai-core' ),
				array( __CLASS__, 'render_meta_box' ),
				$pt,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Render Meta Box fields.
	 *
	 * @param WP_Post $post Current post object.
	 */
	public static function render_meta_box( $post ) {
		wp_nonce_field( 'bankai_seo_meta_nonce_action', 'bankai_seo_meta_nonce' );

		$title    = get_post_meta( $post->ID, '_bankai_seo_title', true );
		$desc     = get_post_meta( $post->ID, '_bankai_seo_description', true );
		$keyword  = get_post_meta( $post->ID, '_bankai_seo_focus_keyword', true );
		?>
		<p>
			<label for="bankai_seo_title"><strong><?php esc_html_e( 'عنوان سئو (SEO Title):', 'bankai-core' ); ?></strong></label><br/>
			<input type="text" id="bankai_seo_title" name="bankai_seo_title" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
		</p>
		<p>
			<label for="bankai_seo_description"><strong><?php esc_html_e( 'توضیحات متا (Meta Description):', 'bankai-core' ); ?></strong></label><br/>
			<textarea id="bankai_seo_description" name="bankai_seo_description" rows="3" class="widefat"><?php echo esc_textarea( $desc ); ?></textarea>
		</p>
		<p>
			<label for="bankai_seo_focus_keyword"><strong><?php esc_html_e( 'کلمه کلیدی اصلی (Focus Keyword):', 'bankai-core' ); ?></strong></label><br/>
			<input type="text" id="bankai_seo_focus_keyword" name="bankai_seo_focus_keyword" value="<?php echo esc_attr( $keyword ); ?>" class="widefat" />
		</p>
		<?php
	}

	/**
	 * Save Meta Box data safely.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save_meta_box( $post_id ) {
		if ( ! isset( $_POST['bankai_seo_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bankai_seo_meta_nonce'] ) ), 'bankai_seo_meta_nonce_action' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['bankai_seo_title'] ) ) {
			update_post_meta( $post_id, '_bankai_seo_title', sanitize_text_field( wp_unslash( $_POST['bankai_seo_title'] ) ) );
		}

		if ( isset( $_POST['bankai_seo_description'] ) ) {
			update_post_meta( $post_id, '_bankai_seo_description', sanitize_textarea_field( wp_unslash( $_POST['bankai_seo_description'] ) ) );
		}

		if ( isset( $_POST['bankai_seo_focus_keyword'] ) ) {
			update_post_meta( $post_id, '_bankai_seo_focus_keyword', sanitize_text_field( wp_unslash( $_POST['bankai_seo_focus_keyword'] ) ) );
		}
	}
}
