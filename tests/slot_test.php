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

defined('MOODLE_INTERNAL') || die();

/**
 * The slot test class.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_room_slot_testcase extends advanced_testcase {
    protected function setup() {
        $this->resetAfterTest();
    }

    /**
     * Old records have an event but no room_slot record. We create a new room_slot record 
     * when saving the slot
     */
    public function test_save() {
        $this->setAdminUser();
        $datagenerator = $this->getDataGenerator();
        $course = $datagenerator->create_course();
        $roomplan = $datagenerator->create_module('room', ['course' => $course->id]);

        // Create just calendar event like in mod_room v1.1.1
        $event = calendar_event::create((object)[
            'courseid' => $course->id,
            'instance' => $roomplan->id,
            'timestart' => time(),
            'timeduration' => 60 * 60,
            'name' => 'Old slot',
            'location' => 'Invalid room',
            'modulename' => 'room',
            'groupid' => 0,
            'userid' => 0,
            'type' => CALENDAR_EVENT_TYPE_STANDARD,
            'eventtype' => ROOM_EVENT_TYPE_SLOT
        ], false);

        $slot = new \mod_room\entity\slot($event->id);

        $this->assertEquals($slot->name, 'Old slot');

        $formproperties = $slot->form_properties();

        $formproperties->spots = 2;

        $slot->set_slot_properties($formproperties, $roomplan);
        $slot->save();

        $slot = new \mod_room\entity\slot($event->id);

        $this->assertEquals($slot->spots, 2);
    }
}
