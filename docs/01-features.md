# Features

## Meta

This document defines what the product should do.

It describes:

    features (featXX)
    intended behavior
    product-level decisions

This document represents the intended behavior of the system.

---

## How to use this document

### For humans

Use this document to:

    define new features before implementation
    clarify behavior during development
    document decisions and constraints
    ensure a shared understanding of the product

This is the place to think about: → What should the product do and why?

### For AI

When working with this document:

    treat it as the source of truth for expected behavior
    do not assume behavior that is not defined here
    if something is unclear → raise a clarification instead of guessing
    ensure implementation and documentation align with this document

---

## What belongs here

Include:

    feature definitions (featXX)
    goals and purpose
    expected behavior (including edge cases)
    non-goals (explicit exclusions)
    design decisions

---

## What does NOT belong here

Do NOT include:

    tasks (→ 04-tasks.md)
    implementation details (→ 03-dev-doc.md)
    test results or bugs (→ 05-quality.md)
    user instructions (→ 02-user-doc.md)

---

# Product Overview

## Purpose

The system provides a centralized way to define and control selected Moodle email notifications using configurable templates.

It enables administrators to ensure consistent, branded, and maintainable email communication without modifying core language strings or code.

The system focuses on **selected, explicitly supported notification types** and integrates with Moodle in a controlled and non-invasive way.

---

## Core Concepts

- **Notification Type**  
  A predefined category of Moodle-triggered communication (e.g. password reset, course enrolment).

- **Template**  
  A configurable structure consisting of subject and message content used for a specific notification type.

- **Branding Layer**  
  A shared layout definition including elements such as logo, colors, and footer.

- **Context-based Resolution**  
  Templates are resolved based on Moodle context hierarchy:
  module → course → system

- **Explicit Override**  
  Templates only replace Moodle core behavior where explicitly defined.

---

## Key Features

- Central management of email templates
- Branding configuration for consistent communication
- Controlled override of selected Moodle notifications
- Context-aware template resolution
- Variable-based personalization using Moodle data

---

## Constraints

- No global interception of all outgoing emails
- Only predefined notification types are supported
- No automatic support for newly introduced Moodle notifications
- No free-form rule engine in MVP
- No full visual template designer in MVP
- Must remain compatible with Moodle core and third-party plugins

---

# Features

## feat01 Configurable Notification Templates (MVP)

### Goal

Provide administrators with a centralized mechanism to control and customize selected Moodle email notifications in a consistent and branded way.

The feature reduces complexity, avoids manual modification of language strings, and improves maintainability of system communication.

---

### Behavior

#### General Flow

- The system provides an administrative interface to manage email templates.
- Administrators can configure:
  - global branding (logo, colors, footer)
  - templates per supported notification type
- Each template consists of:
  - subject
  - message content

#### Template Assignment

- Templates are assigned to:
  - a notification type
  - a context (system, course, or module)

#### Template Resolution

When a notification is triggered:

1. The system determines the notification type.
2. The system determines the relevant Moodle context.
3. The system selects the most specific applicable template:
   - module context overrides course context
   - course context overrides system context
4. If no template is defined:
   - the default Moodle behavior is used

#### Override Behavior

- If a template is active:
  - it replaces the default message content of Moodle for that notification type
- Only explicitly supported notification types are affected
- All other notifications remain unchanged

#### Variables

- Templates can include predefined variables
- Variables are resolved using Moodle data at runtime
- Variables are limited to a predefined set per notification type
- If a variable cannot be resolved:
  - it is rendered as empty or a safe fallback

#### Supported Notification Types (MVP)

The system supports a limited predefined set of notification types.

Each type:
- has a defined set of variables
- is explicitly supported by the system
- can be individually configured

#### Edge Cases

- If multiple templates exist:
  - the most specific context wins
- If a template is incomplete:
  - the system falls back to Moodle default behavior
- If the plugin does not support a notification:
  - no changes are applied
- If other plugins send emails:
  - they are not affected unless explicitly supported

---

