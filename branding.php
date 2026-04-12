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
 * Branding configuration page.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

use tool_eledia_mailtemplates\branding_manager;
use tool_eledia_mailtemplates\form\branding_form;

require_login();
$context = \core\context\system::instance();
require_capability('tool/eledia_mailtemplates:manage', $context);

$baseurl = new moodle_url('/admin/tool/eledia_mailtemplates/index.php');
$pageurl = new moodle_url('/admin/tool/eledia_mailtemplates/branding.php');

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('branding', 'tool_eledia_mailtemplates'));
$PAGE->set_heading(get_string('pluginname', 'tool_eledia_mailtemplates'));

$branding = branding_manager::get_branding();
$form     = new branding_form($pageurl, ['branding' => $branding]);

if ($form->is_cancelled()) {
    redirect($baseurl);
} else if ($data = $form->get_data()) {
    branding_manager::save_branding($data);
    redirect($baseurl, get_string('branding_saved', 'tool_eledia_mailtemplates'));
}

// Prepare form data: load existing values + prepare logo draft area.
$formdata                  = clone $branding;
$formdata->logo            = branding_manager::get_logo_draftitemid();
$formdata->footercontent   = [
    'text'   => $branding->footercontent ?? '',
    'format' => FORMAT_HTML,
];
$formdata->disclaimer = [
    'text'   => $branding->disclaimer ?? '',
    'format' => FORMAT_HTML,
];
$form->set_data($formdata);

echo $OUTPUT->header();
$form->display();
echo $OUTPUT->footer();
