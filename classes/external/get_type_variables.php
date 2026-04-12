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
 * External function to return available variables for a notification type.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use tool_eledia_mailtemplates\notification_type;

/**
 * Returns the list of available variable keys and descriptions for a notification type.
 */
class get_type_variables extends external_api {

    /**
     * Describe the input parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'notification_type' => new external_value(PARAM_ALPHANUMEXT, 'Notification type identifier'),
        ]);
    }

    /**
     * Return available variables for the given notification type.
     *
     * @param string $notificationtype The notification type identifier.
     * @return array List of variable key/description pairs.
     */
    public static function execute(string $notificationtype): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'notification_type' => $notificationtype,
        ]);

        $context = \core\context\system::instance();
        self::validate_context($context);
        require_capability('tool/eledia_mailtemplates:manage', $context);

        if (!notification_type::is_valid($params['notification_type'])) {
            return ['variables' => []];
        }

        $vars = notification_type::get_variables($params['notification_type']);
        $result = [];
        foreach ($vars as $key => $description) {
            $result[] = [
                'key'         => $key,
                'placeholder' => '{{' . $key . '}}',
                'description' => $description,
            ];
        }

        return ['variables' => $result];
    }

    /**
     * Describe the return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'variables' => new external_multiple_structure(
                new external_single_structure([
                    'key'         => new external_value(PARAM_ALPHANUMEXT, 'Variable key'),
                    'placeholder' => new external_value(PARAM_RAW, 'Placeholder syntax'),
                    'description' => new external_value(PARAM_TEXT, 'Human-readable description'),
                ])
            ),
        ]);
    }
}
