# ACF Fields Generation Prompt

Use this prompt when generating or modifying ACF fields in `inc/acf.php`. It enforces consistent naming conventions, structural patterns, and field defaults across all field groups.

---

## 1. File Structure

All ACF fields are registered in `inc/acf.php` using the `acf/include_fields` action hook:

```php
add_action( 'acf/include_fields', function() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    // Register field groups here via acf_add_local_field_group()
} );
```

Options pages are registered separately at the top of the file using `acf_add_options_page()` (before the hook), guarded by `function_exists( 'acf_add_options_page' )`.

---

## 2. Key Naming Convention (MANDATORY)

### Field Group Keys

Format: `group_{descriptive_snake_case_name}`

| Example | Description |
|---|---|
| `group_flexible_template` | Flexible content page template |
| `group_theme_settings` | Global theme settings (options page) |
| `group_team_member_profile` | Team member CPT fields |
| `group_post_faq` | Post FAQ fields |

**NEVER** use hex-based keys like `group_68ce8cfe372b0`. Always use human-readable descriptive keys prefixed with `group_`.

### Field Keys

Format: `field_{context}_{field_name}`

The `context` is the layout or group name in snake_case. For top-level options page fields, the context is the tab or section name.

| Example | Breakdown |
|---|---|
| `field_hero_title` | context=hero, name=title |
| `field_hero_subtitle` | context=hero, name=subtitle |
| `field_hero_links` | context=hero, name=links |
| `field_certificates_stats_label` | context=certificates_stats, name=label |
| `field_footer_copyright` | context=footer, name=copyright |
| `field_social_facebook` | context=social, name=facebook |
| `field_primary_color` | context=primary, name=color (top-level) |

**NEVER** use hex-based keys like `field_68ce8cffb2dc4`. Always use descriptive, hierarchical keys.

### Layout Keys (Flexible Content)

Format: `layout_{descriptive_snake_case_name}`

| Example | Description |
|---|---|
| `layout_hero` | Hero section |
| `layout_text` | Text section |
| `layout_call_to_action` | CTA section |
| `layout_grid` | Grid section |
| `layout_faqs` | FAQs section |
| `layout_blog` | Blog section |
| `layout_form` | Form section |
| `layout_space` | Spacer/divider section |
| `layout_table` | Table section |
| `layout_logos` | Logo gallery section |
| `layout_testimonials` | Testimonials section |
| `layout_locations` | Office locations section |
| `layout_stats` | Statistics section |
| `layout_team` | Team members section |
| `layout_gallery` | Image gallery section |
| `layout_information` | Information section |
| `layout_callout` | Callout section |
| `layout_about` | About section |
| `layout_process` | Process section |
| `layout_services` | Services section |
| `layout_certificates` | Certificates section |
| `layout_compliance` | Compliance section |
| `layout_our_mission` | Mission section |
| `layout_grid_videos` | Video grid section |
| `layout_leanpress_forms` | LearnPress forms section |
| `layout_links` | Links section |
| `layout_points` | Points section |

**NEVER** use hex-based keys like `layout_69c2789906d89`.

### Field `name` Convention

Format: `snake_case` — always lowercase, words separated by underscores.

| Good | Bad |
|---|---|
| `margin_bottom` | `marginBottom` |
| `heading_level` | `headingLevel` |
| `media_type` | `mediaType` |
| `service_links` | `serviceLinks` |
| `background_color` | `backgroundColor` |
| `cta_text` | `ctaText` |
| `custom_size` | `customSize` |
| `divider_style` | `dividerStyle` |

### Layout `name` Convention (Flexible Content)

Format: `snake_case` — must match the descriptive part of the layout key.

| layout key | layout name | layout label |
|---|---|---|
| `layout_hero` | `hero` | `Hero` |
| `layout_call_to_action` | `call_to_action` | `Call To Action` |
| `layout_grid_videos` | `grid_videos` | `Videos Grid` |
| `layout_our_mission` | `our_mission` | `Our Mission` |

---

## 3. Field Defaults by Type

When adding fields, apply these defaults unless the field needs a different value. Omit default-valued properties to keep code concise (ACF merges in defaults automatically).

### text

```php
array(
    'key'   => 'field_{context}_{name}',
    'label' => 'Human Readable Label',
    'name'  => 'snake_case_name',
    'type'  => 'text',
),
```

Only add `default_value`, `placeholder`, `maxlength`, `prepend`, `append`, `readonly`, or `disabled` when needed.

