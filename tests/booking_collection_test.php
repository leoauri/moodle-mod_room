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
 * File containing tests for booking collection.
 *
 * @package     mod_room
 * @category    test
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_room\entity\slot;

/**
 * The booking collection test class.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_room_booking_collection_testcase extends advanced_testcase {
    protected function setup() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->datagenerator = $this->getDataGenerator();
        $this->course = $this->datagenerator->create_course();
        $this->roomplan = $this->datagenerator->create_module('room', ['course' => $this->course->id]);

        // Set up space
        $this->roomspace = new stdClass();
        $this->roomspace->name = 'Test room';

        global $DB;
        $this->roomspace->id = $DB->insert_record('room_space', $this->roomspace);
    }

    public function test_new_booking() {
        $user = $this->datagenerator->create_user();

        // second booking by same user should throw exception
        $slotsettings = (object)[
            'courseid' => $this->course->id,
            'instance' => $this->roomplan->id,
            'starttime' => time(),
            'duration' => [
                'hours' => 1,
                'minutes' => 0
            ],
            'spots' => 2,
            'slottitle' => 'wonderful event',
            'room' => $this->roomspace->id
        ];
        $slot = new slot();
        $slot->set_slot_properties($slotsettings, $this->roomplan);
        $slot->save();

        $slot = new slot($slot->id);

        $bookings = new \mod_room\entity\booking_collection($slot->id);

        $bookings->new_booking($user->id);

        $this->expectException('moodle_exception');
        $bookings->new_booking($user->id);
    }

    // public function test_contructor() {
    //     $user = $this->datagenerator->create_user();
        
    //     $slotsettings = (object)[
    //         'courseid' => $this->course->id,
    //         'instance' => $this->roomplan->id,
    //         'starttime' => time(),
    //         'duration' => [
    //             'hours' => 1,
    //             'minutes' => 0
    //         ],
    //         'spots' => 2,
    //         'slottitle' => 'wonderful event',
    //         'room' => $this->roomspace->id
    //     ];
    //     $slot = new slot();
    //     $slot->set_slot_properties($slotsettings, $this->roomplan);
    //     $slot->save();

    //     $slot = new slot($slot->id);

    //     $slot->new_booking($user->id);

    //     $loadedslot = new slot($slot->id);
    //     $this->assertEquals($user->firstname, )
    // }
}
