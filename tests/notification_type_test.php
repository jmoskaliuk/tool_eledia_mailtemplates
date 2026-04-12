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
 * Tests for the notification_type class.
 *
 * @package    tool_eledia_mailtemplates
 * @category   test
 * @covers     \tool_eledia_mailtemplates\notification_type
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates\tests;

use tool_eledia_mailtemplates\notification_type;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for notification_type.
 */
class notification_type_test extends \advanced_testcase {

    /**
     * Test that exactly 6 MVP notification types are registered.
     */
    public function test_get_types_returns_six_types(): void {
        $types = notification_type::get_types();
        $this->assertCount(6, $types);
    }

    /**
     * Test that all expected MVP types are present.
     */
    public function test_all_mvp_types_exist(): void {
        $types = notification_type::get_types();
        $this->assertArrayHasKey(notification_type::TYPE_PASSWORD_RESET, $types);
        $this->assertArrayHasKey(notification_type::TYPE_COURSE_ENROLMENT, $types);
        $this->assertArrayHasKey(notification_type::TYPE_FORUM_POST, $types);
        $this->assertArrayHasKey(notification_type::TYPE_ASSIGNMENT_GRADING, $types);
        $this->assertArrayHasKey(notification_type::TYPE_USER_REGISTRATION, $types);
        $this->assertArrayHasKey(notification_type::TYPE_ADMIN_NOTIFICATION, $types);
    }

    /**
     * Test is_valid returns true for a supported type.
     */
    public function test_is_valid_returns_true_for_known_type(): void {
        $this->assertTrue(notification_type::is_valid(notification_type::TYPE_PASSWORD_RESET));
        $this->assertTrue(notification_type::is_valid(notification_type::TYPE_FORUM_POST));
    }

    /**
     * Test is_valid returns false for an unknown type.
     */
    public function test_is_valid_returns_false_for_unknown_type(): void {
        $this->assertFalse(notification_type::is_valid('completely_unknown_type'));
        $this->assertFalse(notification_type::is_valid(''));
        $this->assertFalse(notification_type::is_valid('PASSWORD_RESET')); // Case sensitive.
    }

    /**
     * Test that each type has at least the common variables.
     */
    public function test_get_variables_includes_common_vars(): void {
        $commonkeys = ['site_name', 'site_url', 'recipient_firstname', 'recipient_lastname',
            'recipient_fullname', 'recipient_email'];

        foreach (notification_type::get_types() as $type => $stringkey) {
            $vars = notification_type::get_variables($type);
            foreach ($commonkeys as $key) {
                $this->assertArrayHasKey($key, $vars,
                    "Type '{$type}' is missing common variable '{$key}'");
            }
        }
    }

    /**
     * Test type-specific variables for password reset.
     */
    public function test_password_reset_has_reset_url_variable(): void {
        $vars = notification_type::get_variables(notification_type::TYPE_PASSWORD_RESET);
        $this->assertArrayHasKey('reset_url', $vars);
        $this->assertArrayHasKey('reset_expiry', $vars);
    }

    /**
     * Test type-specific variables for course enrolment.
     */
    public function test_course_enrolment_has_course_variables(): void {
        $vars = notification_type::get_variables(notification_type::TYPE_COURSE_ENROLMENT);
        $this->assertArrayHasKey('course_fullname', $vars);
        $this->assertArrayHasKey('course_url', $vars);
    }

    /**
     * Test that sample data is provided for all types.
     */
    public function test_get_sample_data_returns_data_for_all_types(): void {
        foreach (notification_type::get_types() as $type => $stringkey) {
            $sample = notification_type::get_sample_data($type);
            $this->assertIsArray($sample, "No sample data for type '{$type}'");
            $this->assertNotEmpty($sample, "Empty sample data for type '{$type}'");
            $this->assertArrayHasKey('site_name', $sample,
                "Type '{$type}' sample data missing 'site_name'");
        }
    }

    /**
     * Test that sample data keys match the declared variable keys.
     */
    public function test_sample_data_keys_match_variable_keys(): void {
        foreach (notification_type::get_types() as $type => $stringkey) {
            $vars   = notification_type::get_variables($type);
            $sample = notification_type::get_sample_data($type);
            foreach (array_keys($vars) as $key) {
                $this->assertArrayHasKey($key, $sample,
                    "Type '{$type}': variable '{$key}' has no sample data entry");
            }
        }
    }

    /**
     * Test that each type has required variables defined.
     */
    public function test_get_required_variables_returns_array_for_all_types(): void {
        foreach (notification_type::get_types() as $type => $stringkey) {
            $required = notification_type::get_required_variables($type);
            $this->assertIsArray($required, "No required variables for type '{$type}'");
            $this->assertNotEmpty($required, "Empty required variables for type '{$type}'");
            // Common required variables must always be present.
            $this->assertContains('site_name', $required,
                "Type '{$type}' missing common required variable 'site_name'");
            $this->assertContains('recipient_firstname', $required,
                "Type '{$type}' missing common required variable 'recipient_firstname'");
        }
    }

    /**
     * Test that required variables are a subset of available variables.
     */
    public function test_required_variables_are_subset_of_available(): void {
        foreach (notification_type::get_types() as $type => $stringkey) {
            $required = notification_type::get_required_variables($type);
            $available = array_keys(notification_type::get_variables($type));
            foreach ($required as $key) {
                $this->assertContains($key, $available,
                    "Type '{$type}': required variable '{$key}' is not in available variables");
            }
        }
    }

    /**
     * Test has_required_variables returns true when all are present.
     */
    public function test_has_required_variables_true_when_complete(): void {
        $data = notification_type::get_sample_data(notification_type::TYPE_PASSWORD_RESET);
        $this->assertTrue(notification_type::has_required_variables(
            notification_type::TYPE_PASSWORD_RESET, $data));
    }

    /**
     * Test has_required_variables returns false when a required var is missing.
     */
    public function test_has_required_variables_false_when_incomplete(): void {
        $data = notification_type::get_sample_data(notification_type::TYPE_PASSWORD_RESET);
        unset($data['reset_url']);
        $this->assertFalse(notification_type::has_required_variables(
            notification_type::TYPE_PASSWORD_RESET, $data));
    }

    /**
     * Test has_required_variables returns false when a required var is empty string.
     */
    public function test_has_required_variables_false_when_empty(): void {
        $data = notification_type::get_sample_data(notification_type::TYPE_COURSE_ENROLMENT);
        $data['course_fullname'] = '';
        $this->assertFalse(notification_type::has_required_variables(
            notification_type::TYPE_COURSE_ENROLMENT, $data));
    }

    /**
     * Test that get_type_names returns localised strings (not just keys).
     */
    public function test_get_type_names_returns_strings(): void {
        $this->resetAfterTest();
        $names = notification_type::get_type_names();
        $this->assertCount(6, $names);
        foreach ($names as $key => $name) {
            $this->assertIsString($name);
            $this->assertNotEmpty($name);
            // Should not be a raw string key (i.e. actually localised).
            $this->assertStringNotContainsString('type_', $name,
                "Type name for '{$key}' looks like an unlocalised string key");
        }
    }
}
