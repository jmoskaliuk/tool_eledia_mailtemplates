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
 * Hook callbacks for tool_eledia_mailtemplates.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates;

/**
 * Hook callback handlers for message interception.
 */
class hook_callbacks {

    /**
     * Intercept a message before it is sent and apply a template override if available.
     *
     * This callback is invoked by Moodle's messaging system via the
     * \core\hook\message\before_message_sent hook (Moodle 5.x).
     *
     * @param \core\hook\message\before_message_sent $hook The hook instance.
     * @return void
     */
    public static function before_message_sent(\core\hook\message\before_message_sent $hook): void {
        $message = $hook->get_message();

        // Only process notification-type messages (not personal messages).
        if (empty($message->notification)) {
            return;
        }

        // Check if this message type is supported.
        if (!message_interceptor::is_supported($message->component, $message->name)) {
            return;
        }

        // Apply template override — modifies the message object in place.
        message_interceptor::process_message($message);
    }
}
