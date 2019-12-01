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

use mod_room\entity\slot;

defined('MOODLE_INTERNAL') || die();

/**
 * The slot test class.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_room_slot_testcase extends advanced_testcase {
    private $course;
    private $roomplan;
    private $roomspace;

    protected function setup() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $datagenerator = $this->getDataGenerator();
        $this->course = $datagenerator->create_course();
        $this->roomplan = $datagenerator->create_module('room', ['course' => $this->course->id]);

        // Set up space
        $this->roomspace = new stdClass();
        $this->roomspace->name = 'Test room';

        global $DB;
        $this->roomspace->id = $DB->insert_record('room_space', $this->roomspace);
    }

    /**
     * Old records have an event but no room_slot record. We create a new room_slot record 
     * when saving the slot
     */
    public function test_save() {

        // Create just calendar event like in mod_room v1.1.1
        $event = calendar_event::create((object)[
            'courseid' => $this->course->id,
            'instance' => $this->roomplan->id,
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

        $slot->set_slot_properties($formproperties, $this->roomplan);
        $slot->save();

        $slot = new slot($event->id);

        $this->assertEquals($slot->spots, 2);
    }

    protected function assertSlotsPropertiesEqual($expectedslot, $actualslot) {
        foreach (['spots', 'timestart', 'timeduration', 'name', 'location'] as $property) {
            $this->assertEquals(
                $expectedslot->$property, 
                $actualslot->$property, 
                "Property $property was not equal."
            );
        }
    }

    public function test_save_load_clone() {
        $slotsettings = (object)[
            'courseid' => $this->course->id,
            'instance' => $this->roomplan->id,
            'starttime' => time(),
            'duration' => [
                'hours' => 2,
                'minutes' => 40
            ],
            'spots' => 3,
            'slottitle' => 'Best ever event',
            'room' => $this->roomspace->id
        ];
        $originalslot = new slot();
        $originalslot->set_slot_properties($slotsettings, $this->roomplan);

        $originalslot->save();

        $loadedslot = new slot($originalslot->id);

        $this->assertSlotsPropertiesEqual($originalslot, $loadedslot);

        $clonedslot = new slot();
        $clonedslot->clone_slot($originalslot);
        $this->assertSlotsPropertiesEqual($loadedslot, $clonedslot);
    }
}
