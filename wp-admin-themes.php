<?php
/**
 * Plugin Name:       WP Admin Themes
 * Plugin URI:        https://example.com/wp-admin-themes
 * Description:       Lets administrators switch between Default, Classic (WP 6.9.x), Enhanced (a refined layer on top of WP 7.x) and Modern admin themes from Settings → Admin Themes.
 * Version:           0.0.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Developer
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-admin-themes
 * Domain Path:       /languages
 *
 * @package WpAdminThemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WP_ADMIN_THEMES_VERSION', '0.0.1' );
define( 'WP_ADMIN_THEMES_FILE', __FILE__ );
define( 'WP_ADMIN_THEMES_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_ADMIN_THEMES_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_ADMIN_THEMES_SLUG', 'wp-admin-themes' );
define( 'WP_ADMIN_THEMES_OPTION_THEME', 'wp_admin_theme' );
define( 'WP_ADMIN_THEMES_OPTION_COLOR', 'wp_admin_primary_color' );

final class WPAT_Plugin {

	private $themes = array();
	private $current_theme = 'default';
	private $primary_color = '#2271b1';
	private $preset_colors = array();

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	private function __construct() {}

	private function init() {
		add_action( 'init', array( $this, 'load_textdomain' ), 5 );
		add_action( 'init', array( $this, 'register_theme_registry' ), 6 );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_theme_styles' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, 'dequeue_core_styles' ), 999 );
		add_filter( 'admin_body_class', array( $this, 'add_body_class' ) );

		add_action( 'wp_ajax_wpat_preview_theme', array( $this, 'ajax_preview_theme' ) );
		add_action( 'wp_ajax_wpat_reset_settings', array( $this, 'ajax_reset_settings' ) );

		$this->load_options();
	}

	public function register_theme_registry() {
		$this->init_themes();
		$this->init_preset_colors();
	}

	public function init_themes() {
		$this->themes = array(
			'default'  => array(
				'name'        => __( 'Default', 'wp-admin-themes' ),
				/* translators: %s: WordPress */
				'description' => sprintf( __( 'No customization applied. Uses the %s admin styles supplied by your WordPress installation.', 'wp-admin-themes' ), 'WordPress' ),
				'icon'        => 'dashicons-wordpress',
				'features'    => array(
					__( 'No styles dequeued by this plugin', 'wp-admin-themes' ),
					__( 'Default WordPress admin experience', 'wp-admin-themes' ),
					__( 'Respect the user color scheme', 'wp-admin-themes' ),
				),
			),
			'classic'  => array(
				'name'        => __( 'Classic (6.9.5)', 'wp-admin-themes' ),
				'description' => __( 'Faithful classic WordPress admin experience loaded from the bundled 6.9.5 stylesheets.', 'wp-admin-themes' ),
				'icon'        => 'dashicons-admin-appearance',
				'features'    => array(
					__( 'Bundled WP 6.9.5 stylesheets', 'wp-admin-themes' ),
					__( 'Modern 7.x core styles fully dequeued', 'wp-admin-themes' ),
					__( 'Classic dark sidebar', 'wp-admin-themes' ),
					__( 'Familiar user interface', 'wp-admin-themes' ),
				),
			),
			'enhanced' => array(
				'name'        => __( 'Enhanced (7.0.2)', 'wp-admin-themes' ),
				'description' => __( 'Refined, enhanced version of the new WordPress theme. Adds tasteful UX improvements while preserving the 7.0.2 visual language.', 'wp-admin-themes' ),
				'icon'        => 'dashicons-star-filled',
				'features'    => array(
					__( 'Layers on top of WP 7.x core styles', 'wp-admin-themes' ),
					__( 'Smoother transitions (0.15s)', 'wp-admin-themes' ),
					__( 'Sharper, more visible focus rings', 'wp-admin-themes' ),
					__( 'Refined hover states', 'wp-admin-themes' ),
					__( 'Improved button + menu interactions', 'wp-admin-themes' ),
				),
			),
			'modern'   => array(
				'name'        => __( 'Modern', 'wp-admin-themes' ),
				'description' => __( 'UX-focused design with a light sidebar, refined spacing, and a customizable primary color.', 'wp-admin-themes' ),
				'icon'        => 'dashicons-art',
				'features'    => array(
					__( 'Light sidebar that blends with content', 'wp-admin-themes' ),
					__( 'Refined visual hierarchy', 'wp-admin-themes' ),
					__( 'Customizable primary color', 'wp-admin-themes' ),
					__( 'Subtle shadows and modern cards', 'wp-admin-themes' ),
					__( 'Better focus states', 'wp-admin-themes' ),
				),
			),
		);
	}

	public function init_preset_colors() {
		$this->preset_colors = array(
			'#2271b1' => __( 'WordPress Blue', 'wp-admin-themes' ),
			'#007017' => __( 'Forest Green', 'wp-admin-themes' ),
			'#7c5bd4' => __( 'Royal Purple', 'wp-admin-themes' ),
			'#d63638' => __( 'Coral Red', 'wp-admin-themes' ),
			'#dba617' => __( 'Golden', 'wp-admin-themes' ),
			'#00a32a' => __( 'Bright Green', 'wp-admin-themes' ),
			'#1d2327' => __( 'Dark Slate', 'wp-admin-themes' ),
			'#646970' => __( 'Slate Gray', 'wp-admin-themes' ),
			'#e35b48' => __( 'Sunset Orange', 'wp-admin-themes' ),
			'#0969da' => __( 'Ocean Blue', 'wp-admin-themes' ),
			'#8250df' => __( 'Lavender', 'wp-admin-themes' ),
			'#cf222e' => __( 'Cherry Red', 'wp-admin-themes' ),
		);
	}

	private function load_options() {
		$this->current_theme = get_option( WP_ADMIN_THEMES_OPTION_THEME, 'default' );
		$this->primary_color = get_option( WP_ADMIN_THEMES_OPTION_COLOR, '#2271b1' );
	}

	public function load_textdomain() {
		load_plugin_textdomain(
			'wp-admin-themes',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	}

	public function add_admin_menu() {
		add_options_page(
			__( 'WP Admin Themes', 'wp-admin-themes' ),
			__( 'Admin Themes', 'wp-admin-themes' ),
			'manage_options',
			WP_ADMIN_THEMES_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'wp_admin_themes_group',
			WP_ADMIN_THEMES_OPTION_THEME,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_theme' ),
				'default'           => 'default',
			)
		);

		register_setting(
			'wp_admin_themes_group',
			WP_ADMIN_THEMES_OPTION_COLOR,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_primary_color' ),
				'default'           => '#2271b1',
			)
		);
	}

	public function sanitize_theme( $value ) {
		$value   = is_string( $value ) ? sanitize_key( $value ) : 'default';
		$allowed = array_keys( $this->themes );
		return in_array( $value, $allowed, true ) ? $value : 'default';
	}

	public function sanitize_primary_color( $value ) {
		return sanitize_hex_color( $value ) ?: '#2271b1';
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_' . WP_ADMIN_THEMES_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style(
			'wpat-settings',
			WP_ADMIN_THEMES_URL . 'css/settings.css',
			array(),
			WP_ADMIN_THEMES_VERSION
		);

		wp_enqueue_script(
			'wpat-settings',
			WP_ADMIN_THEMES_URL . 'js/settings.js',
			array( 'jquery', 'jquery-ui-tooltip', 'wp-color-picker' ),
			WP_ADMIN_THEMES_VERSION,
			true
		);

		wp_localize_script(
			'wpat-settings',
			'wpatData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wp-admin-themes-nonce' ),
				'restUrl' => esc_url_raw( rest_url() ),
				'i18n'    => array(
					'saving'       => __( 'Saving…', 'wp-admin-themes' ),
					'save'         => __( 'Save Changes', 'wp-admin-themes' ),
					'resetConfirm' => __( 'Reset all settings to defaults?', 'wp-admin-themes' ),
				),
			)
		);
	}

	public function enqueue_theme_styles() {
		if ( ! is_admin() ) {
			return;
		}

		if ( 'classic' === $this->current_theme ) {
			$this->enqueue_classic_styles();
			return;
		}

		if ( 'enhanced' === $this->current_theme ) {
			$this->enqueue_enhanced_styles();
			return;
		}

		if ( 'modern' === $this->current_theme ) {
			$this->enqueue_modern_styles();
			return;
		}
	}

	public function dequeue_core_styles() {
		if ( ! is_admin() ) {
			return;
		}

		if ( 'classic' !== $this->current_theme ) {
			return;
		}

		$handles = array(
			'wp-admin',
			'common',
			'forms',
			'admin-menu',
			'list-tables',
			'dashboard',
			'edit',
			'revisions',
			'media',
			'themes',
			'about',
			'nav-menus',
			'widgets',
			'site-icon',
			'l10n',
			'site-health',
			'wp-color-picker',
			'customize-controls',
			'customize-widgets',
			'customize-nav-menus',
			'press-this',
			'buttons',
			'wp-auth-check',
			'wp-pointer',
		);

		foreach ( $handles as $handle ) {
			wp_dequeue_style( $handle );
		}
	}

	private function enqueue_classic_styles() {
		$base   = WP_ADMIN_THEMES_URL . 'themes/classic/';
		$rtl    = is_rtl();
		$suffix = $rtl ? '-rtl' : '';

		$bundles = array(
			'wp-admin-classic'    => 'wp-admin' . $suffix . '.css',
			'common-classic'      => 'common' . $suffix . '.css',
			'forms-classic'       => 'forms' . $suffix . '.css',
			'admin-menu-classic'  => 'admin-menu' . $suffix . '.css',
			'dashboard-classic'   => 'dashboard.css',
			'list-tables-classic' => 'list-tables' . $suffix . '.css',
			'edit-classic'        => 'edit.css',
			'revisions-classic'   => 'revisions' . $suffix . '.css',
			'media-classic'       => 'media.css',
			'themes-classic'      => 'themes.css',
			'widgets-classic'     => 'widgets.css',
			'nav-menus-classic'   => 'nav-menus.css',
			'about-classic'       => 'about.css',
			'site-icon-classic'   => 'site-icon' . $suffix . '.css',
			'l10n-classic'        => 'l10n.css',
			'site-health-classic' => 'site-health.css',
			'colors-classic'      => 'colors-blue' . $suffix . '.css',
		);

		$deps = array();
		foreach ( $bundles as $handle => $file ) {
			if ( ! file_exists( WP_ADMIN_THEMES_PATH . 'themes/classic/' . $file ) ) {
				continue;
			}
			wp_enqueue_style( $handle, $base . $file, $deps, WP_ADMIN_THEMES_VERSION );
			$deps[] = $handle;
		}
	}

	private function enqueue_enhanced_styles() {
		$base = WP_ADMIN_THEMES_URL . 'themes/enhanced/';

		wp_enqueue_style(
			'wpat-enhanced',
			$base . 'enhance.css',
			array( 'wp-admin', 'common', 'forms', 'admin-menu', 'list-tables', 'dashboard', 'edit', 'media', 'themes', 'widgets', 'nav-menus', 'about', 'site-health' ),
			WP_ADMIN_THEMES_VERSION
		);
	}

	private function enqueue_modern_styles() {
		$path = WP_ADMIN_THEMES_PATH . 'themes/modern/modern.css';
		if ( ! file_exists( $path ) ) {
			return;
		}

		wp_enqueue_style(
			'wp-admin-theme-modern',
			WP_ADMIN_THEMES_URL . 'themes/modern/modern.css',
			array( 'wp-admin', 'common', 'forms', 'admin-menu', 'list-tables', 'dashboard', 'edit', 'media', 'themes', 'widgets', 'nav-menus', 'about', 'site-health' ),
			WP_ADMIN_THEMES_VERSION
		);

		$custom = $this->generate_custom_css();
		wp_add_inline_style( 'wp-admin-theme-modern', $custom );
	}

	private function generate_custom_css() {
		$color = $this->primary_color;
		$hover = $this->adjust_color( $color, -15 );
		$rgb   = $this->hex_to_rgb_array( $color );

		return sprintf(
			':root{--wp-admin-theme-primary:%1$s;--wp-admin-theme-color:%1$s;--wp-admin-theme-color-rgb:%2$d,%3$d,%4$d;--wp-admin-theme-color-hover:%5$s;--wp-admin-theme-color-light:%6$s;}',
			esc_attr( $color ),
			(int) $rgb['r'],
			(int) $rgb['g'],
			(int) $rgb['b'],
			esc_attr( $hover ),
			esc_attr( $this->adjust_color( $color, 25 ) )
		);
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wp-admin-themes' ) );
		}

		$current_theme = $this->current_theme;
		$current_color = $this->primary_color;
		$saved         = isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'];
		$theme         = isset( $this->themes[ $current_theme ] ) ? $this->themes[ $current_theme ] : $this->themes['default'];
		?>
		<div class="wrap wpat-settings-wrapper" data-theme="<?php echo esc_attr( $current_theme ); ?>">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Customize the look of the WordPress admin area. Settings apply only to the current user; reset to Default to use core styles without any overrides.', 'wp-admin-themes' ); ?>
			</p>

			<?php if ( $saved ) : ?>
				<div class="notice notice-success is-dismissible wpat-notice">
					<p><?php esc_html_e( 'Settings saved.', 'wp-admin-themes' ); ?></p>
				</div>
			<?php endif; ?>

			<?php settings_errors( 'wp_admin_themes_group' ); ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>" id="wpat-settings-form">
				<?php
				wp_nonce_field( 'wp_admin_themes_action', 'wp_admin_themes_nonce' );
				settings_fields( 'wp_admin_themes_group' );
				?>

				<div class="wpat-settings-grid">
					<div class="wpat-main">
						<fieldset class="wpat-card">
							<legend class="wpat-card-header">
								<span class="dashicons dashicons-layout" aria-hidden="true"></span>
								<?php esc_html_e( 'Select Theme', 'wp-admin-themes' ); ?>
							</legend>
							<div class="wpat-card-body">
								<div class="wpat-theme-options" role="radiogroup" aria-label="<?php esc_attr_e( 'Admin theme', 'wp-admin-themes' ); ?>">
									<?php foreach ( $this->themes as $slug => $definition ) : ?>
										<div class="wpat-theme-option">
											<input type="radio"
												name="<?php echo esc_attr( WP_ADMIN_THEMES_OPTION_THEME ); ?>"
												id="theme-<?php echo esc_attr( $slug ); ?>"
												value="<?php echo esc_attr( $slug ); ?>"
												<?php checked( $current_theme, $slug ); ?>>
											<label for="theme-<?php echo esc_attr( $slug ); ?>" class="wpat-theme-card">
												<span class="wpat-theme-preview <?php echo esc_attr( $slug ); ?>" aria-hidden="true">
													<span class="dashicons <?php echo esc_attr( $definition['icon'] ); ?>"></span>
												</span>
												<span class="wpat-theme-name"><?php echo esc_html( $definition['name'] ); ?></span>
												<span class="wpat-theme-description"><?php echo esc_html( $definition['description'] ); ?></span>
											</label>
										</div>
									<?php endforeach; ?>
								</div>

								<div class="wpat-color-section" id="wpat-color-section" hidden>
									<h2><?php esc_html_e( 'Primary Color', 'wp-admin-themes' ); ?></h2>
									<div class="wpat-color-picker-wrapper">
										<div class="wpat-color-main">
											<div class="wpat-color-input-wrapper">
												<span class="wpat-color-preview" style="background-color: <?php echo esc_attr( $current_color ); ?>"></span>
												<label for="wpat-primary-color" class="screen-reader-text">
													<?php esc_html_e( 'Primary color', 'wp-admin-themes' ); ?>
												</label>
												<input type="text"
													name="<?php echo esc_attr( WP_ADMIN_THEMES_OPTION_COLOR ); ?>"
													id="wpat-primary-color"
													value="<?php echo esc_attr( $current_color ); ?>"
													class="wpat-color-input"
													data-default-color="#2271b1">
											</div>
											<button type="button" class="button" id="wpat-reset-colors">
												<?php esc_html_e( 'Reset to Default', 'wp-admin-themes' ); ?>
											</button>
										</div>
										<div class="wpat-color-presets" role="listbox" aria-label="<?php esc_attr_e( 'Color presets', 'wp-admin-themes' ); ?>">
											<?php foreach ( $this->preset_colors as $color => $label ) : ?>
												<button type="button"
													class="wpat-color-preset <?php echo $current_color === $color ? 'is-active' : ''; ?>"
													style="background-color: <?php echo esc_attr( $color ); ?>;"
													data-color="<?php echo esc_attr( $color ); ?>"
													title="<?php echo esc_attr( $label ); ?>"
													aria-label="<?php echo esc_attr( $label ); ?>">
												</button>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							</div>
							<div class="wpat-card-footer">
								<p class="description"><?php echo esc_html( $theme['description'] ); ?></p>
							</div>
						</fieldset>
					</div>

					<aside class="wpat-sidebar">
						<div class="wpat-sidebar-card">
							<h2>
								<span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
								<?php
								/* translators: %s: theme name */
								echo esc_html( sprintf( __( '%s Features', 'wp-admin-themes' ), $theme['name'] ) );
								?>
							</h2>
							<ul class="wpat-feature-list">
								<?php foreach ( $theme['features'] as $feature ) : ?>
									<li>
										<span class="dashicons dashicons-yes" aria-hidden="true"></span>
										<?php echo esc_html( $feature ); ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>

						<div class="wpat-sidebar-card">
							<h2>
								<span class="dashicons dashicons-saved" aria-hidden="true"></span>
								<?php esc_html_e( 'Save Settings', 'wp-admin-themes' ); ?>
							</h2>
							<?php submit_button( __( 'Save Changes', 'wp-admin-themes' ), 'primary', 'wp_admin_themes_submit', false ); ?>
						</div>

						<div class="wpat-sidebar-card">
							<h2>
								<span class="dashicons dashicons-editor-help" aria-hidden="true"></span>
								<?php esc_html_e( 'Need Help?', 'wp-admin-themes' ); ?>
							</h2>
							<p class="description">
								<?php esc_html_e( 'Choose Default to remove all overrides and use the styles bundled with this WordPress installation. The Enhanced theme layers tasteful refinements on top of WP 7.x.', 'wp-admin-themes' ); ?>
							</p>
						</div>
					</aside>
				</div>
			</form>
		</div>
		<?php
	}

	public function add_body_class( $classes ) {
		if ( 'modern' === $this->current_theme ) {
			$classes .= ' wp-admin-theme-modern';
		} elseif ( 'enhanced' === $this->current_theme ) {
			$classes .= ' wp-admin-theme-enhanced';
		} elseif ( 'classic' === $this->current_theme ) {
			$classes .= ' wp-admin-theme-classic';
		}
		return $classes;
	}

	public function ajax_preview_theme() {
		check_ajax_referer( 'wp-admin-themes-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to do that.', 'wp-admin-themes' ), 403 );
		}

		$theme = isset( $_POST['theme'] ) ? sanitize_key( wp_unslash( $_POST['theme'] ) ) : '';
		if ( ! in_array( $theme, array_keys( $this->themes ), true ) ) {
			wp_send_json_error( __( 'Invalid theme.', 'wp-admin-themes' ), 400 );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Preview ready.', 'wp-admin-themes' ),
				'theme'   => $theme,
			)
		);
	}

	public function ajax_reset_settings() {
		check_ajax_referer( 'wp-admin-themes-nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You do not have permission to do that.', 'wp-admin-themes' ), 403 );
		}

		update_option( WP_ADMIN_THEMES_OPTION_THEME, 'default' );
		update_option( WP_ADMIN_THEMES_OPTION_COLOR, '#2271b1' );

		wp_send_json_success(
			array(
				'message' => __( 'Settings reset to defaults.', 'wp-admin-themes' ),
			)
		);
	}

	private function adjust_color( $hex, $percent ) {
		$rgb = $this->hex_to_rgb_array( $hex );

		foreach ( $rgb as $key => $value ) {
			if ( $percent > 0 ) {
				$rgb[ $key ] = min( 255, $value + ( 255 * $percent / 100 ) );
			} else {
				$rgb[ $key ] = max( 0, $value + ( 255 * $percent / 100 ) );
			}
			$rgb[ $key ] = (int) round( $rgb[ $key ] );
		}

		return sprintf( '#%02x%02x%02x', $rgb['r'], $rgb['g'], $rgb['b'] );
	}

	private function hex_to_rgb_array( $hex ) {
		$hex = ltrim( $hex, '#' );

		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		return array(
			'r' => (int) hexdec( substr( $hex, 0, 2 ) ),
			'g' => (int) hexdec( substr( $hex, 2, 2 ) ),
			'b' => (int) hexdec( substr( $hex, 4, 2 ) ),
		);
	}

	public static function activate() {
		if ( false === get_option( WP_ADMIN_THEMES_OPTION_THEME ) ) {
			add_option( WP_ADMIN_THEMES_OPTION_THEME, 'default' );
		}
		if ( false === get_option( WP_ADMIN_THEMES_OPTION_COLOR ) ) {
			add_option( WP_ADMIN_THEMES_OPTION_COLOR, '#2271b1' );
		}
	}

	public static function deactivate() {
	}
}

register_activation_hook( __FILE__, array( 'WPAT_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WPAT_Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'WPAT_Plugin', 'instance' ) );
