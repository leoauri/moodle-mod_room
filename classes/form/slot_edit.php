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
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_room\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * The slot_edit form class.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class slot_edit extends \moodleform {
    function definition() {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('text', 'slottitle', get_string('slottitle', 'mod_room'));
        $mform->setType('slottitle', PARAM_TEXT);
        $mform->addRule('slottitle', null, 'required', null, 'client');

        $roomchoices = array('noselection' => '');
        $rooms = $DB->get_records('room_space');
        foreach ($rooms as $room) {
            $roomchoices[$room->id] = $room->name; 
        }
        $mform->addElement('select', 'room', get_string('room', 'mod_room'), $roomchoices);
        $mform->addRule('room', get_string('mustselectaroom', 'mod_room'), 'numeric', null, 'client');

        $mform->addElement('date_time_selector', 'starttime', get_string('starttime', 'mod_room'));

        $mform->addElement('text', 'duration', get_string('durationminutes', 'calendar'));
        $mform->setType('duration', PARAM_INT);
        $mform->addRule('duration', null, 'numeric', null, 'client');

        $confirmmessage = $this->_customdata['slotid'] ? 'updateslot' : 'addslot';

        $this->add_action_buttons(true, get_string($confirmmessage, 'mod_room'));
    }
}