### textarea

```php
array(
    'key'   => 'field_{context}_{name}',
    'label' => 'Human Readable Label',
    'name'  => 'snake_case_name',
    'type'  => 'textarea',
),
```

Only add `rows`, `new_lines`, `placeholder`, `maxlength` when needed.

### wysiwyg

```php
array(
    'key'       => 'field_{context}_{name}',
    'label'     => 'Human Readable Label',
    'name'      => 'snake_case_name',
    'type'      => 'wysiwyg',
    'tabs'      => 'all',
    'toolbar'   => 'full',
    'media_upload' => 1,
),
```

- Use `'toolbar' => 'basic'` for sub-fields inside repeaters (shorter content).
- Use `'toolbar' => 'full'` for top-level/standalone content areas.
- Add `'delay' => 0` only when explicitly needed.

### number

```php
array(
    'key'   => 'field_{context}_{name}',
    'label' => 'Human Readable Label',
    'name'  => 'snake_case_name',
    'type'  => 'number',
),
```

Only add `min`, `max`, `step`, `default_value`, `placeholder`, `prepend`, `append` when needed.

### image

```php
array(
    'key'           => 'field_{context}_{name}',
    'label'         => 'Human Readable Label',
    'name'          => 'snake_case_name',
    'type'          => 'image',
    'return_format' => 'array',
    'preview_size'  => 'medium',
),
```

- Use `'preview_size' => 'thumbnail'` for small sub-fields (e.g., repeater items like team member photos).
- Use `'preview_size' => 'medium'` as the default for most images.
- Only add `library`, `min_width`, `min_height`, `min_size`, `max_width`, `max_height`, `max_size`, `mime_types` when needed.

### file

```php
array(
    'key'           => 'field_{context}_{name}',
    'label'         => 'Human Readable Label',
    'name'          => 'snake_case_name',
    'type'          => 'file',
    'return_format' => 'array',
),
```

Only add `library`, `min_size`, `max_size`, `mime_types` when needed. Common `mime_types` values: `'mp4'`, `'vtt'`, `'mp4,webm,ogg'`.

### link

```php
array(
    'key'           => 'field_{context}_{name}',
    'label'         => 'Human Readable Label',
    'name'          => 'snake_case_name',
    'type'          => 'link',
    'return_format' => 'array',
),
```

Always use `'return_format' => 'array'` so the link URL, title, and target are accessible.

### url

```php
array(
    'key'   => 'field_{context}_{name}',
    'label' => 'Human Readable Label',
    'name'  => 'snake_case_name',
    'type'  => 'url',
),
```

Only add `placeholder` when needed.

### email

```php
array(
    'key'   => 'field_{context}_{name}',
    'label' => 'Human Readable Label',
    'name'  => 'snake_case_name',
    'type'  => 'email',
),
```

### button_group

```php
array(
    'key'           => 'field_{context}_{name}',
    'label'         => 'Human Readable Label',
    'name'          => 'snake_case_name',
    'type'          => 'button_group',
    'choices'       => array(
        'value_key' => 'Human Label',
    ),
    'default_value' => 'value_key',
    'return_format' => 'value',
    'layout'        => 'horizontal',
),
```

- `choices` keys are always `snake_case` (e.g., `'full_width'`, `'alt'`, `'default'`).
- `return_format` is always `'value'`.
- `layout` is always `'horizontal'`.
- Always provide a `default_value`.

### color_picker

```php
array(
    'key'           => 'field_{context}_{name}',
    'label'         => 'Human Readable Label',
    'name'          => 'snake_case_name',
    'type'          => 'color_picker',
),
```

Only add `default_value` when needed (e.g., `'#C41E3A'`).

### true_false

```php
array(
    'key'           => 'field_{context}_{name}',
    'label'         => 'Human Readable Label',
    'name'          => 'snake_case_name',
    'type'          => 'true_false',
    'default_value' => 0,
),
```

Only add `message` and `ui` when needed.

### repeater

```php
array(
    'key'          => 'field_{context}_{name}',
    'label'        => 'Human Readable Label',
    'name'         => 'snake_case_name',
    'type'         => 'repeater',
    'layout'       => 'table',
    'button_label' => 'Add Row',
    'sub_fields'   => array(
        // ... sub-field arrays
    ),
),
```

