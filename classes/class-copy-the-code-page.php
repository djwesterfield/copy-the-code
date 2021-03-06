<?php
/**
 * Settings Page
 *
 * @package Copy the Code
 * @since 1.2.0
 */

if ( ! class_exists( 'Copy_The_Code_Page' ) ) :

	/**
	 * Copy_The_Code_Page
	 *
	 * @since 1.2.0
	 */
	class Copy_The_Code_Page {

		/**
		 * Instance
		 *
		 * @since 1.2.0
		 *
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.2.0
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.2.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'plugin_action_links_' . COPY_THE_CODE_BASE, array( $this, 'action_links' ) );
			add_action( 'after_setup_theme', array( $this, 'save_settings' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		/**
		 * Enqueue Assets.
		 *
		 * @version 1.0.0
		 *
		 * @return void
		 */
		function enqueue_assets() {
			wp_enqueue_style( 'copy-the-code', COPY_THE_CODE_URI . 'assets/css/copy-the-code.css', null, COPY_THE_CODE_VER, 'all' );
			wp_enqueue_script( 'copy-the-code', COPY_THE_CODE_URI . 'assets/js/copy-the-code.js', array( 'jquery' ), COPY_THE_CODE_VER, true );
			wp_localize_script(
				'copy-the-code', 'copyTheCode', apply_filters(
					'copy_the_code_localize_vars', array(
						'selector' => 'pre', // Selector in which have the actual `<code>`.
						'settings' => $this->get_page_settings(),
						'string'   => array(
							'title'  => __( 'Copy to Clipboard', 'copy-the-code' ),
							'copy'   => __( 'Copy', 'copy-the-code' ),
							'copied' => __( 'Copied!', 'copy-the-code' ),
						),
					)
				)
			);
		}

		/**
		 * Admin Settings
		 *
		 * @return void
		 */
		function save_settings() {

			if ( isset( $_REQUEST['page'] ) && strpos( $_REQUEST['page'], 'copy-the-code' ) !== false ) {

				// Only admins can save settings.
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}

				// Make sure we have a valid nonce.
				if ( isset( $_REQUEST['copy-the-code'] ) && wp_verify_nonce( $_REQUEST['copy-the-code'], 'copy-the-code-nonce' ) ) {

					// Stored Settings.
					$stored_data = $this->get_page_settings();

					// New settings.
					$new_data = array(
						'selector' => ( isset( $_REQUEST['selector'] ) ) ? $_REQUEST['selector'] : 'pre',
					);

					// Merge settings.
					$data = wp_parse_args( $new_data, $stored_data );

					// Update settings.
					update_option( 'copy-the-code-settings', $data );
				}
			}
		}

		/**
		 * Settings
		 *
		 * @return array Settings.
		 */
		function get_page_settings() {
			$defaults = array(
				'selector' => 'pre',
			);

			$stored = get_option( 'copy-the-code-settings', $defaults );

			return wp_parse_args( $stored, $defaults );
		}


		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		function action_links( $links ) {
			$action_links = array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=copy-the-code' ) . '" aria-label="' . esc_attr__( 'Settings', 'copy-the-code' ) . '">' . esc_html__( 'Settings', 'copy-the-code' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Register menu
		 *
		 * @since 1.2.0
		 * @return void
		 */
		function register_admin_menu() {
			add_submenu_page( 'options-general.php', __( 'Copy to Clipboard', 'copy-the-code' ), __( 'Copy to Clipboard', 'copy-the-code' ), 'manage_options', 'copy-the-code', array( $this, 'options_page' ) );
		}

		/**
		 * Option Page
		 *
		 * @since 1.2.0
		 * @return void
		 */
		function options_page() {
			$data = $this->get_page_settings();
			?>
			<div class="wrap copy-the-code" id="sync-post">
				<h1><?php _e( 'Copy to Clipboard', 'copy-the-code' ); ?></h1>
				<hr>
				<div class="wrap">
					<div id="poststuff">
						<div id="post-body" class="columns-2">
							<div id="post-body-content">

								<form enctype="multipart/form-data" method="post">
									<table class="form-table">
										<tr>
											<th scope="row"><?php _e( 'Selector', 'copy-the-code' ); ?></th>
											<td>
												<fieldset>
													<input type="text" name="selector" class="regular-text" value="<?php echo esc_attr( $data['selector'] ); ?>" />
													<p class="description"><?php _e( 'Set the selector in which you want to copy the content.<br/>Default its &lt;pre&gt; html tag.', 'copy-the-code' ); ?></p>
												</fieldset>
											</td>
										</tr>
									</table>

									<input type="hidden" name="message" value="saved" />
									<?php wp_nonce_field( 'copy-the-code-nonce', 'copy-the-code' ); ?>

									<?php submit_button(); ?>

								</form>

							</div>

							<div class="postbox-container" id="postbox-container-1">
								<div id="side-sortables" style="">
									<div class="postbox">
										<h2 class="hndle"><span><?php _e( 'Getting Started', 'copy-the-code' ); ?></span></h2>
										<div class="inside">
											<p><?php _e( 'Plugin copy the content from the provided selector. Default it enabled for &lt;pre&gt; tag.', 'copy-the-code' ); ?></p>
											<p><?php _e( 'You can change the selector with your own with setting <b>Selector</b>.', 'copy-the-code' ); ?></p>
										</div>
									</div>
									<div class="postbox">
										<h2 class="hndle"><span><?php _e( 'Support', 'copy-the-code' ); ?></span></h2>
										<div class="inside">
											<p><?php _e( 'Do you have any issue with this plugin? Or Do you have any suggessions?', 'copy-the-code' ); ?></p>
											<p><?php _e( 'Please don\'t hesitate to <a href="http://maheshwaghmare.wordpress.com/?p=999" target="_blank">send request »</a>.', 'copy-the-code' ); ?></p>
										</div>
									</div>
									<div class="postbox">
										<h2 class="hndle"><span><?php _e( 'Donate', 'copy-the-code' ); ?></span></h2>
										<div class="inside">
											<p><?php _e( 'Would you like to support the advancement of this plugin?', 'copy-the-code' ); ?></p>
											<a href="https://www.paypal.me/mwaghmare7/" target="_blank" class="button button-primary"><?php _e( 'Donate Now!', 'copy-the-code' ); ?></a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Initialize class object with 'get_instance()' method
	 */
	Copy_The_Code_Page::get_instance();

endif;
