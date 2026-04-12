<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for tool_eledia_mailtemplates.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// General.
$string['pluginname'] = 'eLeDia Mail Templates';

// Capabilities.
$string['eledia_mailtemplates:manage'] = 'Manage email templates';

// Navigation & settings.
$string['manage_templates'] = 'Manage templates';
$string['manage_branding'] = 'Manage branding';
$string['settings_heading'] = 'eLeDia Mail Templates Settings';

// Notification types.
$string['notificationtype'] = 'Notification type';
$string['type_password_reset'] = 'Password reset';
$string['type_course_enrolment'] = 'Course enrolment';
$string['type_forum_post'] = 'Forum post notification';
$string['type_assignment_grading'] = 'Assignment grading';
$string['type_user_registration'] = 'User registration confirmation';
$string['type_admin_notification'] = 'Admin / automated notification';

// Template management.
$string['template'] = 'Template';
$string['templates'] = 'Templates';
$string['create_template'] = 'Create template';
$string['edit_template'] = 'Edit template';
$string['delete_template'] = 'Delete template';
$string['template_subject'] = 'Subject';
$string['template_body'] = 'Message body (HTML)';
$string['template_active'] = 'Active';
$string['template_context'] = 'Context scope';
$string['template_saved'] = 'Template saved successfully.';
$string['template_deleted'] = 'Template deleted.';
$string['template_confirm_delete'] = 'Are you sure you want to delete this template?';
$string['no_templates'] = 'No templates defined yet.';

// Context scopes.
$string['context_system'] = 'System (site-wide)';
$string['context_course'] = 'Course';
$string['context_module'] = 'Activity module';

// Branding.
$string['branding'] = 'Branding';
$string['branding_logo'] = 'Logo';
$string['branding_logo_desc'] = 'Upload a logo image for email headers.';
$string['branding_logo_help'] = 'Enter the full URL to a logo image. This logo will appear at the top of all branded emails.';
$string['branding_primarycolor'] = 'Primary colour';
$string['branding_primarycolor_desc'] = 'Primary colour used in email template (hex value).';
$string['branding_primarycolor_help'] = 'Enter a hex colour code (e.g. #336699). This colour is used for borders and accents in the email layout.';
$string['branding_footer'] = 'Footer content';
$string['branding_footer_desc'] = 'HTML content displayed at the bottom of every email.';
$string['branding_disclaimer'] = 'Legal disclaimer';
$string['branding_disclaimer_desc'] = 'Optional legal text appended after footer.';
$string['branding_saved'] = 'Branding settings saved.';

// Preview.
$string['preview'] = 'Preview';
$string['preview_loading'] = 'Loading preview...';
$string['preview_sample_data'] = 'Sample data used for preview.';

// Variables.
$string['available_variables'] = 'Available variables';
$string['variable_not_resolved'] = '(not available)';

// Errors.
$string['error_invalid_type'] = 'Invalid notification type.';
$string['error_missing_subject'] = 'Subject is required.';
$string['error_missing_body'] = 'Message body is required.';

// Privacy.
$string['privacy:metadata'] = 'The eLeDia Mail Templates plugin does not store personal user data. It stores administrative template configurations only.';