- Always use `'layout' => 'table'`.
- Customize `button_label` when appropriate (e.g., `'Add Grid Item'`, `'Add Point'`, `'Add Link'`).
- Only add `min`, `max`, `collapsed`, `rows_per_page` when needed.
- Sub-fields inside repeaters MUST include `'parent_repeater' => 'field_{parent_key}'` when the repeater is nested.

### gallery

```php
array(
    'key'           => 'field_{context}_{name}',
    'label'         => 'Human Readable Label',
    'name'          => 'snake_case_name',
    'type'          => 'gallery',
    'return_format' => 'array',
    'preview_size'  => 'medium',
    'insert'        => 'append',
),
```

Only add `library`, `min`, `max`, dimension/size restrictions, `mime_types` when needed.

### tab

```php
array(
    'key'       => 'field_{context}_tab',
    'label'     => 'Tab Label',
    'name'      => '',
    'type'      => 'tab',
    'placement' => 'left',
),
```

- `name` is always empty string for tabs.
- `placement` is always `'left'` for options page tabs.

### group

```php
array(
    'key'        => 'field_{context}_{name}',
    'label'      => 'Human Readable Label',
    'name'       => 'snake_case_name',
    'type'       => 'group',
    'layout'     => 'block',
    'sub_fields' => array(
        // ... sub-field arrays
    ),
),
```

### icon_picker

```php
array(
    'key'           => 'field_{context}_{name}',
    'label'         => 'Human Readable Label',
    'name'          => 'snake_case_name',
    'type'          => 'icon_picker',
    'return_format' => 'array',
),
```

Only add `tabs`, `library`, `default_value` when needed.

### taxonomy

```php
array(
    'key'           => 'field_{context}_{name}',
    'label'         => 'Human Readable Label',
    'name'          => 'snake_case_name',
    'type'          => 'taxonomy',
    'taxonomy'      => 'category',
    'return_format' => 'id',
),
```

Only add `field_type`, `allow_null`, `add_term`, `load_save_terms` when needed.

### user

```php
array(
    'key'           => 'field_{context}_{name}',
    'label'         => 'Human Readable Label',
    'name'          => 'snake_case_name',
    'type'          => 'user',
    'return_format' => 'id',
),
```

Only add `role`, `allow_null`, `multiple` when needed.

---

## 4. Conditional Logic Pattern

Conditional logic follows this array structure:

```php
'conditional_logic' => array(
    array(
        array(
            'field'    => 'field_{sibling_field_key}',
            'operator' => '==',
            'value'    => 'target_value',
        ),
    ),
),
```

- The outer array represents OR groups; inner arrays are AND conditions within each group.
- Most conditions use `'operator' => '=='` against a sibling field's key.
- Always reference the sibling field's **key** (not name).
- Set `'conditional_logic' => 0` when no conditional logic applies.

---

## 5. Field Group Location Rules

### Page Template (Flexible Content)

```php
'location' => array(
    array(
        array(
            'param'    => 'page_template',
            'operator' => '==',
            'value'    => 'page-templates/flexible.php',
        ),
    ),
),
```

### Options Page (Global Settings)

```php
'location' => array(
    array(
        array(
            'param'    => 'options_page',
            'operator' => '==',
            'value'    => 'global-settings',
        ),
    ),
),
```

### Custom Post Type

```php
'location' => array(
    array(
        array(
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'team_member',
        ),
    ),
),
```

### Post Type (Standard)

```php
'location' => array(
    array(
        array(
            'param'    => 'post_type',
            'operator' => '==',
            'value'    => 'post',
        ),
    ),
),
```

---

## 6. Field Group Settings Defaults

```php
acf_add_local_field_group( array(
    'key'                   => 'group_{descriptive_name}',
    'title'                 => 'Human Readable Title',
    'fields'                => array( /* ... */ ),
    'location'              => array( /* ... */ ),
    'menu_order'            => 0,
    'position'              => 'normal',
    'style'                 => 'default',
    'label_placement'       => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen'        => '',
    'active'                => true,
    'description'           => '',
    'show_in_rest'          => 0,
) );
```

- Set `'show_in_rest' => 1` for CPT field groups that need REST API / block editor access.
- Set `'hide_on_screen' => array( 'the_content' )` for flexible template groups that replace the editor.

---

## 7. Minimal Field Style

Omit properties that match ACF defaults to keep code concise. ACF auto-fills missing settings. For example, a simple text field:

```php
array(
    'key'   => 'field_hero_title',
    'label' => 'Title',
    'name'  => 'title',
    'type'  => 'text',
),
```

NOT:

