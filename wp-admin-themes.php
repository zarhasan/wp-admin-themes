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
define( 'WP_ADMIN_THEMES_OPTION_SIDEBAR', 'wp_admin_sidebar_color' );
define( 'WP_ADMIN_THEMES_OPTION_PRIMARY', 'wp_admin_primary_color' );

const WPAT_THEME_DEFAULTS = array(
	'default'  => array(
		'sidebar' => '',
		'primary' => '',
	),
	'classic'  => array(
		'sidebar' => '#1d2327',
		'primary' => '#096484',
	),
	'enhanced' => array(
		'sidebar' => '#1e1e1e',
		'primary' => '#3858e9',
	),
	'modern'   => array(
		'sidebar' => '#1e1e1e',
		'primary' => '#2271b1',
	),
);

const WPAT_PRESET_SIDEBAR = array(
	'#1d2327' => 'Dark Slate',
	'#1e1e1e' => 'WP Modern Dark',
	'#101828' => 'Midnight',
	'#2c3338' => 'Charcoal',
	'#3c434a' => 'Stone',
	'#50575e' => 'Slate',
	'#646970' => 'Light Gray',
	'#a7aaad' => 'Mist',
);

const WPAT_PRESET_PRIMARY = array(
	'#3858e9' => 'WP Modern Blue',
	'#096484' => 'Classic Blue',
	'#0073aa' => 'WordPress Blue',
	'#2271b1' => 'Mid Blue',
	'#c7253e' => 'Sunset Red',
	'#00a32a' => 'Forest Green',
	'#dba617' => 'Golden',
	'#7c5bd4' => 'Royal Purple',
	'#0c8341' => 'Emerald',
	'#d63638' => 'Cherry Red',
);

final class WPAT_Plugin {

	private $themes = array();

