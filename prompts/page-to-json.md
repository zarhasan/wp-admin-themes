# Page to JSON Conversion Prompt

Convert Accessibility Partners website pages into ACF flexible content JSON files for import into WordPress.

## Workflow

When given a URL:

1. **Fetch the page** using webfetch with format: html
2. **Extract all content** including: page title, section headings, paragraphs, lists, links, CTA buttons, FAQs, and **images** (src paths and alt text)
3. **Map content** to the appropriate ACF layout types (see Section Reference below)
4. **Create the JSON file** following the structure and rules documented here
5. **Save** to `src/json/{slug}.json`

---

## Content Fidelity — CRITICAL

> **NEVER add, remove, rephrase, summarize, reorder, or otherwise alter ANY content from the source page.**

The JSON must be a **1:1 faithful representation** of the source page. The only transformation is structural — mapping existing page content into ACF flexible content format. The words, images, and links must remain untouched.

1. **Do not add content** — No invented headings, descriptions, list items, FAQ answers, or any text not on the source page.
2. **Do not remove content** — Every visible piece of content must appear in the JSON.
3. **Do not rephrase or rewrite** — Copy text exactly as it appears. Do not fix grammar or "improve" copy.
4. **Do not summarize or condense** — Five paragraphs on the page means five paragraphs in the JSON.
5. **Do not reorder content** — Sections and items must appear in the same order as the source page.
6. **Preserve HTML formatting** — Keep `<strong>`, `<em>`, `<a>`, `<ul>`, `<ol>`, `<li>`, `<h4>`, and other inline/block HTML tags exactly as they appear.

---

## Top-Level JSON Structure

```json
{
  "page_title": "Page Title",
  "page_template": "page-templates/flexible.php",
  "source_url": "https://accessibilitypartners.ca",
  "sections": [ ... ]
}
```

| Field | Required | Description |
|-------|----------|-------------|
| `page_title` | Yes | The H1 heading of the page |
| `page_template` | Yes | Always `"page-templates/flexible.php"` (use this exact value) |
| `source_url` | Yes when images exist | Base URL for resolving relative image paths (e.g., `"https://accessibilitypartners.ca"`) |
| `sections` | Yes | Array of ACF flexible content sections |

---

## Section Type Selection Guide

Use this guide to decide which ACF layout to use for each piece of page content:

| Page Content Pattern | ACF Layout | `type` Value |
|----------------------|------------|--------------|
| Page hero banner with title, subtitle, description, CTA buttons, and/or image | `hero` | `"home"` for homepage, `"page"` for interior pages |
| Simple centered heading, optionally with a short description below | `section_title` | — |
| Heading + rich WYSIWYG body text (paragraphs, lists, links) | `text` | `"default"`, `"alt"`, or `"heavy"` |
| Checklist/info section with heading and items that have title+description, with optional image | `information` | `"default"` (image right) or `"alt"` (image left) |
| Grid of cards (with images, titles, descriptions) | `grid` | Varies — see Grid Types |
| CTA banner with title, description, and a button | `call_to_action` | `"default"` or `"simple"` |
| Expandable FAQ accordion | `faqs` | — |
| About section with image, description, items, and links | `about` | `"default"` or `"bullet-list"` |
| Contact form section | `contact` | — |
| Bulleted/checked point list without image | `points` | `"default"` or `"grid"` |
| Row of link buttons to other pages | `links` | — |
| Service features section with checkmark list and image | `services` | — |
| Mission statement with quote and service cards | `our_mission` | — |
| Process flow with title, subtitle, and diagram image | `process` | — |
| Certificate/stats section | `certificates` | — |
| Compliance section with checklist, score, and image/video | `compliance` | — |
| Testimonial quote | `testimonials` | — |
| Team member cards | `team` | — |
| Client/partner logo grid | `logos` | — |
| Statistics display | `stats` | — |
| Table with headers and data | `table` | — |
| Video grid | `grid_videos` | — |
| Blog post listing | `blog` | — |
| Image gallery | `gallery` | — |
| Office locations with map | `locations` | — |
| Embedded form | `form` | — |
| Freeform WYSIWYG content block | `callout` | — |
| Spacing divider | `space` | — |