### Non-goals

- No full template designer with free layout capabilities
- No support for all Moodle notifications
- No automatic detection of new notification types
- No generic rule engine for arbitrary events
- No modification of Moodle language files
- No global interception of email sending
- No additional communication channels (e.g. SMS, push)
- No versioning or history of templates

---

### Decisions

- **Explicit integration over global interception**  
  Chosen to ensure compatibility, maintainability, and upgrade safety.

- **Fixed notification type set for MVP**  
  Reduces complexity and ensures stable behavior.

- **Context-based resolution model**  
  Aligns with Moodle’s existing context system and permission logic.

- **Branding layer instead of full designer**  
  Allows consistent output while keeping implementation simple.

- **Predefined variable sets**  
  Ensures predictable behavior and avoids uncontrolled data access.

- **Fallback to Moodle core behavior**  
  Guarantees system stability if templates are missing or incomplete.

---

### Open Questions

- Final list of supported notification types for MVP
- Minimum required variable set per notification type
- Handling of HTML vs. plain text rendering
- Whether a basic preview capability should be included in MVP
- Strategy for handling changes in Moodle core notification behavior

## feat02 Branding Configuration

### Goal

Enable administrators to define a consistent visual identity for all outgoing emails.

---

### Behavior

- Administrators can configure global branding elements:
  - logo (image)
  - primary color
  - footer content
  - optional legal disclaimer

- Branding is applied automatically to all templates
- Branding is independent of notification types

Edge cases:
- If no branding is defined:
  - a default minimal layout is used
- If branding is partially defined:
  - missing elements are omitted gracefully

---

### Non-goals

- No full layout designer
- No per-template layout customization
- No responsive design configuration

---

### Decisions

- Central branding ensures consistency
- Separation from templates avoids duplication


## feat03 Variable System

### Goal

Provide a controlled and predictable way to use dynamic data in templates.

---

### Behavior

- Each notification type provides a predefined set of variables
- Variables are inserted into templates using a defined syntax
- Variables are resolved at runtime using Moodle data

- Variables are grouped into:
  - recipient data
  - context data
  - system data

Edge cases:
- Missing data results in empty output or fallback
- Invalid variables are ignored or removed

---

### Non-goals

- No arbitrary access to Moodle database fields
- No user-defined variables in MVP
- No scripting or logic in templates

---

### Decisions

- Fixed variable sets ensure stability
- Prevents security and performance issues

## feat04 Template Management UI

### Goal

Provide an administrative interface to create and manage templates.

---

### Behavior

- Admins can:
  - create templates
  - edit templates
  - assign templates to notification types
  - assign context scope

- Templates include:
  - subject
  - body content

- Templates can be activated/deactivated

Edge cases:
- inactive templates are ignored
- incomplete templates are not applied

---

### Non-goals

- No workflow (draft/publish)
- No versioning
- No collaboration features

---

### Decisions

- Keep UI minimal for MVP
- Focus on clarity over flexibility

  ## feat05 Context-based Resolution

### Goal

Ensure correct template selection based on Moodle context hierarchy.

---

### Behavior

- Templates can exist at:
  - system level
  - course level
  - module level

- Resolution order:
  module > course > system

Edge cases:
- if multiple templates exist at same level:
  - latest active template is used
- if no template exists:
  - fallback to Moodle default

---

### Non-goals

- No complex priority rules
- No manual ordering

---

### Decisions

- Aligns with Moodle context model
- Predictable behavior for admins

  ## feat06 Controlled Core Override

### Goal

Override Moodle notifications in a safe and controlled way.

---

### Behavior

- Only supported notification types can be overridden
- Override is activated per template
- No global interception of email sending

- If override is active:
  - template replaces core content
- If not:
  - core behavior remains unchanged

Edge cases:
- conflicting plugins:
  - system does not interfere outside supported scope

---

### Non-goals

- No interception of all emails
- No modification of unrelated plugins

---

### Decisions

- Explicit integration ensures compatibility
- Prevents upgrade issues
