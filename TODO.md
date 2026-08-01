# WP Admin Themes — TODO

## 1. Color Settings

Add two independent color settings to the theme settings page:

- **Sidebar color** — controls the admin sidebar/background tone. Default to `#1d2327` for the classic theme.
- **Primary color** — controls the accent/link/brand color used throughout the admin UI. Default to `#096484` for the classic theme.

Requirements:

- Both settings must be user-configurable from the settings page and persist on save.
- Both settings must apply to the **classic** theme.
- Both settings must also apply to the **enhanced** theme (see below).
- The color picker experience should match the existing modern-theme color picker UX (presets, reset, live preview where applicable).

## 2. Enhanced Theme Purpose

Redefine the `enhanced` theme so its purpose is clear:

- It is built on top of the **new WordPress theme** (`wordpress-7.0.2`), not the classic one.
- Its job is to **modernize every table, card, and element** of the WordPress dashboard while staying faithful to the 7.0.2 visual language.
- It must remain an enhancement layer over core 7.x styles — not a wholesale replacement.

## 3. Enhanced Theme Primary Color

Make the enhanced theme's primary color customizable. The new WordPress theme defines its primary color through a set of CSS custom properties that must all stay in sync when the user picks a color:

```css
--wp-admin-theme-color: #3858e9;
--wp-admin-theme-color--rgb: 56, 88, 233;
--wp-admin-theme-color-darker-10: #2145e6;
--wp-admin-theme-color-darker-10--rgb: 33, 68, 230;
--wp-admin-theme-color-darker-20: #183ad6;
```

Requirements:

- When the user changes the enhanced theme's primary color, all related CSS variables (base, rgb, darker-10, darker-10-rgb, darker-20) must be derived and applied consistently across the admin.
- The default values above should be used when the user has not customized the color.
- The customization should be available from the same settings page as the sidebar color.

## Notes

- Keep the default theme and modern theme behavior intact.
- Preserve existing settings page UX conventions.