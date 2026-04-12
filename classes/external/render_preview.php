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
 * External function to render a template preview.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use tool_eledia_mailtemplates\notification_type;
use tool_eledia_mailtemplates\variable_resolver;
use tool_eledia_mailtemplates\branding_manager;

/**
 * Renders a template preview with sample data and branding.
 */
class render_preview extends external_api {

    /**
     * Describe the input parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'notification_type' => new external_value(PARAM_ALPHANUMEXT, 'Notification type identifier'),
            'subject' => new external_value(PARAM_TEXT, 'Template subject line'),
            'body_html' => new external_value(PARAM_RAW, 'Template body HTML'),
        ]);
    }

    /**
     * Render a template preview.
     *
     * @param string $notificationtype The notification type.
     * @param string $subject The template subject.
     * @param string $bodyhtml The template body HTML.
     * @return array The rendered preview data.
     */
    public static function execute(string $notificationtype, string $subject, string $bodyhtml): array {
        global $PAGE;

        $params = self::validate_parameters(self::execute_parameters(), [
            'notification_type' => $notificationtype,
            'subject' => $subject,
            'body_html' => $bodyhtml,
        ]);

        $context = \core\context\system::instance();
        self::validate_context($context);
        require_capability('tool/eledia_mailtemplates:manage', $context);

        // Ensure the renderer is available.
        $PAGE->set_context($context);

        // Get sample data for this notification type.
        $sampledata = notification_type::get_sample_data($params['notification_type']);

        // Resolve variables in subject and body.
        $resolvedsubject = variable_resolver::resolve($params['subject'], $sampledata);
        $resolvedbody = variable_resolver::resolve($params['body_html'], $sampledata);

        // Apply branding wrapper.
        $fullhtml = branding_manager::apply_branding($resolvedbody);

        // Generate plain text version.
        $plaintext = variable_resolver::html_to_plaintext($resolvedbody);

        return [
            'subject' => $resolvedsubject,
            'body_html' => $fullhtml,
            'body_plain' => $plaintext,
        ];
    }

    /**
     * Describe the return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'subject' => new external_value(PARAM_RAW, 'Rendered subject'),
            'body_html' => new external_value(PARAM_RAW, 'Rendered HTML body with branding'),
            'body_plain' => new external_value(PARAM_RAW, 'Auto-generated plain text version'),
        ]);
    }
}
