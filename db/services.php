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
 * External service definitions for tool_eledia_mailtemplates.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'tool_eledia_mailtemplates_get_type_variables' => [
        'classname'     => \tool_eledia_mailtemplates\external\get_type_variables::class,
        'methodname'    => 'execute',
        'description'   => 'Return available variables for a notification type.',
        'type'          => 'read',
        'capabilities'  => 'tool/eledia_mailtemplates:manage',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'tool_eledia_mailtemplates_render_preview' => [
        'classname' => \tool_eledia_mailtemplates\external\render_preview::class,
        'methodname' => 'execute',
        'description' => 'Render a template preview with sample data and branding.',
        'type' => 'read',
        'capabilities' => 'tool/eledia_mailtemplates:manage',
        'ajax' => true,
        'loginrequired' => true,
    ],
];
