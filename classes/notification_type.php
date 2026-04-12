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
 * Notification type registry for tool_eledia_mailtemplates.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates;

/**
 * Registry of supported notification types and their available variables.
 */
class notification_type {

    /** @var string Password reset notification. */
    public const TYPE_PASSWORD_RESET = 'password_reset';

    /** @var string Course enrolment confirmation. */
    public const TYPE_COURSE_ENROLMENT = 'course_enrolment';

    /** @var string Forum post notification. */
    public const TYPE_FORUM_POST = 'forum_post';

    /** @var string Assignment grading notification. */
    public const TYPE_ASSIGNMENT_GRADING = 'assignment_grading';

    /** @var string User registration confirmation. */
    public const TYPE_USER_REGISTRATION = 'user_registration';

    /** @var string Admin / automated notifications. */
    public const TYPE_ADMIN_NOTIFICATION = 'admin_notification';

    /**
     * Get all supported notification types.
     *
     * @return array Associative array of type identifier => language string key.
     */
    public static function get_types(): array {
        return [
            self::TYPE_PASSWORD_RESET => 'type_password_reset',
            self::TYPE_COURSE_ENROLMENT => 'type_course_enrolment',
            self::TYPE_FORUM_POST => 'type_forum_post',
            self::TYPE_ASSIGNMENT_GRADING => 'type_assignment_grading',
            self::TYPE_USER_REGISTRATION => 'type_user_registration',
            self::TYPE_ADMIN_NOTIFICATION => 'type_admin_notification',
        ];
    }

    /**
     * Get localised names for all notification types.
     *
     * @return array Associative array of type identifier => localised name.
     */
    public static function get_type_names(): array {
        $types = self::get_types();
        $names = [];
        foreach ($types as $key => $stringkey) {
            $names[$key] = get_string($stringkey, 'tool_eledia_mailtemplates');
        }
        return $names;
    }

    /**
     * Check whether a given type identifier is valid.
     *
     * @param string $type The notification type identifier.
     * @return bool True if the type is supported.
     */
    public static function is_valid(string $type): bool {
        return array_key_exists($type, self::get_types());
    }

    /**
     * Get the available variables for a notification type.
     *
     * Each variable is a key that can be used in templates as {{key}}.
     *
     * @param string $type The notification type identifier.
     * @return array Associative array of variable key => description string.
     */
    public static function get_variables(string $type): array {
        // Common variables available for all types.
        $common = [
            'site_name' => 'Site name',
            'site_url' => 'Site URL',
            'recipient_firstname' => 'Recipient first name',
            'recipient_lastname' => 'Recipient last name',
            'recipient_fullname' => 'Recipient full name',
            'recipient_email' => 'Recipient email address',
        ];

        $specific = match ($type) {
            self::TYPE_PASSWORD_RESET => [
                'reset_url' => 'Password reset link',
                'reset_expiry' => 'Link expiry time',
            ],
            self::TYPE_COURSE_ENROLMENT => [
                'course_fullname' => 'Course full name',
                'course_shortname' => 'Course short name',
                'course_url' => 'Course URL',
                'enrol_method' => 'Enrolment method',
                'enrol_startdate' => 'Enrolment start date',
            ],
            self::TYPE_FORUM_POST => [
                'forum_name' => 'Forum name',
                'forum_url' => 'Forum URL',
                'post_subject' => 'Post subject',
                'post_content' => 'Post content (excerpt)',
                'post_author' => 'Post author name',
                'post_url' => 'Direct link to post',
                'course_fullname' => 'Course full name',
            ],
            self::TYPE_ASSIGNMENT_GRADING => [
                'assignment_name' => 'Assignment name',
                'assignment_url' => 'Assignment URL',
                'grade' => 'Grade awarded',
                'grade_max' => 'Maximum grade',
                'feedback' => 'Feedback text (excerpt)',
                'grader_name' => 'Grader name',
                'course_fullname' => 'Course full name',
            ],
            self::TYPE_USER_REGISTRATION => [
                'username' => 'Username',
                'confirm_url' => 'Confirmation link',
            ],
            self::TYPE_ADMIN_NOTIFICATION => [
                'admin_message' => 'Admin message content',
                'admin_name' => 'Admin sender name',
            ],
            default => [],
        };

        return array_merge($common, $specific);
    }

    /**
     * Get sample data for preview rendering of a notification type.
     *
     * @param string $type The notification type identifier.
     * @return array Associative array of variable key => sample value.
     */
    public static function get_sample_data(string $type): array {
        $common = [
            'site_name' => 'My Moodle Site',
            'site_url' => 'https://moodle.example.com',
            'recipient_firstname' => 'Max',
            'recipient_lastname' => 'Mustermann',
            'recipient_fullname' => 'Max Mustermann',
            'recipient_email' => 'max@example.com',
        ];

        $specific = match ($type) {
            self::TYPE_PASSWORD_RESET => [
                'reset_url' => 'https://moodle.example.com/login/forgot_password.php?token=abc123',
                'reset_expiry' => '30 minutes',
            ],
            self::TYPE_COURSE_ENROLMENT => [
                'course_fullname' => 'Introduction to PHP Programming',
                'course_shortname' => 'PHP101',
                'course_url' => 'https://moodle.example.com/course/view.php?id=42',
                'enrol_method' => 'Manual enrolment',
                'enrol_startdate' => '15 April 2026',
            ],
            self::TYPE_FORUM_POST => [
                'forum_name' => 'General Discussion',
                'forum_url' => 'https://moodle.example.com/mod/forum/view.php?id=7',
                'post_subject' => 'Welcome to the course!',
                'post_content' => 'Hello everyone, welcome to this course...',
                'post_author' => 'Dr. Anna Schmidt',
                'post_url' => 'https://moodle.example.com/mod/forum/discuss.php?d=99#p123',
                'course_fullname' => 'Introduction to PHP Programming',
            ],
            self::TYPE_ASSIGNMENT_GRADING => [
                'assignment_name' => 'Homework 1: Variables and Types',
                'assignment_url' => 'https://moodle.example.com/mod/assign/view.php?id=15',
                'grade' => '85',
                'grade_max' => '100',
                'feedback' => 'Great work! Pay attention to variable naming conventions.',
                'grader_name' => 'Prof. Mueller',
                'course_fullname' => 'Introduction to PHP Programming',
            ],
            self::TYPE_USER_REGISTRATION => [
                'username' => 'max.mustermann',
                'confirm_url' => 'https://moodle.example.com/login/confirm.php?data=abc/def',
            ],
            self::TYPE_ADMIN_NOTIFICATION => [
                'admin_message' => 'The system will be undergoing maintenance on Sunday from 02:00 to 06:00.',
                'admin_name' => 'Site Administrator',
            ],
            default => [],
        };

        return array_merge($common, $specific);
    }
}
