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
 * Message interceptor — maps Moodle messages to templates and applies overrides.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates;

use core\message\message;
use stdClass;

/**
 * Central logic for intercepting Moodle messages and applying template overrides.
 *
 * Maps Moodle's internal message component+name to our notification type identifiers,
 * resolves the appropriate template, and replaces the message content.
 */
class message_interceptor {

    /**
     * Mapping of Moodle message component+name to our notification type identifiers.
     *
     * Format: 'component/name' => notification_type constant.
     * Multiple Moodle message types can map to the same notification type.
     *
     * @var array
     */
    private const MESSAGE_MAP = [
        // Password reset.
        'moodle/resetpassword' => notification_type::TYPE_PASSWORD_RESET,

        // Course enrolment confirmations.
        'enrol_manual/expiry_notification' => notification_type::TYPE_COURSE_ENROLMENT,
        'enrol_self/expiry_notification' => notification_type::TYPE_COURSE_ENROLMENT,
        'core/enrolcoursewelcome' => notification_type::TYPE_COURSE_ENROLMENT,

        // Forum post notifications.
        'mod_forum/posts' => notification_type::TYPE_FORUM_POST,
        'mod_forum/digests' => notification_type::TYPE_FORUM_POST,

        // Assignment grading.
        'mod_assign/assign_notification' => notification_type::TYPE_ASSIGNMENT_GRADING,

        // User registration / email confirmation.
        'moodle/emailconfirmation' => notification_type::TYPE_USER_REGISTRATION,

        // Admin / automated notifications.
        'moodle/notices' => notification_type::TYPE_ADMIN_NOTIFICATION,
        'moodle/instantmessage' => notification_type::TYPE_ADMIN_NOTIFICATION,
    ];

    /**
     * Determine the notification type for a given Moodle message.
     *
     * @param string $component The message component (e.g. 'mod_forum').
     * @param string $name The message name (e.g. 'posts').
     * @return string|null The notification type identifier, or null if not supported.
     */
    public static function get_notification_type(string $component, string $name): ?string {
        $key = $component . '/' . $name;
        return self::MESSAGE_MAP[$key] ?? null;
    }

    /**
     * Check whether a given Moodle message is supported for template override.
     *
     * @param string $component The message component.
     * @param string $name The message name.
     * @return bool True if the message type is supported.
     */
    public static function is_supported(string $component, string $name): bool {
        return self::get_notification_type($component, $name) !== null;
    }

    /**
     * Process a message and apply a template override if available.
     *
     * This is the main entry point called by the hook/event handler.
     * It determines the notification type, resolves a template, and replaces
     * the message content. If no template is found, the message is unchanged.
     *
     * @param message $message The Moodle message object (modified in place).
     * @return bool True if a template was applied, false if fallback to default.
     */
    public static function process_message(message $message): bool {
        $type = self::get_notification_type($message->component, $message->name);
        if ($type === null) {
            return false;
        }

        // Determine context for template resolution.
        $context = self::get_context_for_message($message);

        // Resolve the template.
        $template = template_manager::resolve_template($type, $context);
        if ($template === null) {
            // No template — fallback to Moodle default behaviour.
            return false;
        }

        // Template found but incomplete — also fallback.
        if (empty($template->subject) || empty($template->body_html)) {
            return false;
        }

        // Build the variable data from the message and context.
        $data = self::build_variable_data($type, $message, $context);

        // Check that all required variables are present. If not, fall back to default.
        if (!notification_type::has_required_variables($type, $data)) {
            return false;
        }

        // Resolve variables in subject and body.
        $resolvedsubject = variable_resolver::resolve($template->subject, $data);
        $resolvedbody = variable_resolver::resolve($template->body_html, $data);

        // Apply branding.
        $brandedhtml = branding_manager::apply_branding($resolvedbody);

        // Generate plain text.
        $plaintext = variable_resolver::html_to_plaintext($resolvedbody);

        // Replace message content.
        $message->subject = $resolvedsubject;
        $message->fullmessagehtml = $brandedhtml;
        $message->fullmessage = $plaintext;
        $message->fullmessageformat = FORMAT_HTML;

        // Update small message (notification popup) with subject.
        if (empty($message->smallmessage)) {
            $message->smallmessage = $resolvedsubject;
        }

        return true;
    }

