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
 * Edit/create a template.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use tool_eledia_mailtemplates\template_manager;
use tool_eledia_mailtemplates\notification_type;
use tool_eledia_mailtemplates\form\template_form;

require_login();
$context = \core\context\system::instance();
require_capability('tool/eledia_mailtemplates:manage', $context);

$id = optional_param('id', 0, PARAM_INT);

$baseurl = new moodle_url('/admin/tool/eledia_mailtemplates/index.php');
$editurl = new moodle_url('/admin/tool/eledia_mailtemplates/edit.php', $id ? ['id' => $id] : []);

$PAGE->set_url($editurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

$template = null;
if ($id > 0) {
    $template = template_manager::get_template($id);
    if (!$template) {
        throw new \moodle_exception('invalidrecord');
    }
    $PAGE->set_title(get_string('edit_template', 'tool_eledia_mailtemplates'));
} else {
    $PAGE->set_title(get_string('create_template', 'tool_eledia_mailtemplates'));
}
$PAGE->set_heading(get_string('pluginname', 'tool_eledia_mailtemplates'));

$form = new template_form($editurl, ['template' => $template]);

if ($form->is_cancelled()) {
    redirect($baseurl);
} else if ($data = $form->get_data()) {
    $record = new stdClass();
    $record->id = $data->id ?? 0;
    $record->notification_type = $data->notification_type;
    $record->contextid = $data->contextid ?? 0;
    $record->contextlevel = $data->contextlevel ?? CONTEXT_SYSTEM;
    $record->subject = $data->subject;
    $record->body_html = $data->body_html['text'];
    $record->active = $data->active ?? 0;

    template_manager::save_template($record);
    redirect($baseurl, get_string('template_saved', 'tool_eledia_mailtemplates'));
}

// Set form data for editing.
if ($template) {
    $formdata = clone $template;
    $formdata->body_html = [
        'text' => $template->body_html,
        'format' => FORMAT_HTML,
    ];
    $form->set_data($formdata);
}

echo $OUTPUT->header();

// Initialise the live preview module — no data passed as args,
// variable lists are loaded on demand via AJAX.
$PAGE->requires->js_call_amd('tool_eledia_mailtemplates/preview', 'init');

$form->display();
echo $OUTPUT->footer();
