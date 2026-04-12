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
 * Tests for the variable_resolver class.
 *
 * @package    tool_eledia_mailtemplates
 * @category   test
 * @covers     \tool_eledia_mailtemplates\variable_resolver
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_eledia_mailtemplates\tests;

use tool_eledia_mailtemplates\variable_resolver;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for variable_resolver.
 */
class variable_resolver_test extends \advanced_testcase {

    // -------------------------------------------------------------------------
    // resolve()
    // -------------------------------------------------------------------------

    /**
     * Test that a single variable is resolved correctly.
     */
    public function test_resolve_single_variable(): void {
        $result = variable_resolver::resolve('Hello {{firstname}}!', ['firstname' => 'Max']);
        $this->assertEquals('Hello Max!', $result);
    }

    /**
     * Test that multiple variables are all resolved.
     */
    public function test_resolve_multiple_variables(): void {
        $result = variable_resolver::resolve(
            'Hello {{firstname}} {{lastname}}, welcome to {{site_name}}.',
            ['firstname' => 'Max', 'lastname' => 'Mustermann', 'site_name' => 'My Moodle']
        );
        $this->assertEquals('Hello Max Mustermann, welcome to My Moodle.', $result);
    }

    /**
     * Test that an unknown variable is replaced with an empty string.
     */
    public function test_resolve_unknown_variable_becomes_empty(): void {
        $result = variable_resolver::resolve('Hello {{unknown_var}}!', []);
        $this->assertEquals('Hello !', $result);
    }

    /**
     * Test that a template with no variables is returned unchanged.
     */
    public function test_resolve_no_variables_unchanged(): void {
        $template = 'Hello, this is a plain message.';
        $result = variable_resolver::resolve($template, ['unused' => 'data']);
        $this->assertEquals($template, $result);
    }

    /**
     * Test that an empty template returns an empty string.
     */
    public function test_resolve_empty_template(): void {
        $result = variable_resolver::resolve('', ['foo' => 'bar']);
        $this->assertEquals('', $result);
    }

    /**
     * Test that the same variable used multiple times is resolved each time.
     */
    public function test_resolve_repeated_variable(): void {
        $result = variable_resolver::resolve(
            '{{name}} is {{name}}.',
            ['name' => 'Alice']
        );
        $this->assertEquals('Alice is Alice.', $result);
    }

    /**
     * Test that variables with special HTML characters in values are kept as-is.
     * (Escaping is handled by Moodle's output layer, not the resolver.)
     */
    public function test_resolve_preserves_html_in_values(): void {
        $result = variable_resolver::resolve(
            '<p>{{content}}</p>',
            ['content' => '<strong>Bold</strong>']
        );
        $this->assertEquals('<p><strong>Bold</strong></p>', $result);
    }

    // -------------------------------------------------------------------------
    // html_to_plaintext()
    // -------------------------------------------------------------------------

    /**
     * Test basic HTML tag stripping.
     */
    public function test_html_to_plaintext_strips_tags(): void {
        $html = '<p>Hello <strong>World</strong></p>';
        $result = variable_resolver::html_to_plaintext($html);
        $this->assertStringNotContainsString('<', $result);
        $this->assertStringContainsString('Hello', $result);
        $this->assertStringContainsString('World', $result);
    }

    /**
     * Test that <br> tags are converted to newlines.
     */
    public function test_html_to_plaintext_br_becomes_newline(): void {
        $html = 'Line one<br>Line two';
        $result = variable_resolver::html_to_plaintext($html);
        $this->assertStringContainsString("\n", $result);
        $this->assertStringContainsString('Line one', $result);
        $this->assertStringContainsString('Line two', $result);
    }

    /**
     * Test that HTML entities are decoded.
     */
    public function test_html_to_plaintext_decodes_entities(): void {
        $html = '<p>Hello &amp; welcome &mdash; it&apos;s great</p>';
        $result = variable_resolver::html_to_plaintext($html);
        $this->assertStringContainsString('&', $result);
        $this->assertStringNotContainsString('&amp;', $result);
    }

    /**
     * Test that links are rendered as "text (url)" format.
     */
    public function test_html_to_plaintext_formats_links(): void {
        $html = '<a href="https://example.com">Click here</a>';
        $result = variable_resolver::html_to_plaintext($html);
        $this->assertStringContainsString('Click here', $result);
        $this->assertStringContainsString('https://example.com', $result);
    }

    /**
     * Test that an empty HTML input returns an empty string.
     */
    public function test_html_to_plaintext_empty_input(): void {
        $result = variable_resolver::html_to_plaintext('');
        $this->assertEquals('', $result);
    }

    /**
     * Data provider for resolve edge cases.
     *
     * @return array
     */
    public static function resolve_edge_cases_provider(): array {
        return [
            'empty data array'      => ['{{foo}}', [], ''],
            'whitespace in value'   => ['{{val}}', ['val' => '  trimmed  '], '  trimmed  '],
            'numeric value'         => ['Grade: {{grade}}', ['grade' => '95'], 'Grade: 95'],
            'url as value'          => ['{{url}}', ['url' => 'https://example.com'], 'https://example.com'],
        ];
    }

    /**
     * Test resolve with various edge cases.
     *
     * @dataProvider resolve_edge_cases_provider
     * @param string $template The template string.
     * @param array $data The data array.
     * @param string $expected The expected result.
     */
    public function test_resolve_edge_cases(string $template, array $data, string $expected): void {
        $this->assertEquals($expected, variable_resolver::resolve($template, $data));
    }
}
