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
 * Template manager for tool_eledia_mailtemplates.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates;

use stdClass;

/**
 * Manages CRUD operations and context-based resolution for email templates.
 */
class template_manager {

    /** @var string DB table name for templates. */
    private const TABLE = 'tool_eledia_mt_templates';

    /**
     * Get a single template by ID.
     *
     * @param int $id The template record ID.
     * @return stdClass|false The template record or false if not found.
     */
    public static function get_template(int $id): stdClass|false {
        global $DB;
        return $DB->get_record(self::TABLE, ['id' => $id]);
    }

    /**
     * Get all templates, optionally filtered by notification type.
     *
     * @param string|null $type Optional notification type filter.
     * @return array Array of template records.
     */
    public static function get_templates(?string $type = null): array {
        global $DB;
        $conditions = [];
        if ($type !== null) {
            $conditions['notification_type'] = $type;
        }
        return $DB->get_records(self::TABLE, $conditions, 'notification_type ASC, contextlevel DESC');
    }

    /**
     * Save (create or update) a template record.
     *
     * @param stdClass $data The template data.
     * @return int The template record ID.
     */
    public static function save_template(stdClass $data): int {
        global $DB, $USER;

        $now = time();
        $data->timemodified = $now;
        $data->usermodified = $USER->id;

        if (!empty($data->id)) {
            $DB->update_record(self::TABLE, $data);
            return $data->id;
        }

        $data->timecreated = $now;
        return $DB->insert_record(self::TABLE, $data);
    }

    /**
     * Delete a template by ID.
     *
     * @param int $id The template record ID.
     * @return bool True on success.
     */
    public static function delete_template(int $id): bool {
        global $DB;
        return $DB->delete_records(self::TABLE, ['id' => $id]);
    }

    /**
     * Resolve the best matching template for a notification type and context.
     *
     * Resolution order: module > course > system.
     * Returns null if no active template is found (fallback to Moodle default).
     *
     * @param string $type The notification type identifier.
     * @param \core\context $context The Moodle context.
     * @return stdClass|null The resolved template or null.
     */
    public static function resolve_template(string $type, \core\context $context): ?stdClass {
        global $DB;

        if (!notification_type::is_valid($type)) {
            return null;
        }

        // Build context path from most specific to least specific.
        $contextids = array_reverse($context->get_parent_context_ids(true));

        // Try each context from most specific to system.
        foreach ($contextids as $contextid) {
            $template = $DB->get_record(self::TABLE, [
                'notification_type' => $type,
                'contextid' => $contextid,
                'active' => 1,
            ]);
            if ($template) {
                return $template;
            }
        }

        // Try system-level (contextid = 0) as final fallback.
        $template = $DB->get_record(self::TABLE, [
            'notification_type' => $type,
            'contextid' => 0,
            'active' => 1,
        ]);

        return $template ?: null;
    }
}
