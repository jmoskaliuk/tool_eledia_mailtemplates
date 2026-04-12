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
 * Live preview module for template editor.
 *
 * @module     tool_eledia_mailtemplates/preview
 * @copyright  2026 eLeDia GmbH {@link https://eledia.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';

/** @var {number|null} Debounce timer for preview updates. */
let debounceTimer = null;

/**
 * Fetch and display available variables for the selected notification type.
 *
 * @param {string} type The notification type identifier.
 */
const updateVariablesList = async(type) => {
    const container = document.getElementById('tool-mt-variables-list');
    if (!container) {
        return;
    }

    if (!type) {
        container.innerHTML = '<em>Select a notification type to see available variables.</em>';
        return;
    }

    try {
        const result = await Ajax.call([{
            methodname: 'tool_eledia_mailtemplates_get_type_variables',
            args: {notification_type: type},
        }])[0];

        if (!result.variables || result.variables.length === 0) {
            container.innerHTML = '<em>No variables available for this type.</em>';
            return;
        }

        const chips = result.variables.map(v =>
            `<code title="${v.description}" style="cursor:pointer;margin:2px;display:inline-block;"` +
            ` onclick="navigator.clipboard.writeText('${v.placeholder}')">${v.placeholder}</code>`
        ).join(' ');

        container.innerHTML = chips +
            '<div style="font-size:0.85em;color:#666;margin-top:4px;">Click to copy</div>';
    } catch (e) {
        Notification.exception(e);
    }
};

/**
 * Get the current HTML content from the active editor (Atto or TinyMCE).
 *
 * @returns {string} The HTML content.
 */
const getEditorContent = () => {
    // Try Atto iframe.
    const attoFrame = document.querySelector('[id$="_body_html_editor"] iframe');
    if (attoFrame && attoFrame.contentDocument) {
        return attoFrame.contentDocument.body.innerHTML;
    }
    // Try TinyMCE.
    if (typeof window.tinymce !== 'undefined') {
        const editor = window.tinymce.get('id_body_html');
        if (editor) {
            return editor.getContent();
        }
    }
    // Fallback: plain textarea.
    const textarea = document.getElementById('id_body_html');
    return textarea ? textarea.value : '';
};

/**
 * Request a rendered preview from the server and display it.
 */
const renderPreview = async() => {
    const typeSelect = document.getElementById('id_notification_type');
    const subjectInput = document.getElementById('id_subject');
    const previewContainer = document.getElementById('tool-mt-preview');

    if (!typeSelect || !subjectInput || !previewContainer) {
        return;
    }

    const type = typeSelect.value;
    if (!type) {
        previewContainer.innerHTML = '<em>Select a notification type to see the preview.</em>';
        return;
    }

    const bodyHtml = getEditorContent();
    if (!bodyHtml) {
        previewContainer.innerHTML = '<em>Enter template content to see the preview.</em>';
        return;
    }

    try {
        const result = await Ajax.call([{
            methodname: 'tool_eledia_mailtemplates_render_preview',
            args: {
                notification_type: type,
                subject: subjectInput.value,
                body_html: bodyHtml,
            },
        }])[0];

        previewContainer.innerHTML =
            '<div style="margin-bottom:8px;font-weight:bold;color:#333;font-size:0.95em;">' +
            'Subject: ' + result.subject +
            '</div>' +
            '<div style="border:1px solid #ddd;padding:16px;background:#fff;">' +
            result.body_html +
            '</div>';
    } catch (e) {
        Notification.exception(e);
    }
};

/**
 * Debounced preview update — waits 800 ms after last change.
 */
const debouncedPreview = () => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    debounceTimer = setTimeout(renderPreview, 800);
};

/**
 * Initialise the preview module.
 * No arguments needed — data is loaded on demand via AJAX.
 */
export const init = () => {
    const typeSelect = document.getElementById('id_notification_type');
    if (typeSelect) {
        typeSelect.addEventListener('change', () => {
            updateVariablesList(typeSelect.value);
            debouncedPreview();
        });
        // Trigger immediately if a type is already selected (edit mode).
        if (typeSelect.value) {
            updateVariablesList(typeSelect.value);
            debouncedPreview();
        }
    }

    const subjectInput = document.getElementById('id_subject');
    if (subjectInput) {
        subjectInput.addEventListener('input', debouncedPreview);
    }

    // Poll editor content every 3 s to catch changes in Atto/TinyMCE.
    setInterval(debouncedPreview, 3000);
};