### Grid Types

The `grid` layout uses the `type` field to control rendering. Possible values:

| Type | Use When |
|------|----------|
| `cards` | Standard card grid with image, title, description |
| `service-cards` | Service offering cards (title + description, often without images) |
| `clickable-cards` | Fully clickable cards |
| `floating-cards` | Elevated card styling |
| `compliance-cards` | Compliance-related cards |
| `information-cards` | Information display cards |
| `incentives` | Incentive/offer display |
| `basic` | Simple bordered cards with icons |
| `alt` | Alternative grid styling |
| `highlight` | Emphasized/highlighted cards |
| `textual` | Text-focused layout |

---

## Section Reference — Complete Field Schemas

### hero

```json
{
  "acf_fc_layout": "hero",
  "type": "page",
  "subtitle": "",
  "title": "Page Title",
  "description": "<p>Description text</p>",
  "image": {
    "url": "/wp-content/uploads/2024/01/hero.jpg",
    "alt": "Hero image description"
  },
  "links": [
    {
      "link": {
        "title": "Button Text",
        "url": "/contact-us/",
        "target": ""
      }
    }
  ],
  "media_type": "image",
  "video": "",
  "transcript": ""
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `type` | string | Yes | `"home"` or `"page"` |
| `subtitle` | string | No | Small label above the title |
| `title` | string | Yes | Main heading |
| `description` | wysiwyg | Yes | Body text, can contain HTML |
| `image` | image | No | Hero image (use image format below) |
| `links` | repeater | No | Array of link objects, each with `{link: {title, url, target}}` |
| `media_type` | string | No | `"image"` or `"video"` |
| `video` | string | No | Video file URL when media_type is video |
| `transcript` | wysiwyg | No | Video transcript for accessibility |
| `service_links` | repeater | No | Links in bottom banner (home hero only) |

### section_title

```json
{
  "acf_fc_layout": "section_title",
  "title": "Section Heading",
  "description": "Optional subtitle text"
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `title` | string | Yes | Section heading |
| `description` | textarea | No | Subtitle below heading |

### text

```json
{
  "acf_fc_layout": "text",
  "type": "default",
  "title": "Section Title",
  "description": "<p>HTML content with paragraphs</p>"
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `type` | string | No | `"default"`, `"alt"`, or `"heavy"` |
| `title` | string | No | Section heading (omit if page content has no heading here) |
| `description` | wysiwyg | Yes | Full HTML body content |

**IMPORTANT**: Always use `description` (never `content`) for the text body field.

### information

```json
{
  "acf_fc_layout": "information",
  "type": "default",
  "title": "Section Title",
  "description": "<p>Optional intro text</p>",
  "items": [
    {
      "title": "Item Heading",
      "description": "Item description text"
    }
  ],
  "link": {
    "title": "Link Text",
    "url": "/page/",
    "target": ""
  },
  "image": {
    "url": "/wp-content/uploads/2024/01/info.jpg",
    "alt": "Information section image"
  }
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `type` | string | Yes | `"default"` (image right) or `"alt"` (image left) |
| `title` | string | Yes | Section heading |
| `description` | wysiwyg | No | Intro text above items |
| `items` | repeater | No | Checklist items — see item formats below |
| `link` | link | No | Single CTA link for the section |
| `image` | image | No | Side image |

**Item formats for `items`**:

Use **`{title, description}`** when items have a heading and body:
```json
{"title": "Item Heading", "description": "Item description text"}
```

Use **`{item}`** when items are simple bullet/checkmark points without a heading:
```json
{"item": "Simple checkmark text without a heading"}
```

### grid

```json
{
  "acf_fc_layout": "grid",
  "type": "cards",
  "grid_size": 3,
  "title": "Grid Section Title",
  "description": "<p>Optional intro text</p>",
  "items": [
    {
      "image": {
        "url": "/wp-content/uploads/2024/01/card.jpg",
        "alt": "Card image description"
      },
      "title": "Card Title",
      "description": "<p>Card description</p>",
      "link": "/relative-url/"
    }
  ],
  "footer_description": "<p>Optional footer text</p>",
  "cta": {
    "title": "View All",
    "url": "/services/",
    "target": ""
  },
  "background_color": "#ffffff",
  "disable_margins": false
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `type` | string | Yes | Grid type — see Grid Types table above |
| `grid_size` | number | No | Column count: 2, 3, 4, or 5 |
| `title` | string | No | Section heading |
| `description` | wysiwyg | No | Intro text |
| `items` | repeater | Yes | Grid cards — each can have `image`, `title`, `description`, `link` |
| `footer_description` | wysiwyg | No | Text below grid |
| `cta` | link | No | Call-to-action link below grid |
| `background_color` | string | No | Hex color code |
| `disable_margins` | boolean | No | Remove default margins |

### call_to_action

```json
{
  "acf_fc_layout": "call_to_action",
  "type": "default",
  "title": "CTA Heading",
  "subtitle": "Optional subtitle",
  "description": "<p>CTA description text</p>",
  "link": {
    "title": "Button Text",
    "url": "/contact-us/",
    "target": ""
  },
  "image": {
    "url": "/wp-content/uploads/2024/01/cta.jpg",
    "alt": "CTA image"
  },
  "media_type": "image",
  "video": ""
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `type` | string | No | `"default"` or `"simple"` |
| `title` | string | Yes | CTA heading |
| `subtitle` | string | No | Secondary heading |
| `description` | wysiwyg | No | Body text |
| `link` | link | No | Single CTA button (use `link` for one button) |
| `links` | repeater | No | Multiple CTA buttons (use instead of `link` for multiple buttons) |
| `image` | image | No | CTA image |
| `media_type` | string | No | `"image"` or `"video"` |
| `video` | string | No | Video URL when media_type is video |
| `subtitles` | file | No | VTT subtitle file |

**IMPORTANT**: Use `link` (singular, a single object) for a single CTA button. Use `links` (plural, a repeater array) only when there are multiple buttons.

### faqs

```json
{
  "acf_fc_layout": "faqs",
  "title": "Frequently Asked Questions",
  "description": "Optional intro text",
  "items": [
    {
      "question": "Question text?",
      "answer": "<p>Answer text, can contain HTML links.</p>"
    }
  ],
  "cta": {
    "title": "Contact Us",
    "url": "/contact-us/",
    "target": ""
  }
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `title` | string | Yes | Section heading |
| `description` | textarea | No | Intro text |
| `items` | repeater | Yes | FAQ items with `question` and `answer` |
| `cta` | link | No | Call-to-action link |

### about

```json
{
  "acf_fc_layout": "about",
  "type": "default",
  "title": "About Section Title",
  "description": "<p>About body text</p>",
  "items": [
    {
      "title": "Item title",
      "description": "Item description"
    }
  ],
  "footnotes": "<p>Optional footnotes</p>",
  "links": [
    {
      "link": {
        "title": "Learn More",
        "url": "/about/",
        "target": ""
      }
    }
  ],
  "image": {
    "url": "/wp-content/uploads/2024/01/about.jpg",
    "alt": "About section image"
  }
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `type` | string | Yes | `"default"` or `"bullet-list"` |
| `title` | string | Yes | Section heading |
| `description` | wysiwyg | No | Body text |
| `items` | repeater | No | Each item has `title`, `description`, and optionally `icon` |
| `footnotes` | wysiwyg | No | Footnotes at the bottom |
| `links` | repeater | No | Array of link objects |
| `image` | image | No | Only shown when type is `"default"` |

When `type` is `"bullet-list"`, a `bullet_list` group field is used instead of image — but in JSON import, include items with title/description.

### services

```json
{
  "acf_fc_layout": "services",
  "title": "Our Services",
  "description": "<p>Section description</p>",
  "features": [
    "Feature 1",
    "Feature 2"
  ],
  "link": {
    "title": "Learn More",
    "url": "/services/",
    "target": ""
  },
  "image": {
    "url": "/wp-content/uploads/2024/01/services.jpg",
    "alt": "Services image"
  }
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `title` | string | Yes | Section heading |
| `description` | wysiwyg | No | Body text |
| `features` | repeater | No | Each is a simple string value with the feature name |
| `link` | link | No | CTA link |
| `image` | image | No | Side image |

### our_mission

```json
{
  "acf_fc_layout": "our_mission",
  "title": "OUR MISSION",
  "quote": "Quote text",
  "description": "Mission description",
  "services": [
    {
      "icon": "",
      "image": {
        "url": "/wp-content/uploads/2024/01/service.jpg",
        "alt": "Service image"
      },
      "title": "Service Title",
      "description": "Service description",
      "link": {
        "title": "Learn More",
        "url": "/service-page/",
        "target": ""
      }
    }
  ],
  "link": {
    "title": "View All Services",
    "url": "/services/",
    "target": ""
  }
}
```

### process

```json
{
  "acf_fc_layout": "process",
  "title": "Our Process",
  "subtitle": "How we work",
  "image": {
    "url": "/wp-content/uploads/2024/01/process.jpg",
    "alt": "Process diagram"
  }
}
```

### certificates

```json
{
  "acf_fc_layout": "certificates",
  "title": "Government Accessibility Services",
  "description": "<p>Description text</p>",
  "stats": [
    {"label": "Years Experience", "value": "10+"},
    {"label": "Projects Completed", "value": "500+"}
  ],
  "link": {
    "title": "Learn More",
    "url": "/services/",
    "target": ""
  },
  "image": {
    "url": "/wp-content/uploads/2024/01/cert.jpg",
    "alt": "Certificate image"
  }
}
```

### compliance

```json
{
  "acf_fc_layout": "compliance",
  "title": "Compliance Management",
  "description": "<p>Description text</p>",
  "items": [
    {"title": "WCAG 2.2 AA", "description": "Compliance description"}
  ],
  "score": 95,
  "link": {
    "title": "Get Assessment",
    "url": "/contact-us/",
    "target": ""
  },
  "image": {
    "url": "/wp-content/uploads/2024/01/compliance.jpg",
    "alt": "Compliance image"
  },
  "transcript": ""
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `score` | number | No | Compliance score 0-100 |
| `items` | repeater | No | Each has `title` and `description` |
| `transcript` | wysiwyg | No | Video transcript (if compliance section has a video) |

### contact

```json
{
  "acf_fc_layout": "contact",
  "subtitle": "CONTACT US",
  "title": "Get Free Initial Compliance Consultation",
  "description": "Contact us today for a free consultation.",
  "contact_form": "[contact-form-7 id=\"f04c6e2\" title=\"Contact Form\"]"
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `subtitle` | string | No | Label above the title |
| `title` | string | Yes | Contact section heading |
| `description` | textarea | No | Contact description text |
| `contact_form` | string | No | Contact Form 7 shortcode |

### points

```json
{
  "acf_fc_layout": "points",
  "type": "default",
  "title": "Key Points",
  "description": "<p>Optional intro</p>",
  "items": [
    {"title": "Point Title", "description": "Point description"}
  ]
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `type` | string | No | `"default"` or `"grid"` |
| `title` | string | Yes | Section heading |
| `description` | wysiwyg | No | Intro text |
| `items` | repeater | Yes | Each has `title` and `description` |

### links

```json
{
  "acf_fc_layout": "links",
  "links": [
    {
      "link": {
        "title": "Link Text",
        "url": "/page-url/",
        "target": ""
      }
    }
  ]
}
```

### testimonials

```json
{
  "acf_fc_layout": "testimonials",
  "title": "What Our Clients Say",
  "description": "Optional intro text",
  "image": {
    "url": "/wp-content/uploads/2024/01/logo.png",
    "alt": "Client logo"
  },
  "quote": "Testimonial quote text",
  "company": "Company Name",
  "position": "Job Title"
}
```

### team

```json
{
  "acf_fc_layout": "team",
  "title": "Our Team",
  "description": "Optional intro text",
  "members": [
    {
      "photo": {
        "url": "/wp-content/uploads/2024/01/team-member.jpg",
        "alt": "Team member name"
      },
      "name": "Full Name",
      "title": "Job Title",
      "bio": "Short bio text",
      "linkedin": "https://linkedin.com/in/username"
    }
  ]
}
```

### blog

```json
{
  "acf_fc_layout": "blog",
  "title": "OUR LATEST NEWS & BLOGS",
  "description": "Optional intro text",
  "post_count": 6,
  "category": "",
  "link": {
    "title": "View All",
    "url": "/blog/",
    "target": ""
  }
}
```

### logos

```json
{
  "acf_fc_layout": "logos",
  "title": "Our Partners",
  "subtitle": "Trusted by leading organizations",
  "description": "Optional description",
  "columns": 4,
  "logos": [
    {
      "url": "/wp-content/uploads/2024/01/logo1.png",
      "alt": "Partner name"
    }
  ]
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `columns` | number | No | Column count: 3, 4, or 5 |
| `logos` | array | Yes | Array of image objects |

### stats

```json
{
  "acf_fc_layout": "stats",
  "title": "By the Numbers",
  "items": [
    {"value": "500+", "label": "Projects Completed"},
    {"value": "10+", "label": "Years Experience"}
  ]
}
```

### table

```json
{
  "acf_fc_layout": "table",
  "title": "Comparison Table",
  "description": "Optional intro text",
  "table": {},
  "footnote": "<p>Optional footnote</p>"
}
```

### locations

```json
{
  "acf_fc_layout": "locations",
  "title": "Our Offices",
  "map_embed": "https://www.google.com/maps/embed?...",
  "map_link": "https://maps.google.com/...",
  "locations": [
    {"name": "Toronto", "address": "123 Main St, Toronto, ON"}
  ]
}
```

### form

```json
{
  "acf_fc_layout": "form",
  "shortcode": "[contact-form-7 id=\"abc123\" title=\"Form Name\"]",
  "email": "info@accessibilitypartners.ca"
}
```

### gallery

```json
{
  "acf_fc_layout": "gallery",
  "title": "Photo Gallery",
  "description": "<p>Optional intro</p>",
  "images": [
    {
      "url": "/wp-content/uploads/2024/01/photo1.jpg",
      "alt": "Photo description"
    }
  ]
}
```

### grid_videos

```json
{
  "acf_fc_layout": "grid_videos",
  "title": "Video Library",
  "description": "Optional intro text",
  "columns": 2,
  "videos": [
    {
      "video_type": "url",
      "video_url": "https://www.youtube.com/watch?v=...",
      "thumbnail": {
        "url": "/wp-content/uploads/2024/01/thumb.jpg",
        "alt": "Video thumbnail"
      },
      "title": "Video Title"
    }
  ]
}
```

### callout

```json
{
  "acf_fc_layout": "callout",
  "type": "default",
  "content": "<p>Freeform HTML content</p>"
}
```

### space

```json
{
  "acf_fc_layout": "space"
}
```

---

## Link Object Format

All link fields use this structure:

**Single link** (used in `call_to_action.link`, `information.link`, `services.link`, etc.):
```json
"link": {
  "title": "Button Text",
  "url": "/contact-us/",
  "target": ""
}
```

**Link repeater** (used in `hero.links`, `call_to_action.links`, `about.links`):
```json
"links": [
  {
    "link": {
      "title": "Button Text",
      "url": "/contact-us/",
      "target": ""
    }
  }
]
```

**IMPORTANT**: For external links, set `"target": "_blank"`.

---

## Image Format

Always use the object format with `url` and `alt` for accessibility:

```json
"image": {
  "url": "/wp-content/uploads/2024/01/image.jpg",
  "alt": "Descriptive alt text"
}
```

A simple string is also accepted but discouraged:
```json
"image": "/wp-content/uploads/2024/01/image.jpg"
```

### Image rules

1. **Always extract image paths** from the source page when they exist
2. **Use relative paths** starting with `/` (e.g., `/wp-content/uploads/2024/01/image.jpg`)
3. **Never use absolute URLs** — the importer resolves relative paths against `source_url`
4. **Include alt text** from the source `<img>` tag's `alt` attribute; use empty string `""` if no alt text is available
5. **The `source_url` field** must be set at the top level for images to resolve correctly
6. **Leave `image` out** (or set to `""`) if no image is present for that section
7. **SVG files** are supported — include them with their `.svg` extension
8. **Deduplication** is automatic — the same path won't be downloaded twice

---

## Common Mistakes to Avoid

1. **Using `"content"` instead of `"description"`** — The `text` section field is always `description`, never `content`.
2. **Using `"flexible.php"` for `page_template`** — The correct value is `"page-templates/flexible.php"`.
3. **Omitting `source_url` on pages with images** — Without this, relative image paths cannot be resolved.
4. **Using `links` (plural) for a single CTA button** — In `call_to_action`, use `link` (singular object) for one button, `links` (plural array) for multiple.
5. **Forgetting `type` on sections that require it** — The `hero`, `information`, `grid`, and `call_to_action` sections need their `type` field set.
6. **Stripping HTML from descriptions** — WYSIWYG fields (`description` in `text`, `grid.items`, `call_to_action`, etc.) must keep their HTML tags intact.
7. **Using `item` format in `grid.items`** — Grid items always use `{title, description, image, link}` keys, never `{item}`.
8. **Missing `grid_size`** — Grid sections should include `grid_size` (2, 3, 4, or 5) when the column count is discernible from the source.
9. **Inconsistent link format** — In `links` repeater arrays, each entry must be `{"link": {title, url, target}}`, not just `{title, url, target}`.

---

## Section Mapping Reference

| Page Content Pattern | ACF Layout | Image Field | Key Fields |
|---------------------|------------|-------------|------------|
| Hero banner | `hero` | section-level | type, subtitle, title, description, links, image |
| Simple heading | `section_title` | none | title, description |
| Rich text block | `text` | none | type, title, description |
| Checklist/info with image | `information` | section-level | type, title, description, items, link, image |
| Card grid | `grid` | per item | type, grid_size, title, items (image, title, description, link) |
| CTA banner | `call_to_action` | section-level | type, title, subtitle, description, link/links, image |
| FAQ accordion | `faqs` | none | title, description, items, cta |
| About section | `about` | section-level | type, title, description, items, footnotes, links, image |
| Service features | `services` | section-level | title, description, features, link, image |
| Mission statement | `our_mission` | per service item | title, quote, description, services, link |
| Process flow | `process` | section-level | title, subtitle, image |
| Certificate/stats | `certificates` | section-level | title, description, stats, link, image |
| Compliance | `compliance` | section-level | title, description, items, score, link, image |
| Contact form | `contact` | none | subtitle, title, description, contact_form |
| Link buttons | `links` | none | links |
| Point list | `points` | none | type, title, description, items |
| Testimonials | `testimonials` | section-level | title, description, image, quote, company, position |
| Team members | `team` | per member | title, description, members |
| Blog listing | `blog` | none | title, description, post_count, category, link |
| Logo grid | `logos` | per logo | title, subtitle, description, columns, logos |
| Stats | `stats` | none | title, items (value, label) |
| Table | `table` | none | title, description, table, footnote |
| Locations | `locations` | none | title, map_embed, map_link, locations |
| Form | `form` | none | shortcode, email |
| Gallery | `gallery` | per image | title, description, images |
| Video grid | `grid_videos` | per video | title, description, columns, videos |
| Freeform block | `callout` | none | type, content |
| Spacer | `space` | none | — |