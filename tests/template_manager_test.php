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
 * Tests for the template_manager class.
 *
 * @package    tool_eledia_mailtemplates
 * @category   test
 * @covers     \tool_eledia_mailtemplates\template_manager
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates\tests;

use tool_eledia_mailtemplates\template_manager;
use tool_eledia_mailtemplates\notification_type;

defined('MOODLE_INTERNAL') || die();

/**
 * Integration tests for template_manager (requires DB).
 */
class template_manager_test extends \advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    /**
     * Create a template record for testing.
     *
     * @param array $overrides Optional field overrides.
     * @return int The new template ID.
     */
    private function create_test_template(array $overrides = []): int {
        $data = (object) array_merge([
            'notification_type' => notification_type::TYPE_PASSWORD_RESET,
            'contextid'         => 0,
            'contextlevel'      => CONTEXT_SYSTEM,
            'subject'           => 'Test Subject',
            'body_html'         => '<p>Test body</p>',
            'active'            => 1,
        ], $overrides);
        return template_manager::save_template($data);
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    /**
     * Test that save_template creates a new record and returns a valid ID.
     */
    public function test_save_template_creates_record(): void {
        $id = $this->create_test_template();
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    /**
     * Test that get_template retrieves the correct record.
     */
    public function test_get_template_returns_correct_record(): void {
        $id = $this->create_test_template(['subject' => 'My Subject']);
        $record = template_manager::get_template($id);

        $this->assertNotFalse($record);
        $this->assertEquals('My Subject', $record->subject);
        $this->assertEquals(notification_type::TYPE_PASSWORD_RESET, $record->notification_type);
    }

    /**
     * Test that get_template returns false for a non-existent ID.
     */
    public function test_get_template_returns_false_for_missing_id(): void {
        $result = template_manager::get_template(99999);
        $this->assertFalse($result);
    }

    /**
     * Test that save_template updates an existing record when ID is set.
     */
    public function test_save_template_updates_existing_record(): void {
        $id = $this->create_test_template(['subject' => 'Original']);

        $update = template_manager::get_template($id);
        $update->subject = 'Updated Subject';
        template_manager::save_template($update);

        $record = template_manager::get_template($id);
        $this->assertEquals('Updated Subject', $record->subject);
    }

    /**
     * Test that delete_template removes the record.
     */
    public function test_delete_template_removes_record(): void {
        $id = $this->create_test_template();
        $this->assertNotFalse(template_manager::get_template($id));

        template_manager::delete_template($id);
        $this->assertFalse(template_manager::get_template($id));
    }

    /**
     * Test that get_templates returns all templates.
     */
    public function test_get_templates_returns_all(): void {
        $this->create_test_template(['notification_type' => notification_type::TYPE_PASSWORD_RESET]);
        $this->create_test_template(['notification_type' => notification_type::TYPE_FORUM_POST]);

        $all = template_manager::get_templates();
        $this->assertCount(2, $all);
    }

    /**
     * Test that get_templates filters by type correctly.
     */
    public function test_get_templates_filters_by_type(): void {
        $this->create_test_template(['notification_type' => notification_type::TYPE_PASSWORD_RESET]);
        $this->create_test_template(['notification_type' => notification_type::TYPE_FORUM_POST]);

        $filtered = template_manager::get_templates(notification_type::TYPE_PASSWORD_RESET);
        $this->assertCount(1, $filtered);
        $record = reset($filtered);
        $this->assertEquals(notification_type::TYPE_PASSWORD_RESET, $record->notification_type);
    }

    /**
     * Test that timemodified and usermodified are set on save.
     */
    public function test_save_template_sets_timestamps(): void {
        $before = time();
        $id = $this->create_test_template();
        $record = template_manager::get_template($id);

        $this->assertGreaterThanOrEqual($before, $record->timecreated);
        $this->assertGreaterThanOrEqual($before, $record->timemodified);
        $this->assertGreaterThan(0, $record->usermodified);
    }

    // -------------------------------------------------------------------------
    // Context resolution
    // -------------------------------------------------------------------------

    /**
     * Test that a system-level template is resolved when no specific template exists.
     */
    public function test_resolve_template_returns_system_template(): void {
        $this->create_test_template([
            'contextid'    => 0,
            'contextlevel' => CONTEXT_SYSTEM,
            'subject'      => 'System Template',
        ]);

        $context  = \core\context\system::instance();
        $resolved = template_manager::resolve_template(
            notification_type::TYPE_PASSWORD_RESET,
            $context
        );

        $this->assertNotNull($resolved);
        $this->assertEquals('System Template', $resolved->subject);
    }

    /**
     * Test that a course-level template overrides the system template.
     */
    public function test_resolve_template_course_overrides_system(): void {
        $course = $this->getDataGenerator()->create_course();
        $coursecontext = \core\context\course::instance($course->id);

        // System template.
        $this->create_test_template([
            'notification_type' => notification_type::TYPE_COURSE_ENROLMENT,
            'contextid'         => 0,
            'contextlevel'      => CONTEXT_SYSTEM,
            'subject'           => 'System Enrolment',
        ]);

        // Course-specific template.
        $this->create_test_template([
            'notification_type' => notification_type::TYPE_COURSE_ENROLMENT,
            'contextid'         => $coursecontext->id,
            'contextlevel'      => CONTEXT_COURSE,
            'subject'           => 'Course Enrolment',
        ]);

        $resolved = template_manager::resolve_template(
            notification_type::TYPE_COURSE_ENROLMENT,
            $coursecontext
        );

        $this->assertNotNull($resolved);
        $this->assertEquals('Course Enrolment', $resolved->subject);
    }

    /**
     * Test that resolve_template returns null when no template exists (fallback to Moodle default).
     */
    public function test_resolve_template_returns_null_when_none_exists(): void {
        $context  = \core\context\system::instance();
        $resolved = template_manager::resolve_template(
            notification_type::TYPE_PASSWORD_RESET,
            $context
        );
        $this->assertNull($resolved);
    }

    /**
     * Test that an inactive template is NOT returned by resolve.
     */
    public function test_resolve_template_ignores_inactive_templates(): void {
        $this->create_test_template([
            'contextid' => 0,
            'active'    => 0,   // Inactive.
            'subject'   => 'Inactive Template',
        ]);

        $context  = \core\context\system::instance();
        $resolved = template_manager::resolve_template(
            notification_type::TYPE_PASSWORD_RESET,
            $context
        );
        $this->assertNull($resolved);
    }

    /**
     * Test that an unsupported notification type returns null.
     */
    public function test_resolve_template_returns_null_for_invalid_type(): void {
        $context  = \core\context\system::instance();
        $resolved = template_manager::resolve_template('totally_unknown_type', $context);
        $this->assertNull($resolved);
    }
}