	private $current_theme    = 'default';
	private $sidebar_color    = '';
	private $primary_color    = '';
	private $preset_sidebar   = array();
	private $preset_primary   = array();
	private $active_channels  = array( 'sidebar', 'primary' );

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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_theme_styles' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ), 200 );
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
				'description' => __( 'Faithful classic WordPress admin experience loaded from the bundled 6.9.5 stylesheets with a tunable sidebar and primary color.', 'wp-admin-themes' ),
				'icon'        => 'dashicons-admin-appearance',
				'features'    => array(
					__( 'Bundled WP 6.9.5 stylesheets', 'wp-admin-themes' ),
					__( 'Modern 7.x core styles fully dequeued', 'wp-admin-themes' ),
					__( 'Tunable sidebar and primary color', 'wp-admin-themes' ),
					__( 'Familiar user interface', 'wp-admin-themes' ),
				),
			),
			'enhanced' => array(
				'name'        => __( 'Enhanced (7.0.2)', 'wp-admin-themes' ),
				'description' => __( 'Refined enhancement of the new WordPress theme. Modernizes every table, card, and element while staying faithful to the 7.0.2 visual language.', 'wp-admin-themes' ),
				'icon'        => 'dashicons-star-filled',
				'features'    => array(
					__( 'Layers on top of WP 7.x core styles', 'wp-admin-themes' ),
					__( 'Sidebar + primary color tokens', 'wp-admin-themes' ),
					__( 'Smoother transitions (0.15s)', 'wp-admin-themes' ),
					__( 'Sharper focus rings', 'wp-admin-themes' ),
					__( 'Refined table / card / form polish', 'wp-admin-themes' ),
				),
			),
			'modern'   => array(
				'name'        => __( 'Modern', 'wp-admin-themes' ),
				'description' => __( 'Custom light sidebar that blends with content, with tunable accent and brand color.', 'wp-admin-themes' ),
				'icon'        => 'dashicons-art',
				'features'    => array(
					__( 'Light sidebar that blends with content', 'wp-admin-themes' ),
					__( 'Refined visual hierarchy', 'wp-admin-themes' ),
					__( 'Customizable sidebar and primary color', 'wp-admin-themes' ),
					__( 'Subtle shadows and modern cards', 'wp-admin-themes' ),
					__( 'Better focus states', 'wp-admin-themes' ),
				),
			),
		);
	}

	public function init_preset_colors() {
		$this->preset_sidebar = WPAT_PRESET_SIDEBAR;
		$this->preset_primary = WPAT_PRESET_PRIMARY;
	}

	private function load_options() {
		$this->current_theme = get_option( WP_ADMIN_THEMES_OPTION_THEME, 'default' );
		$this->sidebar_color = get_option( WP_ADMIN_THEMES_OPTION_SIDEBAR, '' );
		$this->primary_color = get_option( WP_ADMIN_THEMES_OPTION_PRIMARY, '' );
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
			WP_ADMIN_THEMES_OPTION_SIDEBAR,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_color' ),
				'default'           => '',
			)
		);

		register_setting(
			'wp_admin_themes_group',
			WP_ADMIN_THEMES_OPTION_PRIMARY,
			array(
				'type'              => 'string',
				'sanitize_callback' => array( $this, 'sanitize_color' ),
				'default'           => '',
			)
		);
	}

	public function sanitize_theme( $value ) {
		$value   = is_string( $value ) ? sanitize_key( $value ) : 'default';
		$allowed = array_keys( $this->themes );
		if ( ! in_array( $value, $allowed, true ) ) {
			$value = 'default';
		}

		$old_theme = $this->current_theme;
		$new_theme = $value;

		if ( 'default' !== $new_theme && 'default' !== $old_theme && $old_theme !== $new_theme ) {
			$old_defaults   = isset( WPAT_THEME_DEFAULTS[ $old_theme ] ) ? WPAT_THEME_DEFAULTS[ $old_theme ] : array();
			$new_defaults   = isset( WPAT_THEME_DEFAULTS[ $new_theme ] ) ? WPAT_THEME_DEFAULTS[ $new_theme ] : array();
			$cur_sidebar    = get_option( WP_ADMIN_THEMES_OPTION_SIDEBAR, '' );
			$cur_primary    = get_option( WP_ADMIN_THEMES_OPTION_PRIMARY, '' );

			if ( isset( $old_defaults['sidebar'] ) && $old_defaults['sidebar'] === $cur_sidebar && isset( $new_defaults['sidebar'] ) ) {
				update_option( WP_ADMIN_THEMES_OPTION_SIDEBAR, $new_defaults['sidebar'] );
			}
			if ( isset( $old_defaults['primary'] ) && $old_defaults['primary'] === $cur_primary && isset( $new_defaults['primary'] ) ) {
				update_option( WP_ADMIN_THEMES_OPTION_PRIMARY, $new_defaults['primary'] );
			}
		}

		return $new_theme;
	}

	public function sanitize_color( $value ) {
		return sanitize_hex_color( $value ) ?: '';
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
					'saving'         => __( 'Saving…', 'wp-admin-themes' ),
					'save'           => __( 'Save Changes', 'wp-admin-themes' ),
					'resetConfirm'   => __( 'Reset all settings to defaults?', 'wp-admin-themes' ),
					'resetSidebar'   => __( 'Reset sidebar color?', 'wp-admin-themes' ),
					'resetPrimary'   => __( 'Reset primary color?', 'wp-admin-themes' ),
				),
				'defaults' => array(
					'classic'  => array(
						'sidebar' => '#1d2327',
						'primary' => '#096484',
					),
					'enhanced' => array(
						'sidebar' => '#1e1e1e',
						'primary' => '#3858e9',
					),
					'modern'   => array(
						'sidebar' => '#1e1e1e',
						'primary' => '#2271b1',
					),
				),
			)
		);
	}

	public function enqueue_theme_styles( $hook ) {
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

		if ( 'classic' !== $this->current_theme && 'enhanced' !== $this->current_theme ) {
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
			'code-editor',
			'color-picker',
			'deprecated-media',
			'farbtastic',
			'install',
			'login',
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
		$base = WP_ADMIN_THEMES_URL . 'themes/classic/';
		$rtl  = is_rtl();

		$bundles = array(
			'wp-admin-classic'           => array( 'wp-admin' ),
			'common-classic'             => array( 'common' ),
			'forms-classic'              => array( 'forms' ),
			'admin-menu-classic'         => array( 'admin-menu' ),
			'dashboard-classic'          => array( 'dashboard' ),
			'list-tables-classic'        => array( 'list-tables' ),
			'edit-classic'               => array( 'edit' ),
			'revisions-classic'          => array( 'revisions' ),
			'media-classic'              => array( 'media' ),
			'themes-classic'             => array( 'themes' ),
			'widgets-classic'            => array( 'widgets' ),
			'nav-menus-classic'          => array( 'nav-menus' ),
			'about-classic'              => array( 'about' ),
			'site-icon-classic'          => array( 'site-icon' ),
			'l10n-classic'               => array( 'l10n' ),
			'site-health-classic'        => array( 'site-health' ),
			'code-editor-classic'        => array( 'code-editor' ),
			'color-picker-classic'       => array( 'color-picker' ),
			'customize-controls-classic'  => array( 'customize-controls' ),
			'customize-nav-menus-classic' => array( 'customize-nav-menus' ),
			'customize-widgets-classic'  => array( 'customize-widgets' ),
			'deprecated-media-classic'   => array( 'deprecated-media' ),
			'farbtastic-classic'         => array( 'farbtastic' ),
			'install-classic'            => array( 'install' ),
			'login-classic'              => array( 'login' ),
		);

		$deps = array();
		foreach ( $bundles as $handle => $names ) {
			$basename = $names[0];
			$candidates = array( $basename . '.css' );
			if ( $rtl && file_exists( WP_ADMIN_THEMES_PATH . 'themes/classic/' . $basename . '-rtl.css' ) ) {
				$candidates[] = $basename . '-rtl.css';
			}
			$found = null;
			foreach ( $candidates as $candidate ) {
				if ( file_exists( WP_ADMIN_THEMES_PATH . 'themes/classic/' . $candidate ) ) {
					$found = $candidate;
					break;
				}
			}
			if ( null === $found ) {
				continue;
			}
			wp_enqueue_style( $handle, $base . $found, $deps, WP_ADMIN_THEMES_VERSION );
			$deps[] = $handle;
		}

		$custom = WP_ADMIN_THEMES_PATH . 'themes/classic/custom-color.css';
		if ( file_exists( $custom ) ) {
			wp_enqueue_style(
				'wpat-classic-color',
				WP_ADMIN_THEMES_URL . 'themes/classic/custom-color.css',
				$deps,
				WP_ADMIN_THEMES_VERSION
			);
			$deps[] = 'wpat-classic-color';
		}

		$css = $this->generate_classic_css();
		if ( '' !== $css ) {
			wp_add_inline_style( 'wpat-classic-color', $css );
		}
	}

	private function enqueue_enhanced_styles() {
		$base = WP_ADMIN_THEMES_URL . 'themes/enhanced/';
		$rtl  = is_rtl();

		$bundles = array(
			'wp-admin-enhanced'           => array( 'wp-admin' ),
			'common-enhanced'             => array( 'common' ),
			'forms-enhanced'              => array( 'forms' ),
			'admin-menu-enhanced'         => array( 'admin-menu' ),
			'dashboard-enhanced'          => array( 'dashboard' ),
			'list-tables-enhanced'        => array( 'list-tables' ),
			'edit-enhanced'               => array( 'edit' ),
			'revisions-enhanced'          => array( 'revisions' ),
			'media-enhanced'              => array( 'media' ),
			'themes-enhanced'             => array( 'themes' ),
			'widgets-enhanced'            => array( 'widgets' ),
			'nav-menus-enhanced'          => array( 'nav-menus' ),
			'about-enhanced'              => array( 'about' ),
			'site-icon-enhanced'          => array( 'site-icon' ),
			'l10n-enhanced'               => array( 'l10n' ),
			'site-health-enhanced'        => array( 'site-health' ),
			'code-editor-enhanced'        => array( 'code-editor' ),
			'color-picker-enhanced'       => array( 'color-picker' ),
			'customize-controls-enhanced'  => array( 'customize-controls' ),
			'customize-nav-menus-enhanced' => array( 'customize-nav-menus' ),
			'customize-widgets-enhanced'  => array( 'customize-widgets' ),
			'deprecated-media-enhanced'   => array( 'deprecated-media' ),
			'farbtastic-enhanced'         => array( 'farbtastic' ),
			'install-enhanced'            => array( 'install' ),
			'login-enhanced'              => array( 'login' ),
			'view-transitions-enhanced'   => array( 'view-transitions' ),
		);

		$deps = array();
		foreach ( $bundles as $handle => $names ) {
			$basename = $names[0];
			$candidates = array( $basename . '.css' );
			if ( $rtl && file_exists( WP_ADMIN_THEMES_PATH . 'themes/enhanced/' . $basename . '-rtl.css' ) ) {
				$candidates[] = $basename . '-rtl.css';
			}
			$found = null;
			foreach ( $candidates as $candidate ) {
				if ( file_exists( WP_ADMIN_THEMES_PATH . 'themes/enhanced/' . $candidate ) ) {
					$found = $candidate;
					break;
				}
			}
			if ( null === $found ) {
				continue;
			}
			wp_enqueue_style( $handle, $base . $found, $deps, WP_ADMIN_THEMES_VERSION );
			$deps[] = $handle;
		}

		wp_enqueue_style(
			'wpat-enhanced',
			$base . 'enhance.css',
			$deps,
			WP_ADMIN_THEMES_VERSION
		);
		$deps[] = 'wpat-enhanced';

		$custom = WP_ADMIN_THEMES_PATH . 'themes/enhanced/custom-color.css';
		if ( file_exists( $custom ) ) {
			wp_enqueue_style(
				'wpat-enhanced-color',
				WP_ADMIN_THEMES_URL . 'themes/enhanced/custom-color.css',
				$deps,
				WP_ADMIN_THEMES_VERSION
			);
			$deps[] = 'wpat-enhanced-color';
		}

		$css = $this->generate_enhanced_css();
		if ( '' !== $css ) {
			wp_add_inline_style( 'wpat-enhanced-color', $css );
		}
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

		$custom = $this->generate_modern_css();
		wp_add_inline_style( 'wp-admin-theme-modern', $custom );
	}

	private function default_value( $channel ) {
		$defaults = isset( WPAT_THEME_DEFAULTS[ $this->current_theme ] ) ? WPAT_THEME_DEFAULTS[ $this->current_theme ] : array();
		return isset( $defaults[ $channel ] ) ? $defaults[ $channel ] : '';
	}

	private function resolve_value( $channel ) {
		$value = 'sidebar' === $channel ? $this->sidebar_color : $this->primary_color;
		if ( '' !== $value ) {
			return $value;
		}
		return $this->default_value( $channel );
	}

	private function resolve_sidebar() {
		return $this->resolve_value( 'sidebar' );
	}

	private function resolve_primary() {
		return $this->resolve_value( 'primary' );
	}

	private function generate_classic_css() {
		$sidebar = $this->resolve_sidebar();
		$primary = $this->resolve_primary();
		$sidebar_rgb = $this->hex_to_rgb_array( $sidebar );
		$primary_rgb = $this->hex_to_rgb_array( $primary );
		$sidebar_hover = $this->adjust_color( $sidebar, 12 );
		$primary_hover = $this->adjust_color( $primary, -10 );

		return sprintf(
			'body.wp-admin-theme-classic{--wpat-sidebar:%1$s;--wpat-sidebar-rgb:%2$d,%3$d,%4$d;--wpat-sidebar-hover:%5$s;--wpat-primary:%6$s;--wpat-primary-rgb:%7$d,%8$d,%9$d;--wpat-primary-hover:%10$s;--wpat-link:%6$s;--wpat-link-hover:%11$s;--wpat-focus:%12$s;--wpat-notification:%13$s;}',
			esc_attr( $sidebar ),
			(int) $sidebar_rgb['r'],
			(int) $sidebar_rgb['g'],
			(int) $sidebar_rgb['b'],
			esc_attr( $sidebar_hover ),
			esc_attr( $primary ),
			(int) $primary_rgb['r'],
			(int) $primary_rgb['g'],
			(int) $primary_rgb['b'],
			esc_attr( $primary_hover ),
			esc_attr( $this->blend_with_white( $primary, 0.4 ) ),
			esc_attr( $primary_hover ),
			esc_attr( $this->adjust_color( $primary, 25 ) )
		);
	}

	private function generate_enhanced_css() {
		$sidebar = $this->resolve_sidebar();
		$primary = $this->resolve_primary();

		$primary_rgb      = $this->hex_to_rgb_array( $primary );
		$darker10         = $this->darken_color( $primary, 6 );
		$darker20         = $this->darken_color( $primary, 12 );
		$darker10_rgb     = $this->hex_to_rgb_array( $darker10 );
		$sidebar_rgb      = $this->hex_to_rgb_array( $sidebar );
		$sidebar_text     = $this->blend_with_white( $sidebar, 0.78 );
		$sidebar_text_rgb = $this->hex_to_rgb_array( $sidebar_text );
		$sidebar_hover    = $this->adjust_color( $sidebar, 15 );

		return sprintf(
			':root{--wp-admin-theme-color:%1$s;--wp-admin-theme-color--rgb:%2$d,%3$d,%4$d;--wp-admin-theme-color-darker-10:%5$s;--wp-admin-theme-color-darker-10--rgb:%6$d,%7$d,%8$d;--wp-admin-theme-color-darker-20:%9$s;--wp-admin-theme-color-hover:%5$s;--wp-admin-sidebar:%10$s;--wp-admin-sidebar--rgb:%11$d,%12$d,%13$d;--wp-admin-sidebar-text:%14$s;--wp-admin-sidebar-text--rgb:%15$d,%16$d,%17$d;--wp-admin-sidebar-hover:%18$s;}',
			esc_attr( $primary ),
			(int) $primary_rgb['r'],
			(int) $primary_rgb['g'],
			(int) $primary_rgb['b'],
			esc_attr( $darker10 ),
			(int) $darker10_rgb['r'],
			(int) $darker10_rgb['g'],
			(int) $darker10_rgb['b'],
			esc_attr( $darker20 ),
			esc_attr( $sidebar ),
			(int) $sidebar_rgb['r'],
			(int) $sidebar_rgb['g'],
			(int) $sidebar_rgb['b'],
			esc_attr( $sidebar_text ),
			(int) $sidebar_text_rgb['r'],
			(int) $sidebar_text_rgb['g'],
			(int) $sidebar_text_rgb['b'],
			esc_attr( $sidebar_hover )
		);
	}

	private function generate_modern_css() {
		$sidebar = $this->resolve_sidebar();
		$primary = $this->resolve_primary();

		$primary_rgb = $this->hex_to_rgb_array( $primary );
		$darker10    = $this->adjust_color( $primary, -10 );
		$darker20    = $this->adjust_color( $primary, -20 );

		return sprintf(
			':root{--wp-admin-theme-primary:%1$s;--wp-admin-theme-color:%1$s;--wp-admin-theme-color--rgb:%2$d,%3$d,%4$d;--wp-admin-theme-color-rgb:%2$d,%3$d,%4$d;--wp-admin-theme-color-hover:%5$s;--wp-admin-theme-color-darker-10:%6$s;--wp-admin-theme-color-darker-20:%7$s;--wp-admin-theme-color-light:%8$s;--wpat-sidebar:%9$s;--wpat-sidebar-lighter:%10$s;}',
			esc_attr( $primary ),
			(int) $primary_rgb['r'],
			(int) $primary_rgb['g'],
			(int) $primary_rgb['b'],
			esc_attr( $darker10 ),
			esc_attr( $darker10 ),
			esc_attr( $darker20 ),
			esc_attr( $this->adjust_color( $primary, 25 ) ),
			esc_attr( $sidebar ),
			esc_attr( $this->blend_with_white( $sidebar, 0.92 ) )
		);
	}

	private function blend_with_white( $hex, $ratio ) {
		$rgb   = $this->hex_to_rgb_array( $hex );
		$ratio = max( 0.0, min( 1.0, $ratio ) );

		return sprintf(
			'#%02x%02x%02x',
			(int) round( $rgb['r'] + ( 255 - $rgb['r'] ) * $ratio ),
			(int) round( $rgb['g'] + ( 255 - $rgb['g'] ) * $ratio ),
			(int) round( $rgb['b'] + ( 255 - $rgb['b'] ) * $ratio )
		);
	}

	private function render_color_picker( $channel, $presets, $current, $default, $title, $description ) {
		$id = 'wpat-' . $channel . '-color';
		?>
		<div class="wpat-color-picker-wrapper" data-channel="<?php echo esc_attr( $channel ); ?>">
			<div class="wpat-color-main">
				<div class="wpat-color-input-wrapper">
					<span class="wpat-color-preview" style="background-color: <?php echo esc_attr( $current ); ?>"></span>
					<label for="<?php echo esc_attr( $id ); ?>" class="screen-reader-text">
						<?php echo esc_html( $title ); ?>
					</label>
					<input type="text"
						name="<?php echo esc_attr( 'sidebar' === $channel ? WP_ADMIN_THEMES_OPTION_SIDEBAR : WP_ADMIN_THEMES_OPTION_PRIMARY ); ?>"
						id="<?php echo esc_attr( $id ); ?>"
						value="<?php echo esc_attr( $current ); ?>"
						class="wpat-color-input"
						data-default-color="<?php echo esc_attr( $default ); ?>"
						data-channel="<?php echo esc_attr( $channel ); ?>">
				</div>
				<button type="button" class="button wpat-reset-color" data-channel="<?php echo esc_attr( $channel ); ?>">
					<?php esc_html_e( 'Reset', 'wp-admin-themes' ); ?>
				</button>
			</div>
			<div class="wpat-color-presets" role="listbox" aria-label="<?php echo esc_attr( $title . ' ' . __( 'presets', 'wp-admin-themes' ) ); ?>">
				<?php foreach ( $presets as $hex => $label ) : ?>
					<button type="button"
						class="wpat-color-preset <?php echo strcasecmp( $current, $hex ) === 0 ? 'is-active' : ''; ?>"
						style="background-color: <?php echo esc_attr( $hex ); ?>;"
						data-color="<?php echo esc_attr( $hex ); ?>"
						data-channel="<?php echo esc_attr( $channel ); ?>"
						title="<?php echo esc_attr( $label ); ?>"
						aria-label="<?php echo esc_attr( $label ); ?>">
					</button>
				<?php endforeach; ?>
			</div>
			<?php if ( '' !== $description ) : ?>
				<p class="description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wp-admin-themes' ) );
		}

		$current_theme = $this->current_theme;
		$current_sidebar = $this->resolve_sidebar();
		$current_primary = $this->resolve_primary();
		$default_sidebar = $this->default_value( 'sidebar' );
		$default_primary = $this->default_value( 'primary' );
		$saved          = isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'];
		$theme          = isset( $this->themes[ $current_theme ] ) ? $this->themes[ $current_theme ] : $this->themes['default'];
		$show_colors    = 'default' !== $current_theme;
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

								<div class="wpat-color-section" id="wpat-color-section"<?php echo $show_colors ? '' : ' hidden'; ?>>
									<h2 class="wpat-color-section-title">
										<?php esc_html_e( 'Colors', 'wp-admin-themes' ); ?>
									</h2>
									<p class="description">
										<?php
										printf(
											/* translators: %s: theme name, %1$s: default sidebar hex, %2$s: default primary hex */
											esc_html__( 'Sidebar and primary color tokens for %s. Defaults: sidebar %1$s, primary %2$s. Use Reset to restore a single channel.', 'wp-admin-themes' ),
											esc_html( $theme['name'] ),
											esc_html( $default_sidebar ),
											esc_html( $default_primary )
										);
										?>
									</p>

									<h3 class="wpat-color-channel-title">
										<span class="dashicons dashicons-menu" aria-hidden="true"></span>
										<?php
										printf(
											/* translators: %s: default sidebar hex */
											esc_html__( 'Sidebar color — default %s', 'wp-admin-themes' ),
											'<code>' . esc_html( $default_sidebar ) . '</code>'
										);
										?>
									</h3>
									<?php
									$this->render_color_picker(
										'sidebar',
										$this->preset_sidebar,
										$current_sidebar,
										$default_sidebar,
										__( 'Sidebar color', 'wp-admin-themes' ),
										__( 'Drives the admin sidebar, top bar, and dark UI surfaces.', 'wp-admin-themes' )
									);
									?>

									<h3 class="wpat-color-channel-title">
										<span class="dashicons dashicons-art" aria-hidden="true"></span>
										<?php
										printf(
											/* translators: %s: default primary hex */
											esc_html__( 'Primary color — default %s', 'wp-admin-themes' ),
											'<code>' . esc_html( $default_primary ) . '</code>'
										);
										?>
									</h3>
									<?php
									$this->render_color_picker(
										'primary',
										$this->preset_primary,
										$current_primary,
										$default_primary,
										__( 'Primary color', 'wp-admin-themes' ),
										__( 'Drives links, focus rings, and primary buttons.', 'wp-admin-themes' )
									);
									?>
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
		delete_option( WP_ADMIN_THEMES_OPTION_SIDEBAR );
		delete_option( WP_ADMIN_THEMES_OPTION_PRIMARY );

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

	private function darken_color( $hex, $amount ) {
		$hsl     = $this->rgb_to_hsl( $this->hex_to_rgb_array( $hex ) );
		$hsl['l'] = max( 0.0, $hsl['l'] - ( $amount / 100 ) );
		return $this->hsl_to_hex( $hsl );
	}

	private function rgb_to_hsl( $rgb ) {
		$r = $rgb['r'] / 255;
		$g = $rgb['g'] / 255;
		$b = $rgb['b'] / 255;

		$max   = max( $r, $g, $b );
		$min   = min( $r, $g, $b );
		$delta = $max - $min;

		$l = ( $max + $min ) / 2;
		$h = 0;
		$s = 0;

		if ( $delta > 0 ) {
			$s = $l > 0.5 ? $delta / ( 2 - $max - $min ) : $delta / ( $max + $min );

			switch ( $max ) {
				case $r:
					$h = ( $g - $b ) / $delta + ( $g < $b ? 6 : 0 );
					break;
				case $g:
					$h = ( $b - $r ) / $delta + 2;
					break;
				default:
					$h = ( $r - $g ) / $delta + 4;
					break;
			}
			$h /= 6;
		}

		return array( 'h' => $h, 's' => $s, 'l' => $l );
	}

	private function hsl_to_hex( $hsl ) {
		$h = $hsl['h'];
		$s = $hsl['s'];
		$l = $hsl['l'];

		if ( $s <= 0.0 ) {
			$v = (int) round( $l * 255 );
			return sprintf( '#%02x%02x%02x', $v, $v, $v );
		}

		$q = $l < 0.5 ? $l * ( 1 + $s ) : $l + $s - $l * $s;
		$p = 2 * $l - $q;
		$r = $this->hsl_hue_to_rgb( $p, $q, $h + 1 / 3 );
		$g = $this->hsl_hue_to_rgb( $p, $q, $h );
		$b = $this->hsl_hue_to_rgb( $p, $q, $h - 1 / 3 );

		return sprintf( '#%02x%02x%02x', (int) round( $r * 255 ), (int) round( $g * 255 ), (int) round( $b * 255 ) );
	}

	private function hsl_hue_to_rgb( $p, $q, $t ) {
		if ( $t < 0 ) {
			$t += 1;
		}
		if ( $t > 1 ) {
			$t -= 1;
		}
		if ( $t < 1 / 6 ) {
			return $p + ( $q - $p ) * 6 * $t;
		}
		if ( $t < 1 / 2 ) {
			return $q;
		}
		if ( $t < 2 / 3 ) {
			return $p + ( $q - $p ) * ( 2 / 3 - $t ) * 6;
		}
		return $p;
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
		if ( false === get_option( WP_ADMIN_THEMES_OPTION_SIDEBAR ) ) {
			add_option( WP_ADMIN_THEMES_OPTION_SIDEBAR, '' );
		}
		if ( false === get_option( WP_ADMIN_THEMES_OPTION_PRIMARY ) ) {
			add_option( WP_ADMIN_THEMES_OPTION_PRIMARY, '' );
		}
	}

	public static function deactivate() {
	}
}

register_activation_hook( __FILE__, array( 'WPAT_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WPAT_Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'WPAT_Plugin', 'instance' ) );
