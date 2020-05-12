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
 * Plugin form classes are defined here.
 *
 * @package     mod_room
 * @copyright   2020 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_room\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The slot_duplication form class.
 *
 * @package    mod_room
 * @copyright  2020 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class slot_duplication extends \moodleform {
    function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'select', 
            'duplicationmode', 
            get_string('duplicationmode', 'mod_room'), 
            [get_string('consecutive', 'mod_room')]
        );

        $mform->addElement(
            'text', 
            'numberofduplicates', 
            get_string('numberofduplicates', 'mod_room'), 
            'size="3"'
        );
        $mform->setType('numberofduplicates', PARAM_INT);
        $mform->addRule('numberofduplicates', null, 'numeric', null, 'client');

        $mform->addElement(
            'html', 
            '<div id="mod-room-duplication-preview" class="form-group row fitem"></div>'
        );

        // Submit buttons
        $buttons = [];

        $submitbutton = &$mform->createElement(
            'submit', 
            'submitbutton', 
            get_string('duplicateslots', 'mod_room')
        );

        // Pre-disable submit button so form cannot be submitted until javascript loaded
        $submitbutton->updateAttributes(['disabled' => 'disabled']);

        $buttons[] = $submitbutton;

        $buttons[] = &$mform->createElement('cancel');

        $mform->addGroup($buttons, 'buttons', '', array(' '), false);
        $mform->closeHeaderBefore('buttons');
    }
}