```php
array(
    'key'             => 'field_hero_title',
    'label'           => 'Title',
    'name'            => 'title',
    'aria-label'      => '',
    'type'            => 'text',
    'instructions'    => '',
    'required'        => 0,
    'conditional_logic' => 0,
    'wrapper'         => array(
        'width'  => '',
        'class'  => '',
        'id'     => '',
    ),
    'default_value'   => '',
    'maxlength'       => '',
    'allow_in_bindings' => 0,
    'placeholder'     => '',
    'prepend'         => '',
    'append'          => '',
),
```

Only include non-default properties when they have meaningful values.

---

## 8. Common Layout Sub-Field Patterns

Most flexible content layouts share common sub-fields. Follow these patterns:

### Title (present in nearly all layouts)

```php
array(
    'key'   => 'field_{layout_name}_title',
    'label' => 'Title',
    'name'  => 'title',
    'type'  => 'text',
),
```

### Description (rich text)

```php
array(
    'key'     => 'field_{layout_name}_description',
    'label'   => 'Description',
    'name'    => 'description',
    'type'    => 'wysiwyg',
    'tabs'    => 'all',
    'toolbar' => 'full',
    'media_upload' => 1,
),
```

### Image

```php
array(
    'key'           => 'field_{layout_name}_image',
    'label'         => 'Image',
    'name'          => 'image',
    'type'          => 'image',
    'return_format' => 'array',
    'preview_size'  => 'medium',
),
```

### Link

```php
array(
    'key'           => 'field_{layout_name}_link',
    'label'         => 'Link',
    'name'          => 'link',
    'type'          => 'link',
    'return_format' => 'array',
),
```

### Type Variant Selector (button_group)

```php
array(
    'key'           => 'field_{layout_name}_type',
    'label'         => 'Type',
    'name'          => 'type',
    'type'          => 'button_group',
    'choices'       => array(
        'default' => 'Default',
        'alt'     => 'Alt',
    ),
    'default_value' => 'default',
    'return_format' => 'value',
    'layout'        => 'horizontal',
),
```

---

## 9. ACF API Reference

### Core Functions Used in This Project

| Function | Purpose | Docs |
|---|---|---|
| `acf_add_local_field_group( $array )` | Register a field group and its fields via PHP | https://www.advancedcustomfields.com/resources/register-fields-via-php/ |
| `acf_add_options_page( $array )` | Register an options page for global settings | https://www.advancedcustomfields.com/resources/acf_add_options_page/ |
| `acf_add_options_sub_page( $array )` | Register a child options page | https://www.advancedcustomfields.com/resources/acf_add_options_sub_page/ |
| `acf_add_local_field( $array )` | Register a single field independently (requires `parent` key) | https://www.advancedcustomfields.com/resources/register-fields-via-php/ |

### Template Retrieval Functions

| Function | Purpose | Docs |
|---|---|---|
| `get_field( $selector [, $post_id] )` | Get a field value | https://www.advancedcustomfields.com/resources/get_field/ |
| `the_field( $selector [, $post_id] )` | Echo a field value | https://www.advancedcustomfields.com/resources/the_field/ |
| `get_fields( [$post_id] )` | Get all field values for a post | https://www.advancedcustomfields.com/resources/get_fields/ |
| `get_field_object( $selector [, $post_id] )` | Get field settings + value | https://www.advancedcustomfields.com/resources/get_field_object/ |
| `have_rows( $selector [, $post_id] )` | Loop through repeater/flexible content rows | https://www.advancedcustomfields.com/resources/have_rows/ |
| `the_row()` | Setup the current row inside a `have_rows()` loop | https://www.advancedcustomfields.com/resources/have_rows/ |
| `get_sub_field( $selector )` | Get a sub-field value within a `have_rows()` loop | https://www.advancedcustomfields.com/resources/get_sub_field/ |
| `the_sub_field( $selector )` | Echo a sub-field value within a `have_rows()` loop | https://www.advancedcustomfields.com/resources/the_sub_field/ |
| `get_row_layout()` | Get the layout name in a flexible content `have_rows()` loop | https://www.advancedcustomfields.com/resources/get_row_layout/ |
| `get_row_index()` | Get the 1-based index of the current row | https://www.advancedcustomfields.com/resources/get_row_index/ |

### Options Page Retrieval

When retrieving values from an options page, pass `'option'` as the `$post_id`:

```php
$value = get_field( 'header_logo', 'option' );
```

### Action Hooks

