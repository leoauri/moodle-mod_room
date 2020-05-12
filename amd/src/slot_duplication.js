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
 * Manager for slot duplication tool.
 *
 * @package    mod_room
 * @copyright  2020 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/loadingicon',
    'core/ajax',
    'core/notification',
], function(
    LoadingIcon,
    Ajax,
    Notification,
) {
    const numberOfDuplicatesField = document.querySelector('#id_numberofduplicates');
    const duplicationPreview = document.querySelector('#mod-room-duplication-preview');
    const submitButton = document.querySelector('#id_submitbutton');

    return {
        init: function(cmId, eventId) {
            const getSlotDuplicatesPreview = function() {
                duplicationPreview.innerHTML = '';
                submitButton.setAttribute('disabled', 'disabled');

                // trim numberOfDuplicatesField.value, make int, test has value
                let numberOfDuplicates = Math.floor(numberOfDuplicatesField.value);
                if (!numberOfDuplicates) { return; }

                // clamp to max 24 duplicates, min 1
                numberOfDuplicates = Math.max(Math.min(numberOfDuplicates, 24), 1);

                const loadingicon = LoadingIcon.addIconToContainerWithPromise(duplicationPreview);

                const request = {
                    methodname: 'mod_room_duplication_preview',
                    args: {
                        cmid: cmId,
                        eventid: eventId,
                        n_duplicates: numberOfDuplicates
                    }
                };

                Ajax.call([request])[0]
                    .then(response => {
                        loadingicon.resolve();
                        duplicationPreview.innerHTML = '';

                        // If n_duplicates field no longer holds equivalent value, abort here.
                        // Another callback must be handling it
                        if (
                            numberOfDuplicates !==
                            Math.max(Math.min(Math.floor(numberOfDuplicatesField.value), 24), 0)
                        ) {
                            return;
                        }

                        // Create element for response message
                        if (response.message) {
                            let message = document.createElement('div');
                            message.className = 'mod-room-duplication-preview-message';
                            message.classList.add('col-md-3');
                            message.textContent = response.message;
                            duplicationPreview.append(message);
                        }

                        // put each result in a list with time
                        if (response.slots) {
                            let duplicatePreviewTable = document.createElement('table');
                            duplicatePreviewTable.classList.add('col-md-9', 'form-inline', 'felement');
                            duplicationPreview.append(duplicatePreviewTable);

                            response.slots.forEach(slot => {
                                let row = duplicatePreviewTable.insertRow();
                                row.insertCell().textContent = slot.name;
                                row.insertCell().textContent = slot.time;
                            });
                        }

                        submitButton.removeAttribute('disabled');
                    })
                    .fail(Notification.exception);
            };

            getSlotDuplicatesPreview();
            numberOfDuplicatesField.addEventListener('input', getSlotDuplicatesPreview);
        }
    };
});
