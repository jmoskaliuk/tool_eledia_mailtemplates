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
 * Main admin page for managing email templates.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use tool_eledia_mailtemplates\template_manager;
use tool_eledia_mailtemplates\notification_type;

require_login();
$context = \core\context\system::instance();
require_capability('tool/eledia_mailtemplates:manage', $context);

$action = optional_param('action', 'list', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);

$baseurl = new moodle_url('/admin/tool/eledia_mailtemplates/index.php');
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'tool_eledia_mailtemplates'));
$PAGE->set_heading(get_string('pluginname', 'tool_eledia_mailtemplates'));

// Handle delete action.
if ($action === 'delete' && $id > 0) {
    require_sesskey();
    template_manager::delete_template($id);
    redirect($baseurl, get_string('template_deleted', 'tool_eledia_mailtemplates'));
}

echo $OUTPUT->header();

// Template list view.
$templates = template_manager::get_templates();
$typenames = notification_type::get_type_names();

$templatedata = [];
foreach ($templates as $template) {
    $templatedata[] = [
        'id' => $template->id,
        'notification_type' => $typenames[$template->notification_type] ?? $template->notification_type,
        'subject' => format_string($template->subject),
        'active' => (bool) $template->active,
        'edit_url' => (new moodle_url('/admin/tool/eledia_mailtemplates/edit.php', ['id' => $template->id]))->out(),
        'delete_url' => (new moodle_url($baseurl, [
            'action' => 'delete',
            'id' => $template->id,
            'sesskey' => sesskey(),
        ]))->out(),
    ];
}

$rendercontext = [
    'templates' => $templatedata,
    'has_templates' => !empty($templatedata),
    'create_url' => (new moodle_url('/admin/tool/eledia_mailtemplates/edit.php'))->out(),
    'branding_url' => (new moodle_url('/admin/tool/eledia_mailtemplates/branding.php'))->out(),
];

echo $OUTPUT->render_from_template('tool_eledia_mailtemplates/template_list', $rendercontext);

echo $OUTPUT->footer();
