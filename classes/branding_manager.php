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
 * Branding manager for tool_eledia_mailtemplates.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates;

use stdClass;

/**
 * Manages the global branding configuration for email templates.
 */
class branding_manager {

    /** @var string DB table name for branding. */
    private const TABLE = 'tool_eledia_mt_branding';

    /** @var string File area name for the logo. */
    public const FILEAREA_LOGO = 'logo';

    /** @var int Item ID used for the logo file area (singleton). */
    public const LOGO_ITEMID = 0;

    /**
     * Get the current branding configuration.
     *
     * Returns the single branding record, or a default object.
     *
     * @return stdClass The branding configuration.
     */
    public static function get_branding(): stdClass {
        global $DB;

        $record = $DB->get_record(self::TABLE, [], '*', IGNORE_MULTIPLE);
        if ($record) {
            return $record;
        }

        return (object) [
            'id'            => 0,
            'primarycolor'  => '#000000',
            'footercontent' => '',
            'disclaimer'    => '',
        ];
    }

    /**
     * Save branding configuration and persist the logo file from draft area.
     *
     * @param stdClass $data Form data including 'logo' draftitemid.
     * @return int The branding record ID.
     */
    public static function save_branding(stdClass $data): int {
        global $DB, $USER;

        $context = \core\context\system::instance();
        $now     = time();

        // Save logo from draft area to permanent file area.
        $fileoptions = ['maxfiles' => 1, 'accepted_types' => ['image'], 'subdirs' => 0];
        file_save_draft_area_files(
            $data->logo,
            $context->id,
            'tool_eledia_mailtemplates',
            self::FILEAREA_LOGO,
            self::LOGO_ITEMID,
            $fileoptions
        );

        $record                = new stdClass();
        $record->primarycolor  = $data->primarycolor;
        $record->footercontent = $data->footercontent['text'] ?? '';
        $record->disclaimer    = $data->disclaimer['text'] ?? '';
        $record->timemodified  = $now;
        $record->usermodified  = $USER->id;

        $existing = $DB->get_record(self::TABLE, [], '*', IGNORE_MULTIPLE);
        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record(self::TABLE, $record);
            return $record->id;
        }

        $record->timecreated = $now;
        return $DB->insert_record(self::TABLE, $record);
    }

    /**
     * Get the served URL for the branding logo, or empty string if none uploaded.
     *
     * @return string Full pluginfile URL, or empty string.
     */
    public static function get_logo_url(): string {
        $context = \core\context\system::instance();
        $fs      = get_file_storage();
        $files   = $fs->get_area_files(
            $context->id,
            'tool_eledia_mailtemplates',
            self::FILEAREA_LOGO,
            self::LOGO_ITEMID,
            'filename',
            false   // Exclude directories.
        );

        if (empty($files)) {
            return '';
        }

        $file = reset($files);
        return \moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        )->out();
    }

    /**
     * Prepare the logo draft item ID for the filemanager form element.
     *
     * @return int The draft item ID.
     */
    public static function get_logo_draftitemid(): int {
        $context = \core\context\system::instance();
        $draftitemid = 0;
        file_prepare_draft_area(
            $draftitemid,
            $context->id,
            'tool_eledia_mailtemplates',
            self::FILEAREA_LOGO,
            self::LOGO_ITEMID,
            ['maxfiles' => 1, 'accepted_types' => ['image'], 'subdirs' => 0]
        );
        return $draftitemid;
    }

    /**
     * Wrap a template body with the branding layout.
     *
     * @param string $bodyhtml The resolved template body HTML.
     * @param stdClass|null $branding Optional branding config (fetched if null).
     * @return string The full HTML email with branding applied.
     */
    public static function apply_branding(string $bodyhtml, ?stdClass $branding = null): string {
        global $OUTPUT;

        if ($branding === null) {
            $branding = self::get_branding();
        }

        $logourl = self::get_logo_url();

        $context = [
            'body_html'       => $bodyhtml,
            'has_logo'        => $logourl !== '',
            'logo_url'        => $logourl,
            'primary_color'   => $branding->primarycolor ?? '#000000',
            'has_footer'      => !empty($branding->footercontent),
            'footer_content'  => $branding->footercontent ?? '',
            'has_disclaimer'  => !empty($branding->disclaimer),
            'disclaimer'      => $branding->disclaimer ?? '',
        ];

        return $OUTPUT->render_from_template('tool_eledia_mailtemplates/email_wrapper', $context);
    }
}
