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
 * AJAX reload of room plan on date change.
 *
 * @package    mod_room
 * @copyright  2020 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*eslint no-trailing-spaces: "warn"*/
/*eslint no-unused-vars: "warn"*/
/*eslint no-console: "warn"*/

define([
    'core/loadingicon',
    'core/ajax',
    'core/notification',
], function(
    LoadingIcon,
    Ajax,
    Notification,
) {
    const selectorDay = document.getElementById('id_displaydate_day');
    const selectorMonth = document.getElementById('id_displaydate_month');
    const selectorYear = document.getElementById('id_displaydate_year');

    const roomPlanContainer = document.getElementById('mod-room-room-plan');

    let displayingDate;
    let cmId;

    const datesEqual = function(date1, date2) {
        return date1.day === date2.day && date1.month === date2.month && date1.year === date2.year;
    };

    const selectedDate = function() {
        return {
            day: Math.floor(selectorDay.value),
            month: Math.floor(selectorMonth.value),
            year: Math.floor(selectorYear.value)
        };
    };

    const reloadRoomPlanIfDateChanged = function() {
        // remove the dirty form checker, there is no submit of the form to make it clean
        window.onbeforeunload = null;
        
        if (datesEqual(displayingDate, selectedDate())) {
            return;
        }

        displayingDate = selectedDate();
        let iWillLoad = displayingDate;

        roomPlanContainer.innerHTML = '';
        const loadingicon = LoadingIcon.addIconToContainerWithPromise(roomPlanContainer);

        const request = {
            methodname: 'mod_room_room_plan_rendered',
            args: {
                date: displayingDate,
                cmid: cmId
            }
        };

        Ajax.call([request])[0]
            .then((response) => {
                if (datesEqual(selectedDate(), iWillLoad)) {
                    loadingicon.resolve();
                    
                    roomPlanContainer.innerHTML = response.html;
                    
                    // Push updated URL
                    history.pushState(null, null, '?' + response.url);
                }
            })
            .fail(Notification.exception);
    };

    return {
        init: function(initialDate, cmid) {
            document.getElementById('id_submitbutton').style.display = 'none';

            // store initial date value
            displayingDate = initialDate;
            cmId = cmid;

            // poll for changed date fields
            setInterval(reloadRoomPlanIfDateChanged, 250);
        }
    };
});