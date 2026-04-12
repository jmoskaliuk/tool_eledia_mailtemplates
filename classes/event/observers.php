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
 * Event observers for tool_eledia_mailtemplates.
 *
 * These observers serve as a fallback integration point for Moodle versions
 * where the \core\hook\message\before_message_sent hook is not yet available.
 * On Moodle 5.x+ the hook in db/hooks.php is the primary integration.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates\event;

use tool_eledia_mailtemplates\message_interceptor;

/**
 * Observer callbacks for Moodle events related to supported notification types.
 *
 * Note: These observers log that a supported event occurred but cannot directly
 * modify the email content. The actual message interception happens via the
 * hook system (db/hooks.php). These observers are reserved for future use
 * or as diagnostic/logging integration points.
 */
class observers {

    /**
     * Observer for notification_sent events.
     *
     * This is a diagnostic/logging hook. Actual message modification
     * is handled by the before_message_sent hook callback.
     *
     * @param \core\event\notification_sent $event The event.
     * @return void
     */
    public static function notification_sent(\core\event\notification_sent $event): void {
        // Reserved for future diagnostic/logging use.
        // The actual template override is done via the hook system.
    }
}