    /**
     * Determine the Moodle context for template resolution from a message.
     *
     * @param message $message The Moodle message.
     * @return \core\context The determined context.
     */
    private static function get_context_for_message(message $message): \core\context {
        // Try to get context from the message's contexturl or courseid.
        if (!empty($message->courseid) && $message->courseid > 0) {
            try {
                return \core\context\course::instance($message->courseid);
            } catch (\Exception $e) {
                // Course doesn't exist, fall through.
            }
        }

        // Try to extract course/module context from contexturl.
        if (!empty($message->contexturl)) {
            $context = self::parse_context_from_url($message->contexturl);
            if ($context !== null) {
                return $context;
            }
        }

        // Default: system context.
        return \core\context\system::instance();
    }

    /**
     * Try to determine a Moodle context from a URL.
     *
     * @param string $url The context URL.
     * @return \core\context|null The parsed context, or null.
     */
    private static function parse_context_from_url(string $url): ?\core\context {
        // Try to match /mod/<type>/view.php?id=<cmid>.
        if (preg_match('/\/mod\/\w+\/view\.php\?id=(\d+)/', $url, $matches)) {
            try {
                return \core\context\module::instance((int)$matches[1]);
            } catch (\Exception $e) {
                // Module doesn't exist.
            }
        }

        // Try to match /course/view.php?id=<courseid>.
        if (preg_match('/\/course\/view\.php\?id=(\d+)/', $url, $matches)) {
            try {
                return \core\context\course::instance((int)$matches[1]);
            } catch (\Exception $e) {
                // Course doesn't exist.
            }
        }

        return null;
    }

    /**
     * Build the variable data array for template resolution.
     *
     * Merges common system/recipient data with type-specific data extracted
     * from the message and its context.
     *
     * @param string $type The notification type.
     * @param message $message The Moodle message.
     * @param \core\context $context The resolved context.
     * @return array Associative array of variable key => value.
     */
    private static function build_variable_data(
        string $type,
        message $message,
        \core\context $context
    ): array {
        global $CFG, $DB;

        // Common variables.
        $data = [
            'site_name' => format_string(get_site()->fullname),
            'site_url' => $CFG->wwwroot,
        ];

        // Recipient data.
        $recipient = self::get_user_from_message($message, 'to');
        if ($recipient) {
            $data['recipient_firstname'] = $recipient->firstname;
            $data['recipient_lastname'] = $recipient->lastname;
            $data['recipient_fullname'] = fullname($recipient);
            $data['recipient_email'] = $recipient->email;
        }

        // Type-specific data.
        $data = array_merge($data, self::build_type_specific_data($type, $message, $context));

        return $data;
    }

    /**
     * Get a user object from the message (either sender or recipient).
     *
     * @param message $message The message.
     * @param string $direction 'to' for recipient, 'from' for sender.
     * @return stdClass|null The user record or null.
     */
    private static function get_user_from_message(message $message, string $direction): ?stdClass {
        global $DB;

        $user = ($direction === 'to') ? ($message->userto ?? null) : ($message->userfrom ?? null);

        if ($user === null) {
            return null;
        }

        // If it's already a full user object.
        if (is_object($user) && !empty($user->firstname)) {
            return $user;
        }

        // If it's a user ID.
        $userid = is_object($user) ? ($user->id ?? 0) : (int)$user;
        if ($userid > 0) {
            return $DB->get_record('user', ['id' => $userid]) ?: null;
        }

        return null;
    }

