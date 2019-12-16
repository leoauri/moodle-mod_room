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
 * Behat step definitions for room module.
 *
 * @package   mod_room
 * @category  test
 * @copyright 2019 Leo Auri <code@leoauri.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode;

class behat_mod_room extends behat_base {
    /**
     * @Given /^the following rooms are defined in the room module:$/
     */
    public function define_rooms(TableNode $table) {
        global $USER;
        global $DB;
        
        foreach ($table->getHash() as $roomdata) {
            if (empty($roomdata['roomname'])) {
                throw new Exception('Rooms require a roomname');
            }
            $newroom = new stdClass();
            $newroom->name = $roomdata['roomname'];
            $newroom->usermodified = $USER->id;
            $newroom->timecreated = time();
            $newroom->timemodified = time();
    
            $DB->insert_record('room_space', $newroom);    
        }
    }
}