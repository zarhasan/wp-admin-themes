/* WP Admin Themes settings page */

(function($) {
    'use strict';

    const data = window.wpatData || {};
    const strings = data.i18n || {};

    const WPATSettings = {
        defaults: data.defaults || {},
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
                WPATSettings.resetToThemeDefault();
            });
        },

        initColorPicker: function() {
            if (typeof $.fn.wpColorPicker !== 'function') {
                return;
            }
            const theme = this.getCurrentTheme();
            const defaultColor = this.defaults[theme] || this.defaults.modern || '#2271b1';
            $('#wpat-primary-color').wpColorPicker({
                defaultColor: defaultColor,
                change: function(event, ui) {
                    WPATSettings.updateColorPreview(ui.color.toString());
                },
                clear: function() {
                    WPATSettings.setColor(defaultColor);
                }
            });
        },

        initThemePreview: function() {
            const current = this.getCurrentTheme();
            this.updateThemePreview(current);
        },

        getCurrentTheme: function() {
            const checked = $('.wpat-theme-option input[type="radio"]:checked').val();
            return checked || 'default';
        },

        setColor: function(color) {
            $('#wpat-primary-color').val(color).trigger('change');
            this.updateColorPreview(color);
            this.updateActivePreset(color);
        },

        updateColorPreview: function(color) {
            $('.wpat-color-preview').css('background-color', color);
            const root = document.documentElement;
            root.style.setProperty('--wp-admin-theme-color', color);
            const rgb = this.hexToRgb(color);
            if (rgb) {
                root.style.setProperty('--wp-admin-theme-color--rgb', `${rgb.r}, ${rgb.g}, ${rgb.b}`);
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
            if ($section.length) {
                if (theme === 'default') {
                    $section.prop('hidden', true);
                } else {
                    $section.prop('hidden', false);
                    const defaultColor = this.defaults[theme] || '#2271b1';
                    const $input = $('#wpat-primary-color');
                    if (!$input.val() || $input.data('was-empty')) {
                        this.setColor(defaultColor);
                        $input.data('was-empty', true);
                    } else {
                        this.setColor($input.val());
                    }
                    $input.data('default-color', defaultColor);
                    if (typeof $.fn.wpColorPicker === 'function') {
                        try {
                            $input.wpColorPicker('option', 'defaultColor', defaultColor);
                        } catch (e) {}
                    }
                }
            }
        },

        resetToThemeDefault: function() {
            const theme = this.getCurrentTheme();
            const defaultColor = this.defaults[theme];
            if (!defaultColor) {
                if (!window.confirm(this.i18n.resetConfirm)) {
                    return;
                }
                $('input[name="wp_admin_theme"][value="default"]').prop('checked', true).trigger('change');
                return;
            }
            this.setColor(defaultColor);
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
        }
    };

    $(document).ready(function() {
        WPATSettings.init();
    });

    window.WPATSettings = WPATSettings;

})(jQuery);