| Hook | Purpose | Docs |
|---|---|---|
| `acf/include_fields` | Recommended hook for registering local field groups (ACF 6.x+) | Used in this project |
| `acf/init` | General ACF initialization hook; use for `acf_add_options_page()` | https://www.advancedcustomfields.com/resources/acf_add_options_page/ |

### Key Rules

- Every `key` must be unique across the entire WordPress instance. If two fields share a key, the later one overwrites the former.
- Field groups and fields registered via PHP are **not** editable from the ACF admin UI.
- The `key` prefix convention: `group_` for field groups, `field_` for fields, `layout_` for flexible content layouts.
- Fields inside repeaters need `parent_repeater` pointing to the repeater's key.
- Fields inside groups need `parent` pointing to the group's key.

---

## 10. Complete Example: Adding a New Flexible Content Layout

To add a new "Pricing" layout to the existing flexible content field group:

```php
'layout_pricing' => array(
    'key'        => 'layout_pricing',
    'name'       => 'pricing',
    'label'      => 'Pricing',
    'display'    => 'block',
    'sub_fields' => array(
        array(
            'key'   => 'field_pricing_title',
            'label' => 'Title',
            'name'  => 'title',
            'type'  => 'text',
        ),
        array(
            'key'     => 'field_pricing_description',
            'label'   => 'Description',
            'name'    => 'description',
            'type'    => 'wysiwyg',
            'tabs'    => 'all',
            'toolbar' => 'full',
            'media_upload' => 1,
        ),
        array(
            'key'           => 'field_pricing_type',
            'label'         => 'Type',
            'name'          => 'type',
            'type'          => 'button_group',
            'choices'       => array(
                'default' => 'Default',
                'alt'     => 'Alt',
            ),
            'default_value' => 'default',
            'return_format' => 'value',
            'layout'        => 'horizontal',
        ),
        array(
            'key'           => 'field_pricing_plans',
            'label'         => 'Plans',
            'name'          => 'plans',
            'type'          => 'repeater',
            'layout'        => 'table',
            'button_label'  => 'Add Plan',
            'sub_fields'    => array(
                array(
                    'key'   => 'field_pricing_plan_title',
                    'label' => 'Title',
                    'name'  => 'title',
                    'type'  => 'text',
                ),
                array(
                    'key'     => 'field_pricing_plan_description',
                    'label'   => 'Description',
                    'name'    => 'description',
                    'type'    => 'wysiwyg',
                    'tabs'    => 'all',
                    'toolbar' => 'basic',
                    'media_upload' => 0,
                ),
                array(
                    'key'   => 'field_pricing_plan_price',
                    'label' => 'Price',
                    'name'  => 'price',
                    'type'  => 'text',
                ),
                array(
                    'key'           => 'field_pricing_plan_link',
                    'label'         => 'Link',
                    'name'          => 'link',
                    'type'          => 'link',
                    'return_format' => 'array',
                ),
                array(
                    'key'           => 'field_pricing_plan_features',
                    'label'         => 'Features',
                    'name'          => 'features',
                    'type'          => 'repeater',
                    'layout'        => 'table',
                    'button_label'  => 'Add Feature',
                    'sub_fields'    => array(
                        array(
                            'key'   => 'field_pricing_plan_feature',
                            'label' => 'Feature',
                            'name'  => 'feature',
                            'type'  => 'text',
                        ),
                    ),
                ),
            ),
        ),
    ),
    'min' => '',
    'max' => '',
),
```

---

## 11. Checklist Before Adding Fields

- [ ] Field group key starts with `group_` and uses descriptive snake_case (no hex hashes)
- [ ] Field keys start with `field_` and follow `field_{context}_{name}` pattern (no hex hashes)
- [ ] Layout keys start with `layout_` and use descriptive snake_case (no hex hashes)
- [ ] All `name` values use snake_case
- [ ] Layout `name` matches the descriptive part of its `key`
- [ ] Layout `label` is Human Readable Title Case
- [ ] `return_format` is set to `'array'` for image, file, link, gallery, icon_picker fields
- [ ] `return_format` is set to `'value'` for button_group fields
- [ ] Repeater sub-fields use `'layout' => 'table'`
- [ ] Button group fields use `'layout' => 'horizontal'`
- [ ] No redundant default-value properties that ACF fills automatically
- [ ] Conditional logic references sibling **keys** (not names)
- [ ] Options page fields use proper tab grouping with `'placement' => 'left'`
- [ ] New field keys do not collide with existing keys in the file
