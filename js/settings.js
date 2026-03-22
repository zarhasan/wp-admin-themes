/**
 * WP Admin Themes - Settings Page JavaScript
 */

(function($) {
    'use strict';

    const WPATSettings = {
        init: function() {
            this.bindEvents();
            this.initColorPicker();
            this.initThemePreview();
            this.initTooltips();
        },

        bindEvents: function() {
            // Theme selection
            $('.wpat-theme-option input[type="radio"]').on('change', function() {
                WPATSettings.updateThemePreview($(this).val());
            });

            // Color preset selection
            $('.wpat-color-preset').on('click', function(e) {
                e.preventDefault();
                const color = $(this).data('color');
                WPATSettings.setColor(color);
            });

            // Color input change
            $('#wpat-primary-color').on('input', function() {
                const color = $(this).val();
                if (WPATSettings.isValidHex(color)) {
                    WPATSettings.updateColorPreview(color);
                }
            });

            // Preview mode buttons
            $('.wpat-preview-btn').on('click', function() {
                const mode = $(this).data('mode');
                WPATSettings.switchPreviewMode(mode);
            });

            // Form submission
            $('#wpat-settings-form').on('submit', function(e) {
                WPATSettings.showSavingState();
            });

            // Reset to defaults
            $('#wpat-reset-colors').on('click', function(e) {
                e.preventDefault();
                WPATSettings.resetToDefaults();
            });
        },

        initColorPicker: function() {
            // Initialize WordPress color picker if available
            if (typeof $.fn.wpColorPicker === 'function') {
                $('#wpat-primary-color').wpColorPicker({
                    defaultColor: '#2271b1',
                    change: function(event, ui) {
                        WPATSettings.updateColorPreview(ui.color.toString());
                    },
                    clear: function() {
                        WPATSettings.setColor('#2271b1');
                    }
                });
            }
        },

        initThemePreview: function() {
            const currentTheme = $('.wpat-theme-option input[type="radio"]:checked').val();
            this.updateThemePreview(currentTheme);
        },

        initTooltips: function() {
            $('.wpat-tooltip').tooltip({
                position: { my: 'center bottom', at: 'center top-10' }
            });
        },

        setColor: function(color) {
            $('#wpat-primary-color').val(color).trigger('change');
            this.updateColorPreview(color);
            this.updateActivePreset(color);
        },

        updateColorPreview: function(color) {
            $('.wpat-color-preview').css('background-color', color);
            
            // Generate hover color
            const hoverColor = this.adjustColor(color, -15);
            
            // Update CSS variables
            const root = document.documentElement;
            root.style.setProperty('--wp-admin-theme-color', color);
            root.style.setProperty('--wp-admin-theme-color-hover', hoverColor);
            
            // Convert to RGB
            const rgb = this.hexToRgb(color);
            if (rgb) {
                root.style.setProperty('--wp-admin-theme-color-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`);
            }
            
            // Update header gradient
            const header = $('.wpat-settings-header');
            if (header.length) {
                header.css('background', `linear-gradient(135deg, ${color} 0%, ${hoverColor} 100%)`);
            }
        },

        updateActivePreset: function(color) {
            $('.wpat-color-preset').removeClass('active');
            $(`.wpat-color-preset[data-color="${color}"]`).addClass('active');
        },

        updateThemePreview: function(theme) {
            $('.wpat-theme-card').removeClass('selected');
            $(`.wpat-theme-option input[value="${theme}"]`).siblings('.wpat-theme-card').addClass('selected');
            
            // Show/hide color section
            if (theme === 'enhanced') {
                $('#color-section').slideDown(200);
            } else {
                $('#color-section').slideUp(200);
            }
        },

        switchPreviewMode: function(mode) {
            $('.wpat-preview-btn').removeClass('active');
            $(`.wpat-preview-btn[data-mode="${mode}"]`).addClass('active');
            
            const previewFrame = $('#wpat-preview-frame');
            
            if (mode === 'desktop') {
                previewFrame.css('width', '100%');
            } else if (mode === 'tablet') {
                previewFrame.css('width', '768px');
            } else if (mode === 'mobile') {
                previewFrame.css('width', '375px');
            }
        },

        resetToDefaults: function() {
            if (confirm('Are you sure you want to reset all settings to defaults?')) {
                $('#wpat-primary-color').val('#2271b1');
                this.setColor('#2271b1');
                $('input[name="wp_admin_theme"][value="default"]').prop('checked', true).trigger('change');
            }
        },

        showSavingState: function() {
            const submitBtn = $('.wpat-settings-form .button-primary');
            submitBtn.prop('disabled', true).text('Saving...');
            
            setTimeout(function() {
                submitBtn.prop('disabled', false).text('Save Changes');
            }, 1000);
        },

        isValidHex: function(hex) {
            return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex);
        },

        hexToRgb: function(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : null;
        },

        adjustColor: function(hex, percent) {
            const num = parseInt(hex.replace('#', ''), 16);
            const amt = Math.round(2.55 * percent);
            const R = Math.min(255, Math.max(0, (num >> 16) + amt));
            const G = Math.min(255, Math.max(0, (num >> 8 & 0x00FF) + amt));
            const B = Math.min(255, Math.max(0, (num & 0x0000FF) + amt));
            return '#' + (0x1000000 + R * 0x10000 + G * 0x100 + B).toString(16).slice(1);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WPATSettings.init();
    });

    // Expose to global scope for debugging
    window.WPATSettings = WPATSettings;

})(jQuery);