    /**
     * Build type-specific variable data from the message content and context.
     *
     * @param string $type The notification type.
     * @param message $message The Moodle message.
     * @param \core\context $context The resolved context.
     * @return array Additional variable key => value pairs.
     */
    private static function build_type_specific_data(
        string $type,
        message $message,
        \core\context $context
    ): array {
        global $DB;

        $data = [];

        switch ($type) {
            case notification_type::TYPE_PASSWORD_RESET:
                // Extract reset URL from original message body if available.
                if (preg_match('/(https?:\/\/\S*forgot_password\S*)/', $message->fullmessage ?? '', $m)) {
                    $data['reset_url'] = $m[1];
                }
                $data['reset_expiry'] = get_string('passwordforgotteninstructions2', 'moodle');
                break;

            case notification_type::TYPE_COURSE_ENROLMENT:
                if ($context instanceof \core\context\course) {
                    $course = $DB->get_record('course', ['id' => $context->instanceid]);
                    if ($course) {
                        $data['course_fullname'] = format_string($course->fullname);
                        $data['course_shortname'] = format_string($course->shortname);
                        $data['course_url'] = (new \moodle_url('/course/view.php',
                            ['id' => $course->id]))->out();
                    }
                }
                $data['enrol_method'] = '';
                $data['enrol_startdate'] = '';
                break;

            case notification_type::TYPE_FORUM_POST:
                $data['forum_name'] = '';
                $data['forum_url'] = '';
                $data['post_subject'] = $message->subject ?? '';
                $data['post_content'] = '';
                $data['post_author'] = '';
                $data['post_url'] = $message->contexturl ?? '';
                // Try to get course name from context.
                if ($context instanceof \core\context\module) {
                    $cm = get_coursemodule_from_id('forum', $context->instanceid);
                    if ($cm) {
                        $data['forum_name'] = format_string($cm->name);
                        $data['forum_url'] = (new \moodle_url('/mod/forum/view.php',
                            ['id' => $cm->id]))->out();
                        $course = $DB->get_record('course', ['id' => $cm->course]);
                        if ($course) {
                            $data['course_fullname'] = format_string($course->fullname);
                        }
                    }
                }
                $sender = self::get_user_from_message($message, 'from');
                if ($sender && $sender->id > 0) {
                    $data['post_author'] = fullname($sender);
                }
                break;

            case notification_type::TYPE_ASSIGNMENT_GRADING:
                $data['assignment_name'] = '';
                $data['assignment_url'] = $message->contexturl ?? '';
                $data['grade'] = '';
                $data['grade_max'] = '';
                $data['feedback'] = '';
                $data['grader_name'] = '';
                if ($context instanceof \core\context\module) {
                    $cm = get_coursemodule_from_id('assign', $context->instanceid);
                    if ($cm) {
                        $data['assignment_name'] = format_string($cm->name);
                        $data['assignment_url'] = (new \moodle_url('/mod/assign/view.php',
                            ['id' => $cm->id]))->out();
                        $course = $DB->get_record('course', ['id' => $cm->course]);
                        if ($course) {
                            $data['course_fullname'] = format_string($course->fullname);
                        }
                    }
                }
                $grader = self::get_user_from_message($message, 'from');
                if ($grader && $grader->id > 0) {
                    $data['grader_name'] = fullname($grader);
                }
                break;

            case notification_type::TYPE_USER_REGISTRATION:
                $recipient = self::get_user_from_message($message, 'to');
                $data['username'] = $recipient->username ?? '';
                // Extract confirmation URL from original message.
                if (preg_match('/(https?:\/\/\S*confirm\.php\S*)/', $message->fullmessage ?? '', $m)) {
                    $data['confirm_url'] = $m[1];
                }
                break;

            case notification_type::TYPE_ADMIN_NOTIFICATION:
                $data['admin_message'] = $message->fullmessage ?? '';
                $sender = self::get_user_from_message($message, 'from');
                $data['admin_name'] = ($sender && $sender->id > 0) ? fullname($sender) : '';
                break;
        }

        return $data;
    }
}
