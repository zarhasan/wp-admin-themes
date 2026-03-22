<?php
/**
 * Plugin Name: WP Admin Themes
 * Plugin URI: https://example.com/wp-admin-themes
 * Description: Allows selection of different themes for the WordPress admin dashboard with an Enhanced theme featuring modern UX improvements.
 * Version: 1.0.0
 * Author: Developer
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: wp-admin-themes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WP_ADMIN_THEMES_VERSION', '1.0.0' );
define( 'WP_ADMIN_THEMES_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP_ADMIN_THEMES_URL', plugin_dir_url( __FILE__ ) );

class WP_Admin_Themes {
    
    private $themes = array();
    private $current_theme = 'default';
    private $primary_color = '#2271b1';
    
    public function __construct() {
        $this->themes = array(
            'default' => array(
                'name' => 'Default WordPress',
                'description' => 'The classic WordPress admin appearance.'
            ),
            'enhanced' => array(
                'name' => 'Enhanced',
                'description' => 'Modern, UX-focused version with enhanced visual design.',
                'path' => WP_ADMIN_THEMES_PATH . 'themes/enhanced/enhanced.css'
            )
        );
        
        $this->current_theme = get_option( 'wp_admin_theme', 'default' );
        $this->primary_color = get_option( 'wp_admin_primary_color', '#2271b1' );
        
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_theme_styles' ), 999 );
        add_filter( 'admin_body_class', array( $this, 'add_body_class' ) );
        
        add_action( 'wp_ajax_reset_enhanced_theme_cache', array( $this, 'ajax_reset_cache' ) );
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
        register_setting( 'wp_admin_themes_group', 'wp_admin_theme', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_key',
            'default'           => 'default'
        ) );
        
        register_setting( 'wp_admin_themes_group', 'wp_admin_primary_color', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_hex_color',
            'default'           => '#2271b1'
        ) );
        
        add_settings_section(
            'wp_admin_themes_main',
            __( 'Theme Selection', 'wp-admin-themes' ),
            array( $this, 'render_section' ),
            'wp-admin-themes'
        );
        
        add_settings_field(
            'wp_admin_theme',
            __( 'Active Theme', 'wp-admin-themes' ),
            array( $this, 'render_theme_selector' ),
            'wp-admin-themes',
            'wp_admin_themes_main'
        );
        
        add_settings_field(
            'wp_admin_primary_color',
            __( 'Primary Color', 'wp-admin-themes' ),
            array( $this, 'render_color_picker' ),
            'wp-admin-themes',
            'wp_admin_themes_main'
        );
    }
    
    public function render_section() {
        echo '<p>' . esc_html__( 'Select an admin theme and customize its appearance.', 'wp-admin-themes' ) . '</p>';
    }
    
    public function render_theme_selector() {
        $current = $this->current_theme;
        
        $translated_themes = array(
            'default' => array(
                'name' => __( 'Default WordPress', 'wp-admin-themes' ),
                'description' => __( 'The classic WordPress admin appearance.', 'wp-admin-themes' )
            ),
            'enhanced' => array(
                'name' => __( 'Enhanced', 'wp-admin-themes' ),
                'description' => __( 'Modern, UX-focused version with enhanced visual design.', 'wp-admin-themes' )
            )
        );
        
        echo '<div class="wp-admin-theme-selector">';
        foreach ( $this->themes as $slug => $theme ) {
            $checked = checked( $current, $slug, false );
            $name = isset( $translated_themes[ $slug ]['name'] ) ? $translated_themes[ $slug ]['name'] : $theme['name'];
            $description = isset( $translated_themes[ $slug ]['description'] ) ? $translated_themes[ $slug ]['description'] : $theme['description'];
            echo '<div class="wp-admin-theme-option">';
            echo '<label>';
            echo '<input type="radio" name="wp_admin_theme" value="' . esc_attr( $slug ) . '" ' . $checked . ' />';
            echo '<span class="theme-name">' . esc_html( $name ) . '</span>';
            echo '<span class="theme-description">' . esc_html( $description ) . '</span>';
            echo '</label>';
            echo '</div>';
        }
        echo '</div>';
    }
    
    public function render_color_picker() {
        $color = $this->primary_color;
        $preset_colors = array(
            '#2271b1' => __( 'WordPress Blue', 'wp-admin-themes' ),
            '#007017' => __( 'Forest Green', 'wp-admin-themes' ),
            '#7c5bd4' => __( 'Purple', 'wp-admin-themes' ),
            '#d63638' => __( 'Red', 'wp-admin-themes' ),
            '#dba617' => __( 'Golden', 'wp-admin-themes' ),
            '#00a32a' => __( 'Bright Green', 'wp-admin-themes' ),
            '#1d2327' => __( 'Dark Slate', 'wp-admin-themes' ),
            '#646970' => __( 'Gray', 'wp-admin-themes' )
        );
        
        echo '<div class="wp-admin-color-picker-wrapper">';
        echo '<input type="text" name="wp_admin_primary_color" value="' . esc_attr( $color ) . '" class="wp-admin-color-picker" data-default-color="#2271b1" />';
        echo '<div class="color-presets">';
        foreach ( $preset_colors as $hex => $label ) {
            echo '<button type="button" class="color-preset" data-color="' . esc_attr( $hex ) . '" style="background-color:' . esc_attr( $hex ) . '" title="' . esc_attr( $label ) . '"></button>';
        }
        echo '</div>';
        echo '<p class="description">' . esc_html__( 'Choose a primary color for the Enhanced theme.', 'wp-admin-themes' ) . '</p>';
        echo '</div>';
    }
    
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'wp_admin_themes_group' );
                do_settings_sections( 'wp-admin-themes' );
                submit_button();
                ?>
            </form>
            
            <hr>
            
            <h2><?php esc_html_e( 'Theme Preview', 'wp-admin-themes' ); ?></h2>
            <p><?php esc_html_e( 'The Enhanced theme applies modern UX improvements to your WordPress admin dashboard.', 'wp-admin-themes' ); ?></p>
            
            <h3><?php esc_html_e( 'Enhanced Theme Features', 'wp-admin-themes' ); ?></h3>
            <ul>
                <li><?php esc_html_e( 'Softer, more refined color palette', 'wp-admin-themes' ); ?></li>
                <li><?php esc_html_e( 'Improved spacing and visual hierarchy', 'wp-admin-themes' ); ?></li>
                <li><?php esc_html_e( 'Enhanced focus states for accessibility', 'wp-admin-themes' ); ?></li>
                <li><?php esc_html_e( 'Modern card designs with subtle shadows', 'wp-admin-themes' ); ?></li>
                <li><?php esc_html_e( 'Improved table readability', 'wp-admin-themes' ); ?></li>
                <li><?php esc_html_e( 'Smoother transitions and animations', 'wp-admin-themes' ); ?></li>
                <li><?php esc_html_e( 'Customizable primary color', 'wp-admin-themes' ); ?></li>
            </ul>
        </div>
        
        <style>
        .wp-admin-theme-selector {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 15px 0;
        }
        .wp-admin-theme-option {
            flex: 1;
            min-width: 200px;
            max-width: 300px;
        }
        .wp-admin-theme-option label {
            display: block;
            padding: 15px;
            border: 2px solid #c3c4c7;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .wp-admin-theme-option label:hover {
            border-color: #2271b1;
            background: #f0f6fc;
        }
        .wp-admin-theme-option input:checked + label {
            border-color: #2271b1;
            background: #f0f6fc;
            box-shadow: 0 0 0 1px #2271b1;
        }
        .wp-admin-theme-option .theme-name {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .wp-admin-theme-option .theme-description {
            display: block;
            font-size: 12px;
            color: #646970;
        }
        .wp-admin-color-picker-wrapper {
            max-width: 300px;
        }
        .wp-admin-color-picker {
            width: 100px;
            height: 40px;
            padding: 2px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            cursor: pointer;
        }
        .color-presets {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }
        .color-preset {
            width: 28px;
            height: 28px;
            border: 2px solid transparent;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.15s ease, border-color 0.15s ease;
        }
        .color-preset:hover {
            transform: scale(1.15);
            border-color: rgba(0,0,0,0.2);
        }
        .color-preset.selected {
            border-color: #3c434a;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            $('.color-preset').on('click', function() {
                var color = $(this).data('color');
                $('input[name="wp_admin_primary_color"]').val(color).trigger('change');
                $('.color-preset').removeClass('selected');
                $(this).addClass('selected');
            });
            
            $('input[name="wp_admin_primary_color"]').on('input', function() {
                var color = $(this).val();
                $('.color-preset').each(function() {
                    if ($(this).data('color') === color) {
                        $(this).addClass('selected');
                    } else {
                        $(this).removeClass('selected');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public function enqueue_theme_styles( $hook ) {
        if ( $this->current_theme !== 'enhanced' ) {
            return;
        }
        
        $enhanced_css_path = WP_ADMIN_THEMES_PATH . 'themes/enhanced/enhanced.css';
        
        if ( file_exists( $enhanced_css_path ) ) {
            $version = WP_ADMIN_THEMES_VERSION;
            
            wp_enqueue_style(
                'wp-admin-theme-enhanced',
                WP_ADMIN_THEMES_URL . 'themes/enhanced/enhanced.css',
                array(),
                $version
            );
            
            $custom_css = $this->generate_custom_css();
            wp_add_inline_style( 'wp-admin-theme-enhanced', $custom_css );
        }
    }
    
    private function generate_custom_css() {
        $color = $this->primary_color;
        $hover = $this->adjust_color( $color, -15 );
        $light = $this->adjust_color( $color, 40 );
        $rgb = $this->hex_to_rgb_array( $color );
        
        $custom_css = "
            :root {
                --wp-admin-theme-primary: {$color};
                --wp-admin-theme-primary-hover: {$hover};
                --wp-admin-theme-primary-light: {$light};
                --wp-admin-theme-color-rgb: {$rgb['r']}, {$rgb['g']}, {$rgb['b']};
            }
        ";
        
        return $custom_css;
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
    
    private function hex_to_rgb( $hex, $alpha = 1 ) {
        $rgb = $this->hex_to_rgb_array( $hex );
        return sprintf( 'rgba(%d, %d, %d, %s)', $rgb['r'], $rgb['g'], $rgb['b'], $alpha );
    }
    
    private function hex_to_rgb_array( $hex ) {
        $hex = ltrim( $hex, '#' );
        
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex . $hex;
        }
        
        return array(
            'r' => hexdec( substr( $hex, 0, 2 ) ),
            'g' => hexdec( substr( $hex, 2, 2 ) ),
            'b' => hexdec( substr( $hex, 4, 2 ) )
        );
    }
    
    public function add_body_class( $classes ) {
        if ( $this->current_theme !== 'default' ) {
            $classes .= ' wp-admin-theme-' . esc_attr( $this->current_theme );
        }
        return $classes;
    }
    
    public function ajax_reset_cache() {
        check_ajax_referer( 'wp_admin_themes_reset', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        
        wp_send_json_success();
    }
    
    public static function activate() {
        add_option( 'wp_admin_theme', 'default' );
        add_option( 'wp_admin_primary_color', '#2271b1' );
    }
    
    public static function deactivate() {
        delete_option( 'wp_admin_theme' );
        delete_option( 'wp_admin_primary_color' );
    }
}

register_activation_hook( __FILE__, array( 'WP_Admin_Themes', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Admin_Themes', 'deactivate' ) );

new WP_Admin_Themes();
