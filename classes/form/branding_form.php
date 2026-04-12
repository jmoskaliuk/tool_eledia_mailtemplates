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
 * Branding configuration form.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for configuring global email branding.
 */
class branding_form extends \moodleform {

    /**
     * Define the form elements.
     *
     * @return void
     */
    protected function definition(): void {
        $mform = $this->_form;

        // Logo — file upload like course image.
        $mform->addElement('filemanager', 'logo',
            get_string('branding_logo', 'tool_eledia_mailtemplates'),
            null,
            [
                'maxfiles'       => 1,
                'accepted_types' => ['image'],
                'subdirs'        => 0,
            ]
        );
        $mform->addHelpButton('logo', 'branding_logo', 'tool_eledia_mailtemplates');

        // Primary colour.
        $mform->addElement('text', 'primarycolor',
            get_string('branding_primarycolor', 'tool_eledia_mailtemplates'), ['size' => 10]);
        $mform->setType('primarycolor', PARAM_TEXT);
        $mform->setDefault('primarycolor', '#000000');
        $mform->addHelpButton('primarycolor', 'branding_primarycolor', 'tool_eledia_mailtemplates');

        // Footer content (editor).
        $mform->addElement('editor', 'footercontent',
            get_string('branding_footer', 'tool_eledia_mailtemplates'), ['rows' => 5]);
        $mform->setType('footercontent', PARAM_RAW);

        // Legal disclaimer (editor).
        $mform->addElement('editor', 'disclaimer',
            get_string('branding_disclaimer', 'tool_eledia_mailtemplates'), ['rows' => 3]);
        $mform->setType('disclaimer', PARAM_RAW);

        $this->add_action_buttons(true);
    }
}
