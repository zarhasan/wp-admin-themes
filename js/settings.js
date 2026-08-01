/* WP Admin Themes settings page */

(function($) {
    'use strict';

    const strings = (window.wpatData && window.wpatData.i18n) ? window.wpatData.i18n : {};

    const WPATSettings = {
        i18n: {
            saving: strings.saving || 'Saving…',
            save: strings.save || 'Save Changes',
            resetConfirm: strings.resetConfirm || 'Reset all settings to defaults?',
        },

        init: function() {
            this.bindEvents();
            this.initColorPicker();
            this.initThemePreview();
        },

        bindEvents: function() {
            $(document).on('change', '.wpat-theme-option input[type="radio"]', function() {
                WPATSettings.updateThemePreview($(this).val());
            });

            $(document).on('click', '.wpat-color-preset', function(e) {
                e.preventDefault();
                WPATSettings.setColor($(this).data('color'));
            });

            $('#wpat-primary-color').on('input', function() {
                const color = $(this).val();
                if (WPATSettings.isValidHex(color)) {
                    WPATSettings.updateColorPreview(color);
                }
            });

            $('#wpat-settings-form').on('submit', function() {
                WPATSettings.showSavingState();
            });

            $('#wpat-reset-colors').on('click', function(e) {
                e.preventDefault();
                WPATSettings.resetToDefaults();
            });
        },

        initColorPicker: function() {
            if (typeof $.fn.wpColorPicker !== 'function') {
                return;
            }
            $('#wpat-primary-color').wpColorPicker({
                defaultColor: '#2271b1',
                change: function(event, ui) {
                    WPATSettings.updateColorPreview(ui.color.toString());
                },
                clear: function() {
                    WPATSettings.setColor('#2271b1');
                }
            });
        },

        initThemePreview: function() {
            const current = $('.wpat-theme-option input[type="radio"]:checked').val();
            this.updateThemePreview(current);
        },

        setColor: function(color) {
            $('#wpat-primary-color').val(color).trigger('change');
            this.updateColorPreview(color);
            this.updateActivePreset(color);
        },

        updateColorPreview: function(color) {
            $('.wpat-color-preview').css('background-color', color);
            const hoverColor = this.adjustColor(color, -15);
            const root = document.documentElement;
            root.style.setProperty('--wp-admin-theme-color', color);
            root.style.setProperty('--wp-admin-theme-color-hover', hoverColor);
            const rgb = this.hexToRgb(color);
            if (rgb) {
                root.style.setProperty('--wp-admin-theme-color-rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`);
            }
        },

        updateActivePreset: function(color) {
            $('.wpat-color-preset').removeClass('is-active');
            $(`.wpat-color-preset[data-color="${color}"]`).addClass('is-active');
        },

        updateThemePreview: function(theme) {
            $('.wpat-theme-card').removeClass('selected');
            $(`.wpat-theme-option input[value="${theme}"]`).siblings('.wpat-theme-card').addClass('selected');

            const $section = $('#wpat-color-section');
            if (!$section.length) {
                return;
            }
            if (theme === 'modern') {
                $section.prop('hidden', false);
            } else {
                $section.prop('hidden', true);
            }
        },

        resetToDefaults: function() {
            if (!window.confirm(this.i18n.resetConfirm)) {
                return;
            }
            $('#wpat-primary-color').val('#2271b1');
            this.setColor('#2271b1');
            $('input[name="wp_admin_theme"][value="default"]').prop('checked', true).trigger('change');
        },

        showSavingState: function() {
            const submitBtn = $('#wpat-settings-form .button-primary');
            if (!submitBtn.length) {
                return;
            }
            submitBtn.prop('disabled', true).text(this.i18n.saving);
            setTimeout(() => {
                submitBtn.prop('disabled', false).text(this.i18n.save);
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

    $(document).ready(function() {
        WPATSettings.init();
    });

    window.WPATSettings = WPATSettings;

})(jQuery);
