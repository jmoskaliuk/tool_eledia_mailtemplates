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
 * Template edit form.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates\form;

use tool_eledia_mailtemplates\notification_type;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for creating and editing email templates.
 */
class template_form extends \moodleform {

    /**
     * Define the form elements.
     *
     * @return void
     */
    protected function definition(): void {
        $mform = $this->_form;
        $template = $this->_customdata['template'] ?? null;

        // Hidden ID field.
        $mform->addElement('hidden', 'id', $template->id ?? 0);
        $mform->setType('id', PARAM_INT);

        // Notification type.
        $typeoptions = ['' => get_string('choosedots')];
        foreach (notification_type::get_type_names() as $key => $name) {
            $typeoptions[$key] = $name;
        }
        $mform->addElement('select', 'notification_type',
            get_string('notificationtype', 'tool_eledia_mailtemplates'), $typeoptions);
        $mform->addRule('notification_type', null, 'required');
        $mform->setType('notification_type', PARAM_ALPHANUMEXT);

        // Context scope.
        $contextoptions = [
            CONTEXT_SYSTEM => get_string('context_system', 'tool_eledia_mailtemplates'),
            CONTEXT_COURSECAT => get_string('category'),
            CONTEXT_COURSE => get_string('context_course', 'tool_eledia_mailtemplates'),
            CONTEXT_MODULE => get_string('context_module', 'tool_eledia_mailtemplates'),
        ];
        $mform->addElement('select', 'contextlevel',
            get_string('template_context', 'tool_eledia_mailtemplates'), $contextoptions);
        $mform->setType('contextlevel', PARAM_INT);

        // Context ID (system = 0 by default, can be set for course/module scope).
        $mform->addElement('hidden', 'contextid', 0);
        $mform->setType('contextid', PARAM_INT);

        // Subject.
        $mform->addElement('text', 'subject',
            get_string('template_subject', 'tool_eledia_mailtemplates'), ['size' => 80]);
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', get_string('error_missing_subject', 'tool_eledia_mailtemplates'), 'required');

        // Body HTML (editor).
        $mform->addElement('editor', 'body_html',
            get_string('template_body', 'tool_eledia_mailtemplates'), ['rows' => 15]);
        $mform->setType('body_html', PARAM_RAW);
        $mform->addRule('body_html', get_string('error_missing_body', 'tool_eledia_mailtemplates'), 'required');

        // Active toggle.
        $mform->addElement('advcheckbox', 'active',
            get_string('template_active', 'tool_eledia_mailtemplates'));
        $mform->setDefault('active', 1);

        // Available variables info (rendered via JS based on selected type).
        $mform->addElement('static', 'variables_info',
            get_string('available_variables', 'tool_eledia_mailtemplates'),
            '<div id="tool-mt-variables-list"></div>');

        // Preview container.
        $mform->addElement('static', 'preview_container',
            get_string('preview', 'tool_eledia_mailtemplates'),
            '<div id="tool-mt-preview" style="border: 1px solid #ccc; padding: 16px; ' .
            'min-height: 200px; background: #fafafa;">' .
            get_string('preview_loading', 'tool_eledia_mailtemplates') . '</div>');

        $this->add_action_buttons(true);
    }

    /**
     * Validate form data.
     *
     * @param array $data The submitted form data.
     * @param array $files The submitted files.
     * @return array Validation errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);

        if (!notification_type::is_valid($data['notification_type'] ?? '')) {
            $errors['notification_type'] = get_string('error_invalid_type', 'tool_eledia_mailtemplates');
        }

        return $errors;
    }
}
