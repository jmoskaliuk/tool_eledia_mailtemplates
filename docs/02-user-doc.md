# User Documentation

## Meta

This document describes how users interact with the product and its features.

It serves two purposes:
1. Explain the product from a user perspective
2. Describe how individual features (featXX) are used

This document is the **source of truth for user experience**.

---

## How to use this document

### For humans

Use this document to:
- describe how users interact with the system
- ensure features are understandable and usable
- document flows, steps, and expected outcomes
- validate usability independently from implementation

Think:
→ *How does a user experience this product?*

---

### For AI

When working with this document:

- treat it as the **source of truth for user-facing behavior**
- do not introduce technical explanations
- ensure consistency with `01-features.md`
- ensure it matches actual behavior (`03-dev-doc.md`)
- if unclear → request clarification

---

## What belongs here

Include:
- user flows
- step-by-step interactions
- expected results
- constraints from a user perspective
- usage examples

---

## What does NOT belong here

Do NOT include:
- implementation details → `03-dev-doc.md`
- internal logic or architecture
- tasks or planning → `04-tasks.md`
- bugs or test logs → `05-quality.md`

---

# Product Usage Overview

## Target Users

- Moodle site administrators
- System administrators responsible for email communication

---

## Main Use Cases

- Configure branded email templates for selected Moodle notifications
- Define global branding (logo, colors, footer) for consistent email appearance
- Override specific notification types with custom templates
- Assign templates at system, course, or module level

---

## Typical Workflow

1. Administrator navigates to Site Administration → Tools → eLeDia Mail Templates
2. Administrator configures global branding settings
3. Administrator creates a template for a supported notification type
4. Administrator assigns the template to a context (system, course, or module)
5. When the notification is triggered, the custom template is used instead of the Moodle default

---

## Key Concepts (User Perspective)

- **Template**: A customizable email layout with subject and message body
- **Notification Type**: A specific kind of Moodle email (e.g. password reset, course enrolment)
- **Branding**: Shared visual elements applied to all templates (logo, colors, footer)
- **Context**: The scope at which a template applies (system-wide, per course, or per module)

---

# Feature Usage

Each feature describes how a user interacts with it.

All entries must:
- reference a feature (featXX)
- focus on usability
- avoid technical details

---

# Rules

- Every feature must reference a `featXX`
- Keep language simple and user-focused
- Avoid technical terminology
- Keep instructions actionable
- Update when user-facing behavior changes

---

# Key Principle

> This document explains how the product feels and works for the user — not how it is built.
