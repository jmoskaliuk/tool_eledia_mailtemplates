# Tasks

## Meta

This is the operational center of the system.

---

## 🆕 New

*(none)*

---

## ❓ Clarification Needed

*(none — all MVP decisions resolved 2026-04-12)*

---

## 📋 Tasks

### task01 Create plugin scaffold (version.php, lang, settings, db)
Status: done
Feature: feat01
Goal: Runnable plugin skeleton that installs cleanly on Moodle 5.x
Completed: 2026-04-12

---

### task02 Define variables per notification type
Status: done
Feature: feat03
Goal: Document the variable set for each of the 6 MVP notification types
Completed: 2026-04-12 — implemented in classes/notification_type.php

---

### task03 Define branding properties and DB schema
Status: done
Feature: feat02
Goal: XMLDB table for branding (logo, color, footer, disclaimer), settings page
Completed: 2026-04-12 — db/install.xml + classes/branding_manager.php + branding.php

---

### task04 Design template DB structure (install.xml)
Status: done
Feature: feat04
Goal: XMLDB tables for templates (subject, body_html, notification_type, context, active)
Completed: 2026-04-12 — tool_eledia_mt_templates table in db/install.xml

---

### task05 Implement context resolution logic
Status: done
Feature: feat05
Goal: PHP class that resolves template by context hierarchy (module > course > system)
Completed: 2026-04-12 — template_manager::resolve_template()

---

### task06 Implement override hook per notification type
Status: done
Feature: feat06
Goal: Event/hook listeners that intercept the 6 MVP notification types and apply templates
Completed: 2026-04-12 — message_interceptor.php, hook_callbacks.php, db/hooks.php, db/events.php

---

### task07 Implement fallback behavior
Status: done
Feature: feat01
Goal: If no template or incomplete template → Moodle default behavior unchanged
Completed: 2026-04-12 — built into message_interceptor::process_message() with 3 fallback points

---

### task08 Build template management UI (admin page)
Status: done
Feature: feat04
Goal: Admin page to create/edit/activate templates per notification type and context
Completed: 2026-04-12 — index.php, edit.php, template_form.php, template_list.mustache

---

### task09 Implement variable syntax and resolution engine
Status: done
Feature: feat03
Goal: Parser for {{variable}} syntax, resolver that maps to Moodle data at runtime
Completed: 2026-04-12 — classes/variable_resolver.php

---

### task10 Implement branding layer (HTML wrapper)
Status: done
Feature: feat02
Goal: Mustache wrapper that applies logo, color, footer around template body
Completed: 2026-04-12 — branding_manager::apply_branding() + email_wrapper.mustache

---

### task11 Implement live preview (AJAX endpoint)
Status: done
Feature: feat04
Goal: AJAX service that renders template with sample data, shown alongside editor
Completed: 2026-04-12 — external/render_preview.php + amd/src/preview.js

---

### task12 Implement auto plain-text generation
Status: done
Feature: feat04
Goal: strip_tags + formatting logic to generate plain text from HTML template
Completed: 2026-04-12 — variable_resolver::html_to_plaintext()

---

### task13 Define edge cases for missing data
Status: done
Feature: feat03
Goal: Document and handle missing variable data (empty/fallback behavior)
Completed: 2026-04-12 — variable_resolver replaces unknown vars with '', build_type_specific_data sets safe defaults

---

## 🔧 In Progress

---

## 🔎 Verify After Deploy

---

## ✅ Done

### ~~task-r01~~ Define MVP notification types
Resolved: 2026-04-12
Result: 6 types — Password Reset, Course Enrolment, Forum Posts, Assignment Grading, User Registration, Admin/Automated

### ~~task-r02~~ Decide plugin name
Resolved: 2026-04-12
Result: tool_eledia_mailtemplates (confirmed)

### ~~task-r03~~ Decide HTML vs plain text strategy
Resolved: 2026-04-12
Result: HTML only, plain text auto-generated

### ~~task-r04~~ Decide preview for MVP
Resolved: 2026-04-12
Result: Live-Preview with sample data included in MVP

---

## Rules

- convert relevant items into tasks
- keep tasks small
- move completed tasks to Done
- do not delete, only move
