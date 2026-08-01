# WP Admin Themes — TODO

Complete the following tasks for this plugin. Implement the logic as you see fit; the items below describe **what** needs to be achieved, not how.

## 1. Classic Theme (`wordpress-6.9.5`)

- Deliver a faithful "classic" admin theme experience based on the bundled `wordpress-6.9.5` assets.
- The classic theme must load cleanly and render the admin UI as expected from that WordPress version, without leaking modern (7.x) styles.
- Ensure the new WordPress admin theme is fully dequeued/deregistered when the classic theme is active, so no conflicts remain between core 7.x styles and the classic stylesheets.
- Verify the classic theme works across the standard admin screens (dashboard, list tables, edit screens, settings, media, widgets, nav-menus, site-health, about).

## 2. Enhanced Theme (`wordpress-7.0.2`)

- Rework the current `enhanced` theme so it reads as a refined, enhanced version of the new theme (`wordpress-7.0.2`) — not a re-skin of classic.
- Preserve the 7.0.2 visual language while adding tasteful UX improvements (transitions, focus states, hover behavior, button/menu interactions).
- Keep it compatible with core 7.x styles; the enhanced theme should layer on top of core rather than replace it wholesale.

## 3. Modern Theme — Design & Layout Bug Audit

- Perform a deep analysis of the `modern` theme for design and layout bugs across all admin screens.
- Cover visual regression, spacing, alignment, responsive behavior, color contrast, focus visibility, component states (hover/active/disabled/error), and any inconsistencies with WordPress admin conventions.
- Log each issue as a new row in `sheets/bugs.csv` with the headers: `Status,Severity,Title,Type,File,Description,Suggested fix`.
  - `Status` — e.g. open.
  - `Severity` — blocker / critical / major / minor / trivial.
  - `Type` — design / layout / responsive / accessibility / interaction.
  - `File` — the stylesheet/template/screen where the issue is observable.
  - `Description` — what's wrong and where it's visible (steps to reproduce or screen context).
  - `Suggested fix` — a direction, not a full implementation.

## 4. wordpress.org Guidelines Compliance Audit

- Analyze the plugin (code, assets, enqueue strategy, options, headers, text domain, capability checks, nonce usage, uninstall routine) against the wordpress.org plugin guidelines and coding standards.
- Log every deviation in `sheets/bugs.csv` using the same headers as task 3, with `Type` set to `compliance` and `File` pointing to the relevant PHP/asset file.
- Cover the usual review hotspots: proper prefixing, sanitization/escaping, capability checks, nonce verification, no direct file access, asset enqueueing rules, license/header correctness, and clean uninstall.

## Notes

- Prefer faithful, production-quality implementations over quick fixes.
- Do not break the existing settings page UX or the default theme.