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
 * Setup slot display filters.
 *
 * @package    mod_room
 * @copyright  2020 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*eslint no-unused-vars: "warn"*/

define(['core/str'], function(str) {
    return {
        init: function(filterlist) {
            const filters = filterlist.split(",").filter(f => f);

            // Leave if no filters are specified
            if (filters.length < 1) {
                return;
            }

            // Create and insert container for filter controls
            const filterContainer = document.createElement("div");
            filterContainer.id = 'mod-room-slot-filters';

            const roomPlanContainer = document.getElementById('mod-room-room-plan');
            roomPlanContainer.insertAdjacentElement('beforebegin', filterContainer);

            const title = document.createElement('h4');
            filterContainer.appendChild(title);
            str.get_string('filterslots', 'mod_room')
                .then(titleString => {
                    title.appendChild(document.createTextNode(titleString));
                });

            let filterButtons = [];

            const filterSlots = function(filter) {
                let slots = [...document.getElementById('mod-room-roomplan').children];
                slots.forEach(slot => {
                    slot.classList.remove('hidden');
                    if (
                        filter &&
                        !slot.dataset.eventTitle.toLowerCase().includes(filter.toLowerCase())
                    ) {
                        slot.classList.add('hidden');
                    }
                });
            };

            const selectFilter = function(event) {
                // If filter was already selected, deselect
                if (event.target.classList.contains('selected')) {
                    event.target.classList.remove('selected');

                    filterSlots();
                } else {
                    // otherwise select it exclusively
                    filterButtons.forEach((filterButton) => {
                        filterButton.classList.remove('selected');
                    });
                    event.target.classList.add('selected');

                    filterSlots(event.target.textContent);
                }
            };

            // Add filter buttons
            filters.forEach(filterName => {
                let filterButton = document.createElement('span');
                filterButton.innerHTML = filterName;
                filterContainer.appendChild(filterButton);

                filterButtons.push(filterButton);

                filterButton.addEventListener('click', selectFilter);
            });
        }
    };
});