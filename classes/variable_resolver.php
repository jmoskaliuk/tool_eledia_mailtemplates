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
 * Variable resolver for email templates.
 *
 * @package    tool_eledia_mailtemplates
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates;

/**
 * Resolves {{variable}} placeholders in template strings.
 */
class variable_resolver {

    /**
     * Resolve all variables in a template string.
     *
     * Variables use the syntax {{variable_name}}.
     * Unknown or unresolvable variables are replaced with an empty string.
     *
     * @param string $template The template string containing {{variable}} placeholders.
     * @param array $data Associative array of variable key => value.
     * @return string The resolved string with all variables replaced.
     */
    public static function resolve(string $template, array $data): string {
        return preg_replace_callback(
            '/\{\{(\w+)\}\}/',
            function ($matches) use ($data) {
                $key = $matches[1];
                return $data[$key] ?? '';
            },
            $template
        );
    }

    /**
     * Generate a plain text version from an HTML string.
     *
     * Strips HTML tags, decodes entities, and normalises whitespace.
     *
     * @param string $html The HTML content.
     * @return string A readable plain text version.
     */
    public static function html_to_plaintext(string $html): string {
        // Convert <br> and block elements to newlines.
        $text = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $text = preg_replace('/<\/(p|div|h[1-6]|li|tr)>/i', "\n", $text);
        $text = preg_replace('/<(p|div|h[1-6]|ul|ol|table)[^>]*>/i', "\n", $text);

        // Convert links to "text (url)" format.
        $text = preg_replace('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', '$2 ($1)', $text);

        // Strip remaining tags.
        $text = strip_tags($text);

        // Decode HTML entities.
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalise whitespace: collapse multiple blank lines, trim lines.
        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $text = implode("\n", $lines);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
