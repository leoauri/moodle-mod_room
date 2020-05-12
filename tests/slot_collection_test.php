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
 * File containing tests for slot collection.
 *
 * @package     mod_room
 * @category    test
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\plugininfo\mod;

defined('MOODLE_INTERNAL') || die();
require_once('mod_room_test_base.php');

/**
 * The slot_collection test class.
 *
 * @package    mod_room
 * @copyright  2020 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_room_slot_collection_testcase extends mod_room_test_base {
    public function test_duplicates_consecutive() {
        $slotsettings = (object)[
            'courseid' => $this->course->id,
            'instance' => $this->roomplan->id,
            'starttime' => 1593640800,
            'duration' => [
                'hours' => 1,
                'minutes' => 0
            ],
            'spots' => 1,
            'slottitle' => 'wonderful event',
            'room' => $this->roomspace->id
        ];
        $slot = new mod_room\entity\slot();
        $slot->set_slot_properties($slotsettings, $this->roomplan);

        
        $test_dup_values = function() use ($slot) {
            $duplicates = \mod_room\entity\slot_collection::duplicates_consecutive($slot, 4);
            $duplicates->prepare_display(context_module::instance($this->roomplan->cmid));

            $times = [];
            $humantimes = [];
            foreach ($duplicates as $duplicate) {
                $times[] = $duplicate->timestart;
                $humantimes[] = $duplicate->userdatestart;
            }
    
            $this->assertEquals(1593644400, $times[0]);
            $this->assertEquals(1593648000, $times[1]);
            $this->assertEquals(1593651600, $times[2]);
            $this->assertEquals(1593655200, $times[3]);
    
            // test server uses local Australian time, of course 😅
            $this->assertEquals(
                [
                    'Thursday, 2 July 2020, 7:00 AM', 
                    'Thursday, 2 July 2020, 8:00 AM', 
                    'Thursday, 2 July 2020, 9:00 AM', 
                    'Thursday, 2 July 2020, 10:00 AM', 
                ],
                $humantimes
            );
        };
        $test_dup_values();

        // test duplication of loaded slot
        $slot->save();
        $slot = new \mod_room\entity\slot($slot->id);

        $test_dup_values();
    }
}
