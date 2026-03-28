<?php
/**
 * Plugin Name: WP Admin Themes
 * Plugin URI: https://example.com/wp-admin-themes
 * Description: Allows selection of different themes for the WordPress admin dashboard with an Enhanced theme featuring modern UX improvements.
 * Version: 1.1.0
 * Author: Developer
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: wp-admin-themes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WP_ADMIN_THEMES_VERSION', '1.1.0' );
define( 'WP_ADMIN_THEMES_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_ADMIN_THEMES_URL', plugin_dir_url( __FILE__ ) );

class WP_Admin_Themes {
    
    private $themes = array();
    private $current_theme = 'classic';
    private $primary_color = '#2271b1';
    private $preset_colors = array();
    
    public function __construct() {
        $this->load_options();
        
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'init_themes' ) );
        add_action( 'init', array( $this, 'init_preset_colors' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'handle_form_submit' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ), 999 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_theme_styles' ), 999 );
        add_filter( 'admin_body_class', array( $this, 'add_body_class' ) );
        
        // AJAX handlers
        add_action( 'wp_ajax_wpat_preview_theme', array( $this, 'ajax_preview_theme' ) );
        add_action( 'wp_ajax_wpat_reset_settings', array( $this, 'ajax_reset_settings' ) );
    }
    
    public function init_themes() {
        $this->themes = array(
            'classic' => array(
                'name'        => __( 'Classic', 'wp-admin-themes' ),
                'description' => __( 'Classic WordPress admin appearance loaded from plugin with the familiar dark sidebar.', 'wp-admin-themes' ),
                'icon'        => 'dashicons-wordpress',
                'features'    => array(
                    __( 'Classic WordPress styling', 'wp-admin-themes' ),
                    __( 'Dark sidebar navigation', 'wp-admin-themes' ),
                    __( 'Familiar user interface', 'wp-admin-themes' ),
                    __( 'Standard WordPress experience', 'wp-admin-themes' ),
                    __( 'Styles loaded from plugin', 'wp-admin-themes' ),
                ),
            ),
            'enhanced' => array(
                'name'        => __( 'Enhanced', 'wp-admin-themes' ),
                'description' => __( 'Modern, UX-focused design with a light sidebar, improved spacing, and enhanced visual hierarchy.', 'wp-admin-themes' ),
                'icon'        => 'dashicons-art',
                'features'    => array(
                    __( 'Modern light sidebar design', 'wp-admin-themes' ),
                    __( 'Enhanced visual hierarchy', 'wp-admin-themes' ),
                    __( 'Improved spacing & readability', 'wp-admin-themes' ),
                    __( 'Subtle shadows & modern cards', 'wp-admin-themes' ),
                    __( 'Customizable primary color', 'wp-admin-themes' ),
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
        $this->current_theme = get_option( 'wp_admin_theme', 'classic' );
        $this->primary_color = get_option( 'wp_admin_primary_color', '#2271b1' );
    }
    
    public function load_textdomain() {
        load_plugin_textdomain( 'wp-admin-themes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
    
    public function add_admin_menu() {
        add_options_page(
            __( 'WP Admin Themes', 'wp-admin-themes' ),
            __( 'Admin Themes', 'wp-admin-themes' ),
            'manage_options',
            'wp-admin-themes',
            array( $this, 'render_settings_page' )
        );
    }
    
    public function register_settings() {
        register_setting( 
            'wp_admin_themes_group', 
            'wp_admin_theme', 
            array(
                'type'              => 'string',
                'sanitize_callback' => array( $this, 'sanitize_theme' ),
                'default'           => 'classic',
            )
        );
        
        register_setting( 
            'wp_admin_themes_group', 
            'wp_admin_primary_color', 
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default'           => '#2271b1',
            )
        );
    }
    
    public function sanitize_theme( $value ) {
        $allowed = array_keys( $this->themes );
        return in_array( $value, $allowed, true ) ? $value : 'classic';
    }
    
    public function handle_form_submit() {
        if ( ! isset( $_POST['wp_admin_themes_submit'] ) ) {
            return;
        }
        
        if ( ! check_admin_referer( 'wp_admin_themes_action', 'wp_admin_themes_nonce' ) ) {
            return;
        }
        
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        $theme = isset( $_POST['wp_admin_theme'] ) ? sanitize_key( $_POST['wp_admin_theme'] ) : 'classic';
        $color = isset( $_POST['wp_admin_primary_color'] ) ? sanitize_hex_color( $_POST['wp_admin_primary_color'] ) : '#2271b1';
        
        if ( ! array_key_exists( $theme, $this->themes ) ) {
            $theme = 'classic';
        }
        
        update_option( 'wp_admin_theme', $theme );
        
        if ( 'enhanced' === $theme ) {
            update_option( 'wp_admin_primary_color', $color );
        }
        
        wp_safe_redirect( add_query_arg( 'settings-updated', 'true', wp_get_referer() ) );
        exit;
    }
    
    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_wp-admin-themes' !== $hook ) {
            return;
        }
        
        // Enqueue WordPress color picker
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        
        // Enqueue settings page styles
        wp_enqueue_style(
            'wp-admin-themes-settings',
            WP_ADMIN_THEMES_URL . 'css/settings.css',
            array(),
            WP_ADMIN_THEMES_VERSION
        );
        
        // Enqueue settings page JavaScript
        wp_enqueue_script(
            'wp-admin-themes-settings',
            WP_ADMIN_THEMES_URL . 'js/settings.js',
            array( 'jquery', 'jquery-ui-tooltip', 'wp-color-picker' ),
            WP_ADMIN_THEMES_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script( 
            'wp-admin-themes-settings',
            'wpatData',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wp-admin-themes-nonce' ),
            )
        );
    }
    
    public function enqueue_theme_styles() {
        if ( $this->current_theme === 'classic' ) {
            $this->dequeue_core_styles();
            $this->enqueue_classic_styles();
            return;
        }
        
        if ( $this->current_theme !== 'enhanced' ) {
            return;
        }
        
        $enhanced_css_path = WP_ADMIN_THEMES_PATH . 'themes/enhanced/enhanced.css';
        
        if ( file_exists( $enhanced_css_path ) ) {
            wp_enqueue_style(
                'wp-admin-theme-enhanced',
                WP_ADMIN_THEMES_URL . 'themes/enhanced/enhanced.css',
                array(),
                WP_ADMIN_THEMES_VERSION
            );
            
            $custom_css = $this->generate_custom_css();
            wp_add_inline_style( 'wp-admin-theme-enhanced', $custom_css );
        }
    }
    
    private function dequeue_core_styles() {
        wp_dequeue_style( 'wp-admin' );
        wp_dequeue_style( 'common' );
        wp_dequeue_style( 'forms' );
        wp_dequeue_style( 'admin-menu' );
        wp_dequeue_style( 'list-tables' );
        wp_dequeue_style( 'dashboard' );
        wp_dequeue_style( 'edit' );
        wp_dequeue_style( 'media' );
        wp_dequeue_style( 'themes' );
        wp_dequeue_style( 'widgets' );
        wp_dequeue_style( 'nav-menus' );
        wp_dequeue_style( 'about' );
        wp_dequeue_style( 'site-health' );
    }
    
    private function enqueue_classic_styles() {
        $base_url = WP_ADMIN_THEMES_URL . 'themes/classic/';
        
        wp_enqueue_style( 'wp-admin-classic', $base_url . 'wp-admin.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'common-classic', $base_url . 'common.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'forms-classic', $base_url . 'forms.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'admin-menu-classic', $base_url . 'admin-menu.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'list-tables-classic', $base_url . 'list-tables.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'dashboard-classic', $base_url . 'dashboard.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'edit-classic', $base_url . 'edit.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'media-classic', $base_url . 'media.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'themes-classic', $base_url . 'themes.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'widgets-classic', $base_url . 'widgets.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'nav-menus-classic', $base_url . 'nav-menus.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'about-classic', $base_url . 'about.css', array(), WP_ADMIN_THEMES_VERSION );
        wp_enqueue_style( 'site-health-classic', $base_url . 'site-health.css', array(), WP_ADMIN_THEMES_VERSION );
    }
    
    private function generate_custom_css() {
        $color = $this->primary_color;
        $hover = $this->adjust_color( $color, -15 );
        $rgb = $this->hex_to_rgb( $color );
        
        $css = sprintf( '
            :root {
                --wp-admin-theme-primary: %1$s;
                --wp-admin-theme-color: %1$s;
                --wp-admin-theme-color-rgb: %2$d, %3$d, %4$d;
                --wp-admin-theme-color-hover: %5$s;
            }
        ', esc_attr( $color ), $rgb['r'], $rgb['g'], $rgb['b'], esc_attr( $hover ) );
        
        return $css;
    }
    
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        // Get current values
        $current_theme = $this->current_theme;
        $current_color = $this->primary_color;
        
        // Check for success message
        $saved = isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'];
        ?>
        <div class="wrap wpat-settings-wrapper">
            
            <div class="wpat-settings-header">
                <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
                <p><?php esc_html_e( 'Customize your WordPress admin dashboard appearance with modern themes.', 'wp-admin-themes' ); ?></p>
            </div>
            
            <?php if ( $saved ) : ?>
                <div class="wpat-notice wpat-notice-success">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <span><?php esc_html_e( 'Settings saved successfully!', 'wp-admin-themes' ); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" id="wpat-settings-form">
                <?php wp_nonce_field( 'wp_admin_themes_action', 'wp_admin_themes_nonce' ); ?>
                
                <div class="wpat-settings-grid">
                    
                    <!-- Main Content -->
                    <div class="wpat-main-content">
                        
                        <!-- Theme Selection -->
                        <div class="wpat-card">
                            <div class="wpat-card-header">
                                <span class="dashicons dashicons-layout"></span>
                                <h2><?php esc_html_e( 'Select Theme', 'wp-admin-themes' ); ?></h2>
                            </div>
                            <div class="wpat-card-body">
                                <div class="wpat-theme-options">
                                    <?php foreach ( $this->themes as $slug => $theme ) : ?>
                                        <div class="wpat-theme-option">
                                            <input type="radio" 
                                                   name="wp_admin_theme" 
                                                   id="theme-<?php echo esc_attr( $slug ); ?>" 
                                                   value="<?php echo esc_attr( $slug ); ?>" 
                                                   <?php checked( $current_theme, $slug ); ?>>
                                            <label for="theme-<?php echo esc_attr( $slug ); ?>" class="wpat-theme-card">
                                                <div class="wpat-theme-preview <?php echo esc_attr( $slug ); ?>">
                                                    <span class="dashicons <?php echo esc_attr( $theme['icon'] ); ?>"></span>
                                                </div>
                                                <div class="wpat-theme-name"><?php echo esc_html( $theme['name'] ); ?></div>
                                                <div class="wpat-theme-description"><?php echo esc_html( $theme['description'] ); ?></div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Color Picker (only for enhanced theme) -->
                                <div class="wpat-color-section" id="color-section" style="display: <?php echo 'enhanced' === $current_theme ? 'block' : 'none'; ?>;">
                                    <h3><?php esc_html_e( 'Primary Color', 'wp-admin-themes' ); ?></h3>
                                    <div class="wpat-color-picker-wrapper">
                                        <div class="wpat-color-main">
                                            <div class="wpat-color-input-wrapper">
                                                <div class="wpat-color-preview" style="background-color: <?php echo esc_attr( $current_color ); ?>"></div>
                                                <input type="text" 
                                                       name="wp_admin_primary_color" 
                                                       id="wpat-primary-color" 
                                                       value="<?php echo esc_attr( $current_color ); ?>" 
                                                       class="wpat-color-input"
                                                       data-default-color="#2271b1">
                                            </div>
                                            <button type="button" class="button" id="wpat-reset-colors">
                                                <?php esc_html_e( 'Reset to Default', 'wp-admin-themes' ); ?>
                                            </button>
                                        </div>
                                        <div class="wpat-color-presets">
                                            <?php foreach ( $this->preset_colors as $color => $label ) : ?>
                                                <button type="button" 
                                                        class="wpat-color-preset <?php echo $current_color === $color ? 'active' : ''; ?>" 
                                                        style="background-color: <?php echo esc_attr( $color ); ?>" 
                                                        data-color="<?php echo esc_attr( $color ); ?>"
                                                        data-label="<?php echo esc_attr( $label ); ?>"
                                                        title="<?php echo esc_attr( $label ); ?>">
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="wpat-card-footer">
                                <p class="description">
                                    <?php esc_html_e( 'The Enhanced theme provides a modern, refreshed admin experience.', 'wp-admin-themes' ); ?>
                                </p>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="wpat-sidebar">
                        
                        <!-- Theme Features -->
                        <div class="wpat-sidebar-card">
                            <h3>
                                <span class="dashicons dashicons-star-filled"></span>
                                <?php esc_html_e( 'Enhanced Features', 'wp-admin-themes' ); ?>
                            </h3>
                            <ul class="wpat-feature-list">
                                <?php foreach ( $this->themes['enhanced']['features'] as $feature ) : ?>
                                    <li>
                                        <span class="dashicons dashicons-yes"></span>
                                        <?php echo esc_html( $feature ); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- Save Button -->
                        <div class="wpat-sidebar-card">
                            <h3>
                                <span class="dashicons dashicons-saved"></span>
                                <?php esc_html_e( 'Save Settings', 'wp-admin-themes' ); ?>
                            </h3>
                            <p class="description" style="margin-bottom: 16px;">
                                <?php esc_html_e( 'Your theme selection will be applied immediately after saving.', 'wp-admin-themes' ); ?>
                            </p>
                            <?php submit_button( __( 'Save Changes', 'wp-admin-themes' ), 'primary', 'wp_admin_themes_submit', false ); ?>
                        </div>
                        
                        <!-- Help -->
                        <div class="wpat-sidebar-card">
                            <h3>
                                <span class="dashicons dashicons-editor-help"></span>
                                <?php esc_html_e( 'Need Help?', 'wp-admin-themes' ); ?>
                            </h3>
                            <p class="description">
                                <?php esc_html_e( 'The Enhanced theme is designed to work seamlessly with all WordPress features and most plugins.', 'wp-admin-themes' ); ?>
                            </p>
                        </div>
                        
                    </div>
                    
                </div>
            </form>
        </div>
        <?php
    }
    
    public function add_body_class( $classes ) {
        if ( $this->current_theme === 'enhanced' ) {
            $classes .= ' wp-admin-theme-enhanced';
        }
        return $classes;
    }
    
    public function ajax_preview_theme() {
        check_ajax_referer( 'wp-admin-themes-nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }
        
        wp_send_json_success( array( 'message' => 'Preview loaded' ) );
    }
    
    public function ajax_reset_settings() {
        check_ajax_referer( 'wp-admin-themes-nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }
        
        update_option( 'wp_admin_theme', 'classic' );
        update_option( 'wp_admin_primary_color', '#2271b1' );
        
        wp_send_json_success( array( 'message' => 'Settings reset' ) );
    }
    
    private function adjust_color( $hex, $percent ) {
        $rgb = $this->hex_to_rgb_array( $hex );
        
        foreach ( $rgb as $key => $value ) {
            if ( $percent > 0 ) {
                $rgb[ $key ] = min( 255, $value + ( 255 * $percent / 100 ) );
            } else {
                $rgb[ $key ] = max( 0, $value + ( 255 * $percent / 100 ) );
            }
            $rgb[ $key ] = round( $rgb[ $key ] );
        }
        
        return sprintf( '#%02x%02x%02x', $rgb['r'], $rgb['g'], $rgb['b'] );
    }
    
    private function hex_to_rgb( $hex ) {
        return $this->hex_to_rgb_array( $hex );
    }
    
    private function hex_to_rgb_array( $hex ) {
        $hex = ltrim( $hex, '#' );
        
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return array(
            'r' => hexdec( substr( $hex, 0, 2 ) ),
            'g' => hexdec( substr( $hex, 2, 2 ) ),
            'b' => hexdec( substr( $hex, 4, 2 ) ),
        );
    }
    
    public static function activate() {
        add_option( 'wp_admin_theme', 'classic' );
        add_option( 'wp_admin_primary_color', '#2271b1' );
    }
    
    public static function deactivate() {
        // Don't delete options on deactivation, only on uninstall
    }
    
    public static function uninstall() {
        delete_option( 'wp_admin_theme' );
        delete_option( 'wp_admin_primary_color' );
    }
}

// Register hooks
register_activation_hook( __FILE__, array( 'WP_Admin_Themes', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Admin_Themes', 'deactivate' ) );

// Initialize plugin
new WP_Admin_Themes();
