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
 * Plugin library functions for tool_eledia_mailtemplates.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Serve plugin files (logo image) via Moodle's pluginfile system.
 *
 * @param stdClass $course The course object (unused — system-level plugin).
 * @param stdClass $cm The course module object (unused).
 * @param context $context The context.
 * @param string $filearea The file area name.
 * @param array $args Extra arguments (itemid, filepath, filename).
 * @param bool $forcedownload Whether to force a download.
 * @param array $options Additional options.
 * @return bool False if file not found.
 */
function tool_eledia_mailtemplates_pluginfile(
    $course,
    $cm,
    $context,
    string $filearea,
    array $args,
    bool $forcedownload,
    array $options = []
): bool {
    // Only serve from system context.
    if ($context->contextlevel !== CONTEXT_SYSTEM) {
        return false;
    }

    // Only the logo file area is supported.
    if ($filearea !== 'logo') {
        return false;
    }

    // Require login to serve branding assets.
    require_login();

    $itemid  = (int) array_shift($args);
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $fs   = get_file_storage();
    $file = $fs->get_file($context->id, 'tool_eledia_mailtemplates', 'logo', $itemid, $filepath, $filename);

    if (!$file || $file->is_directory()) {
        return false;
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}
