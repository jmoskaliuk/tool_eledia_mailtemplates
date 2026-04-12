# eLeDia Mail Templates (tool_eledia_mailtemplates)

A Moodle admin tool for centralized email template management. Allows administrators to override selected Moodle notification emails with branded, configurable templates — without modifying core language strings or code.

## Features

- **6 supported notification types:** Password Reset, Course Enrolment, Forum Posts, Assignment Grading, User Registration, Admin/Automated Notifications
- **Context-aware resolution:** Templates can be defined at system, course, or activity module level; the most specific match wins
- **Branding layer:** Shared logo, primary colour, footer, and legal disclaimer applied to all outgoing emails
- **Variable system:** `{{variable}}` syntax with predefined, type-specific variables resolved at runtime
- **Live preview:** AJAX-powered preview with sample data directly in the template editor
- **Safe fallback:** If no template exists, or required variables cannot be resolved, Moodle's default behaviour is preserved
- **Auto plain-text:** HTML templates automatically generate a plain-text version

## Requirements

- Moodle 4.5+ (tested with 4.5 and 5.0)
- PHP 8.1+

## Installation

1. Copy this plugin folder to `<moodle>/admin/tool/eledia_mailtemplates/`
2. Visit **Site administration > Notifications** to trigger the database installation
3. Navigate to **Site administration > Plugins > Admin tools > eLeDia Mail Templates** to configure branding and create templates

Alternatively install via `git clone`:

```bash
cd /path/to/moodle/admin/tool
git clone https://github.com/jmoskaliuk/tool_eledia_mailtemplates.git eledia_mailtemplates
```

## Configuration

### Branding

Go to **Site administration > Plugins > Admin tools > eLeDia Mail Templates > Manage branding** to set:

- Logo URL
- Primary colour (hex)
- Footer HTML
- Legal disclaimer

### Templates

Go to **Manage templates** to create templates per notification type and context scope. Use `{{variable_name}}` syntax in subject and body. The editor shows available variables and a live preview.

## Supported variables

All types share: `{{site_name}}`, `{{site_url}}`, `{{recipient_firstname}}`, `{{recipient_lastname}}`, `{{recipient_fullname}}`, `{{recipient_email}}`

| Type | Additional variables |
|------|---------------------|
| Password Reset | `reset_url`, `reset_expiry` |
| Course Enrolment | `course_fullname`, `course_shortname`, `course_url`, `enrol_method`, `enrol_startdate` |
| Forum Post | `forum_name`, `forum_url`, `post_subject`, `post_content`, `post_author`, `post_url`, `course_fullname` |
| Assignment Grading | `assignment_name`, `assignment_url`, `grade`, `grade_max`, `feedback`, `grader_name`, `course_fullname` |
| User Registration | `username`, `confirm_url` |
| Admin Notification | `admin_message`, `admin_name` |

## Development

### Running tests

```bash
# PHPUnit
vendor/bin/phpunit --testsuite tool_eledia_mailtemplates_testsuite

# Or via moodle-plugin-ci
moodle-plugin-ci phpunit
```

### CI

GitHub Actions workflow runs on push/PR against `main`/`master`. Matrix: Moodle 4.5/5.0 x PHP 8.1/8.3 x PostgreSQL/MariaDB.

## License

GNU GPL v3 or later — see [COPYING](https://www.gnu.org/licenses/gpl-3.0.html)

## Credits

Developed by [eLeDia GmbH](https://eledia.de) (2026).
