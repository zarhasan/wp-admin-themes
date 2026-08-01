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
            resetSidebar: strings.resetSidebar || 'Reset sidebar color?',
            resetPrimary: strings.resetPrimary || 'Reset primary color?',
        },
        channels: ['sidebar', 'primary'],

        init: function() {
            this.bindEvents();
            this.initColorPickers();
            this.initThemePreview();
        },

        bindEvents: function() {
            $(document).on('change', '.wpat-theme-option input[type="radio"]', function() {
                WPATSettings.updateThemePreview($(this).val());
            });

            $(document).on('click', '.wpat-color-preset', function(e) {
                e.preventDefault();
                const channel = $(this).data('channel');
                WPATSettings.setColor(channel, $(this).data('color'));
            });

            $(document).on('input', '.wpat-color-input', function() {
                const channel = $(this).data('channel');
                const color = $(this).val();
                if (channel && WPATSettings.isValidHex(color)) {
                    WPATSettings.updateColorPreview(channel, color);
                }
            });

            $(document).on('click', '.wpat-reset-color', function(e) {
                e.preventDefault();
                const channel = $(this).data('channel');
                WPATSettings.resetChannel(channel);
            });

            $('#wpat-settings-form').on('submit', function() {
                WPATSettings.showSavingState();
            });
        },

        initColorPickers: function() {
            if (typeof $.fn.wpColorPicker !== 'function') {
                return;
            }
            const theme = this.getCurrentTheme();
            this.channels.forEach((channel) => {
                const defaultColor = this.getDefault(channel, theme);
                $('#wpat-' + channel + '-color').wpColorPicker({
                    defaultColor: defaultColor,
                    change: function(event, ui) {
                        WPATSettings.updateColorPreview(channel, ui.color.toString());
                    },
                    clear: function() {
                        WPATSettings.setColor(channel, defaultColor);
                    }
                });
            });
        },

        initThemePreview: function() {
            this.updateThemePreview(this.getCurrentTheme());
        },

        getCurrentTheme: function() {
            const checked = $('.wpat-theme-option input[type="radio"]:checked').val();
            return checked || 'default';
        },

        getDefault: function(channel, theme) {
            theme = theme || this.getCurrentTheme();
            if (this.defaults[theme] && this.defaults[theme][channel]) {
                return this.defaults[theme][channel];
            }
            return '';
        },

        setColor: function(channel, color) {
            const $input = $('#wpat-' + channel + '-color');
            $input.val(color).trigger('change');
            this.updateColorPreview(channel, color);
            this.updateActivePreset(channel, color);
        },

        updateColorPreview: function(channel, color) {
            $('.wpat-color-input-wrapper[data-channel="' + channel + '"] .wpat-color-preview').css('background-color', color);
            const root = document.documentElement;
            const cssVar = '--wpat-' + channel;
            root.style.setProperty(cssVar, color);
            const rgb = this.hexToRgb(color);
            if (rgb) {
                root.style.setProperty(cssVar + '-rgb', rgb.r + ', ' + rgb.g + ', ' + rgb.b);
            }
        },

        updateActivePreset: function(channel, color) {
            $('.wpat-color-preset[data-channel="' + channel + '"]').removeClass('is-active');
            $('.wpat-color-preset[data-channel="' + channel + '"][data-color="' + color + '"]').addClass('is-active');
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
                    this.channels.forEach((channel) => {
                        const defaultColor = this.getDefault(channel, theme);
                        const $input = $('#wpat-' + channel + '-color');
                        $input.data('default-color', defaultColor);
                        if (typeof $.fn.wpColorPicker === 'function') {
                            try {
                                $input.wpColorPicker('option', 'defaultColor', defaultColor);
                            } catch (e) {}
                        }
                    });
                }
            }
        },

        resetChannel: function(channel) {
            const theme = this.getCurrentTheme();
            const defaultColor = this.getDefault(channel, theme);
            if (!defaultColor) {
                return;
            }
            if (!window.confirm(this.i18n.resetSidebar === undefined ? this.i18n.resetConfirm : (channel === 'sidebar' ? this.i18n.resetSidebar : this.i18n.resetPrimary))) {
                return;
            }
            this.setColor(channel, defaultColor);
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
