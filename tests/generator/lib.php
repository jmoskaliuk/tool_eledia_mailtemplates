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
 * Test data generator for tool_eledia_mailtemplates.
 *
 * @package    tool_eledia_mailtemplates
 * @category   test
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Data generator for tool_eledia_mailtemplates.
 *
 * Provides create_template() for both PHPUnit and Behat test data generation.
 */
class tool_eledia_mailtemplates_generator extends testing_module_generator {

    /** @var int Counter for unique template names. */
    private int $templatecount = 0;

    /**
     * Create a template record.
     *
     * @param array $record Template data (all fields optional with defaults).
     * @return stdClass The created template record.
     */
    public function create_template(array $record = []): stdClass {
        global $DB;

        $this->templatecount++;

        $record = array_merge([
            'notification_type' => 'password_reset',
            'contextid' => \core\context\system::instance()->id,
            'contextlevel' => CONTEXT_SYSTEM,
            'subject' => 'Test template subject ' . $this->templatecount,
            'body_html' => '<p>Test template body ' . $this->templatecount . '</p>',
            'active' => 1,
            'timecreated' => time(),
            'timemodified' => time(),
        ], $record);

        $record['id'] = $DB->insert_record('tool_eledia_mt_templates', $record);
        return (object) $record;
    }
}
