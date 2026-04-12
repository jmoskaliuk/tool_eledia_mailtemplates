# Developer Documentation

## Meta

This document describes how the system is actually implemented.

It serves two purposes:
1. Document the technical structure of the product
2. Describe how individual features (featXX) are implemented

This document represents the **current implementation (source of truth for reality)**.

---

## How to use this document

### For humans

Use this document to:
- understand how the system works internally
- navigate architecture and components
- onboard new developers
- support debugging and extension

Think:
→ *How is this system built and how does it actually work?*

---

### For AI

When working with this document:

- treat it as the **source of truth for implementation**
- do not invent behavior not implemented
- do not describe intended behavior → use `01-features.md` for that
- ensure consistency with:
  - `01-features.md` (intended behavior)
  - `02-user-doc.md` (user-facing behavior)
- if mismatch is detected → flag inconsistency

---

## What belongs here

Include:
- architecture
- components and modules
- data flow
- interactions between components
- technical constraints
- known limitations

---

## What does NOT belong here

Do NOT include:
- feature planning or ideas → `01-features.md`
- tasks or work tracking → `04-tasks.md`
- bugs or test logs → `05-quality.md`
- user explanations → `02-user-doc.md`

---

# 🧭 System Overview

## Architecture Overview

- **Plugin Type:** Moodle admin tool (`tool_eledia_mailtemplates`)
- **Moodle Version:** 4.5+ (requires 2024100700)
- **Integration:** Hooks/Events-based override of selected Moodle notifications
- **Storage:** XMLDB tables `tool_eledia_mt_templates` and `tool_eledia_mt_branding`
- **Rendering:** Mustache templates with `{{variable}}` resolution via PHP regex
- **Preview:** AJAX-based live preview using external API + AMD module
- **HTML Strategy:** HTML only, plain text auto-generated via `strip_tags` + formatting

---

## Core Components

### notification_type (`classes/notification_type.php`)
Static registry of the 6 supported MVP notification types and their variables.
Provides type validation, localised names, variable definitions, and sample data for preview.

### template_manager (`classes/template_manager.php`)
CRUD operations on `tool_eledia_mt_templates`. Handles context-based resolution
(module > course > system fallback).

### variable_resolver (`classes/variable_resolver.php`)
Parses `{{variable}}` syntax in template strings and replaces with data values.
Also provides `html_to_plaintext()` for auto-generating plain text from HTML.

### branding_manager (`classes/branding_manager.php`)
Singleton CRUD for `tool_eledia_mt_branding`. Applies branding (logo, colour, footer,
disclaimer) around template body via Mustache wrapper.

### Forms
- `form/template_form.php` — Template create/edit form with notification type selector,
  subject, HTML body editor, context scope, active toggle.
- `form/branding_form.php` — Branding configuration form (logo URL, colour, footer, disclaimer).

### External API
- `external/render_preview.php` — AJAX service that resolves variables with sample data,
  applies branding, and returns rendered HTML + auto-generated plain text.

### Message Interception (`classes/message_interceptor.php`)
Central override logic. Maps Moodle's internal message component+name identifiers
to our 6 notification types via `MESSAGE_MAP`. Entry point `process_message()`:
1. Identifies notification type from component/name.
2. Determines context from message (courseid, contexturl, or system fallback).
3. Resolves template via `template_manager`.
4. Builds variable data from Moodle objects (user, course, module).
5. Resolves variables, applies branding, generates plain text.
6. Replaces message content in-place.

Falls back to Moodle default if: type not supported, no template found, template incomplete.

### Hook Callbacks (`classes/hook_callbacks.php`)
Registers for `\core\hook\message\before_message_sent` (Moodle 5.x).
Filters for notification-type messages only, delegates to `message_interceptor`.

### Event Observers (`classes/event/observers.php`)
Fallback/diagnostic observer for `\core\event\notification_sent`.
Reserved for logging. Actual interception is hook-based.

### Privacy
- `privacy/provider.php` — Null provider (plugin stores no personal user data).

### JavaScript (AMD)
- `amd/src/preview.js` — Live preview in template editor. Debounced AJAX calls on
  type/subject/body changes. Updates available variables list per notification type.

---

## Data Flow

### Template Resolution (at notification time)
1. Moodle triggers a notification (e.g. password reset).
2. Hook/event listener identifies the notification type.
3. `template_manager::resolve_template($type, $context)` walks the context hierarchy.
4. If active template found: `variable_resolver::resolve()` replaces placeholders with real data.
5. `branding_manager::apply_branding()` wraps body in branded HTML layout.
6. `variable_resolver::html_to_plaintext()` generates the plain text version.
7. Result replaces Moodle's default message content.
8. If no template found or template incomplete: Moodle default behaviour is used unchanged.

### Template Editing (admin workflow)
1. Admin opens index.php → template list (Mustache rendered).
2. Admin clicks create/edit → edit.php → template_form.
3. AMD preview.js calls `render_preview` AJAX service on changes.
4. On save: `template_manager::save_template()` → redirect to list.

### Database Tables
- `tool_eledia_mt_templates` — Templates: id, notification_type, contextid, contextlevel, subject, body_html, active, timecreated, timemodified, usermodified.
- `tool_eledia_mt_branding` — Branding (singleton): id, logourl, primarycolor, footercontent, disclaimer, timecreated, timemodified, usermodified.

---

## External Dependencies

- Moodle core messaging API (`\core\message\message`, `message_send()`)
- Moodle Hooks API (`\core\hook\message\before_message_sent`) — Moodle 5.x
- Moodle context system (`\core\context\*`)
- Moodle events system (`\core\event\notification_sent`) — diagnostic/fallback

## Message Type Mapping

| Moodle component/name | Our notification type |
|---|---|
| `moodle/resetpassword` | `password_reset` |
| `core/enrolcoursewelcome`, `enrol_*/expiry_notification` | `course_enrolment` |
| `mod_forum/posts`, `mod_forum/digests` | `forum_post` |
| `mod_assign/assign_notification` | `assignment_grading` |
| `moodle/emailconfirmation` | `user_registration` |
| `moodle/notices`, `moodle/instantmessage` | `admin_notification` |

---

## Technical Constraints

- Must not intercept all outgoing emails
- Must remain compatible with Moodle core upgrades
- Only predefined notification types are supported

---

# 🧩 Feature Implementation

Each feature describes how it is implemented in the system.

All entries must:
- reference a feature (featXX)
- describe actual implementation
- avoid speculation

---

# 📏 Rules

- Always describe the current implementation (not the intended one)
- Keep descriptions precise and technical
- Do not duplicate feature descriptions
- Update this document when implementation changes

---

# 🔑 Key Principle

> This document explains how the system is built — not how it should behave.
