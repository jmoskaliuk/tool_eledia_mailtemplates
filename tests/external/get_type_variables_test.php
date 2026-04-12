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
 * Tests for the get_type_variables external function.
 *
 * @package    tool_eledia_mailtemplates
 * @category   test
 * @covers     \tool_eledia_mailtemplates\external\get_type_variables
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates\tests\external;

use tool_eledia_mailtemplates\external\get_type_variables;
use tool_eledia_mailtemplates\notification_type;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for get_type_variables external function.
 */
class get_type_variables_test extends \advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Test that variables are returned for a valid notification type.
     */
    public function test_execute_returns_variables_for_valid_type(): void {
        $result = get_type_variables::execute(notification_type::TYPE_PASSWORD_RESET);

        $this->assertArrayHasKey('variables', $result);
        $this->assertNotEmpty($result['variables']);

        $keys = array_column($result['variables'], 'key');
        $this->assertContains('site_name', $keys);
        $this->assertContains('recipient_firstname', $keys);
        $this->assertContains('reset_url', $keys);
    }

    /**
     * Test that each variable entry has the required fields.
     */
    public function test_execute_returns_well_formed_variable_entries(): void {
        $result = get_type_variables::execute(notification_type::TYPE_FORUM_POST);
        foreach ($result['variables'] as $var) {
            $this->assertArrayHasKey('key', $var);
            $this->assertArrayHasKey('placeholder', $var);
            $this->assertArrayHasKey('description', $var);
            $this->assertEquals('{{' . $var['key'] . '}}', $var['placeholder']);
        }
    }

    /**
     * Test that an invalid type returns an empty variables list.
     */
    public function test_execute_returns_empty_for_invalid_type(): void {
        $result = get_type_variables::execute('totally_invalid_type');
        $this->assertArrayHasKey('variables', $result);
        $this->assertEmpty($result['variables']);
    }

    /**
     * Test that a non-admin user without capability cannot call the function.
     */
    public function test_execute_requires_manage_capability(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(\required_capability_exception::class);
        get_type_variables::execute(notification_type::TYPE_PASSWORD_RESET);
    }
}
