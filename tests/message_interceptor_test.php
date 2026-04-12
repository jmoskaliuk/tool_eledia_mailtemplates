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
 * Tests for the message_interceptor class.
 *
 * @package    tool_eledia_mailtemplates
 * @category   test
 * @covers     \tool_eledia_mailtemplates\message_interceptor
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates\tests;

use tool_eledia_mailtemplates\message_interceptor;
use tool_eledia_mailtemplates\template_manager;
use tool_eledia_mailtemplates\notification_type;
use core\message\message;

defined('MOODLE_INTERNAL') || die();

/**
 * Integration tests for message_interceptor.
 */
class message_interceptor_test extends \advanced_testcase {

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    // -------------------------------------------------------------------------
    // is_supported / get_notification_type
    // -------------------------------------------------------------------------

    /**
     * Test that known Moodle message types are recognised as supported.
     */
    public function test_is_supported_returns_true_for_known_types(): void {
        $this->assertTrue(message_interceptor::is_supported('moodle', 'resetpassword'));
        $this->assertTrue(message_interceptor::is_supported('mod_forum', 'posts'));
        $this->assertTrue(message_interceptor::is_supported('mod_assign', 'assign_notification'));
        $this->assertTrue(message_interceptor::is_supported('moodle', 'emailconfirmation'));
    }

    /**
     * Test that unknown Moodle message types are not supported.
     */
    public function test_is_supported_returns_false_for_unknown_types(): void {
        $this->assertFalse(message_interceptor::is_supported('mod_quiz', 'attempt_submitted'));
        $this->assertFalse(message_interceptor::is_supported('unknown', 'whatever'));
        $this->assertFalse(message_interceptor::is_supported('', ''));
    }

    /**
     * Test the component/name → notification type mapping.
     */
    public function test_get_notification_type_returns_correct_type(): void {
        $this->assertEquals(
            notification_type::TYPE_PASSWORD_RESET,
            message_interceptor::get_notification_type('moodle', 'resetpassword')
        );
        $this->assertEquals(
            notification_type::TYPE_FORUM_POST,
            message_interceptor::get_notification_type('mod_forum', 'posts')
        );
        $this->assertEquals(
            notification_type::TYPE_ASSIGNMENT_GRADING,
            message_interceptor::get_notification_type('mod_assign', 'assign_notification')
        );
        $this->assertEquals(
            notification_type::TYPE_USER_REGISTRATION,
            message_interceptor::get_notification_type('moodle', 'emailconfirmation')
        );
    }

    /**
     * Test that an unknown component/name returns null.
     */
    public function test_get_notification_type_returns_null_for_unknown(): void {
        $this->assertNull(message_interceptor::get_notification_type('mod_quiz', 'attempt'));
    }

    // -------------------------------------------------------------------------
    // process_message() — fallback cases
    // -------------------------------------------------------------------------

    /**
     * Test that process_message returns false (fallback) when no template exists.
     */
    public function test_process_message_returns_false_without_template(): void {
        $recipient = $this->getDataGenerator()->create_user();
        $message = $this->build_test_message('moodle', 'resetpassword', $recipient);

        $result = message_interceptor::process_message($message);
        $this->assertFalse($result);
    }

    /**
     * Test that process_message returns false for unsupported message types.
     */
    public function test_process_message_returns_false_for_unsupported_type(): void {
        $recipient = $this->getDataGenerator()->create_user();
        $message = $this->build_test_message('mod_quiz', 'attempt_submitted', $recipient);

        $result = message_interceptor::process_message($message);
        $this->assertFalse($result);
    }

    /**
     * Test that process_message returns false when template is inactive.
     */
    public function test_process_message_returns_false_for_inactive_template(): void {
        $this->create_template(notification_type::TYPE_PASSWORD_RESET, active: 0);

        $recipient = $this->getDataGenerator()->create_user();
        $message = $this->build_test_message('moodle', 'resetpassword', $recipient);

        $result = message_interceptor::process_message($message);
        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // process_message() — template applied
    // -------------------------------------------------------------------------

    /**
     * Test that process_message returns true and modifies the message when a template exists.
     */
    public function test_process_message_applies_template(): void {
        $this->create_template(
            notification_type::TYPE_PASSWORD_RESET,
            subject: 'Hello {{recipient_firstname}}, reset your password',
            body: '<p>Hello {{recipient_firstname}}, click here to reset.</p>'
        );

        $recipient = $this->getDataGenerator()->create_user(['firstname' => 'Anna']);
        $message   = $this->build_test_message('moodle', 'resetpassword', $recipient);

        $result = message_interceptor::process_message($message);

        $this->assertTrue($result);
        $this->assertStringContainsString('Anna', $message->subject);
        $this->assertStringContainsString('Anna', $message->fullmessagehtml);
        $this->assertNotEmpty($message->fullmessage); // Plain text generated.
    }

    /**
     * Test that the original message content is unchanged after a failed override (fallback).
     */
    public function test_process_message_does_not_alter_message_on_fallback(): void {
        $recipient = $this->getDataGenerator()->create_user();
        $message   = $this->build_test_message('moodle', 'resetpassword', $recipient);
        $originalsubject = $message->subject;
        $originalbody    = $message->fullmessagehtml;

        message_interceptor::process_message($message);

        $this->assertEquals($originalsubject, $message->subject);
        $this->assertEquals($originalbody, $message->fullmessagehtml);
    }

    /**
     * Test that an incomplete template (missing body) does not override.
     */
    public function test_process_message_returns_false_for_incomplete_template(): void {
        $this->create_template(
            notification_type::TYPE_PASSWORD_RESET,
            subject: 'Reset password',
            body: ''   // Empty body — incomplete.
        );

        $recipient = $this->getDataGenerator()->create_user();
        $message   = $this->build_test_message('moodle', 'resetpassword', $recipient);

        $result = message_interceptor::process_message($message);
        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a test message object.
     *
     * @param string $component Message component.
     * @param string $name Message name.
     * @param \stdClass $recipient Recipient user.
     * @return message
     */
    private function build_test_message(string $component, string $name, \stdClass $recipient): message {
        $message                    = new message();
        $message->component         = $component;
        $message->name              = $name;
        $message->userfrom          = \core_user::get_noreply_user();
        $message->userto            = $recipient;
        $message->subject           = 'Original subject';
        $message->fullmessage       = 'Original plain text';
        $message->fullmessagehtml   = '<p>Original HTML</p>';
        $message->fullmessageformat = FORMAT_HTML;
        $message->notification      = 1;
        return $message;
    }

    /**
     * Create a test template record in the DB.
     *
     * @param string $type Notification type.
     * @param string $subject Template subject.
     * @param string $body Template body HTML.
     * @param int $active Whether the template is active.
     * @return int The template ID.
     */
    private function create_template(
        string $type,
        string $subject = 'Test Subject {{site_name}}',
        string $body = '<p>Hello {{recipient_firstname}}</p>',
        int $active = 1
    ): int {
        $data = (object) [
            'notification_type' => $type,
            'contextid'         => 0,
            'contextlevel'      => CONTEXT_SYSTEM,
            'subject'           => $subject,
            'body_html'         => $body,
            'active'            => $active,
        ];
        return template_manager::save_template($data);
    }
}
