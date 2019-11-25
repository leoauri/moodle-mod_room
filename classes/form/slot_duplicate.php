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

require_once($CFG->libdir . '/formslib.php');

/**
 * The slot_duplicate form class.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class slot_duplicate extends \moodleform {
    function definition() {
        $mform = $this->_form;

        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'mod_room'));
        if ($this->_customdata['startdate']) {
            $mform->setDefault('startdate', $this->_customdata['startdate']);
        }

        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'mod_room'));
        if ($this->_customdata['enddate']) {
            $mform->setDefault('enddate', $this->_customdata['enddate']);
        }

        $mform->addElement(
            'select', 
            'duplicationmode', 
            get_string('duplicationmode', 'mod_room'), 
            [get_string('oneweeklater', 'mod_room')]
        );

        $this->add_action_buttons(true, get_string('duplicateslots', 'mod_room'));
    }
}
