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
    private $datagenerator;

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

        $slot = new slot($event->id);

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

    public function test_prepare_display_id_mismatch() {
        // There could be a mismatch between eventid and slotid
        // Test that prepare display links to the slotid
        // Old style calendar event like in mod_room v1.1.1
        calendar_event::create((object)[
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
        $newslot = new slot();
        $newslot->set_slot_properties($slotsettings, $this->roomplan);
        $newslot->save();

        $loadedslot = new slot(['slotid' => $newslot->slotid]);
        $this->assertNotEquals($loadedslot->slotid, $loadedslot->id);

        $loadedslot->prepare_display(context_module::instance($this->roomplan->cmid));
        $this->assertEquals($loadedslot->slotid, $loadedslot->spotbooking->action->get_param('slotid'));
    }

    public function test_prepare_display() {
        $slotsettings = (object)[
            'courseid' => $this->course->id,
            'instance' => $this->roomplan->id,
            'starttime' => time(),
            'slottitle' => 'Slot with spot',
            'room' => $this->roomspace->id
        ];
        $slot = new slot();
        $slot->set_slot_properties($slotsettings, $this->roomplan);

        // test no spotbooking without spots
        $slot->prepare_display(context_module::instance($this->roomplan->cmid));

        $this->assertNull($slot->spotbooking);

        // test that spotbooking when there is a free spot
        $slot->spots = 1;
        $slot->save();
        $slot = new slot($slot->id);
        $slot->prepare_display(context_module::instance($this->roomplan->cmid));

        $this->assertNotNull($slot->spotbooking);

        $this->assertEquals('Book spot', $slot->spotbooking->message);
        $this->assertEquals('/moodle/mod/room/spotbook.php', $slot->spotbooking->action->get_path());

        // test that spotbooking when there are free spots
        $slot->spots = 2;
        $slot->save();
        $slot = new slot($slot->id);
        $slot->prepare_display(context_module::instance($this->roomplan->cmid));

        $this->assertNotNull($slot->spotbooking);

        $this->assertEquals('Book spot', $slot->spotbooking->message);
        $this->assertEquals('/moodle/mod/room/spotbook.php', $slot->spotbooking->action->get_path());

        // test that no spotbooking when user has already booked
        $slot->save();

        $user = $this->datagenerator->create_user();
        $this->setUser($user);

        $slot->prepare_display(context_module::instance($this->roomplan->cmid));
        $this->assertEquals(2, $slot->bookingsfree);
        $this->assertNotNull($slot->spotbooking);

        $slot->new_booking($user->id);

        $slot->prepare_display(context_module::instance($this->roomplan->cmid));
        $this->assertEquals(1, $slot->bookingsfree);
        $this->assertNull($slot->spotbooking);

        // test that no spotbooking when user does not have capability
        // TODO: test that spotbooking when some spots are booked
        // TODO: test that no spotbooking when all spots are booked
    }

    public function test_constructor() {
        // check that constructing from slotid works and returns saved values
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

        $loadedslot = new slot(['slotid' => $slot->slotid]);

        $this->assertEquals($slot->timestart, $loadedslot->timestart, 'timestart not loaded.');
        $this->assertEquals(60 * 60, $loadedslot->timeduration);
        $this->assertEquals(2, $loadedslot->spots);
        $this->assertEquals('wonderful event', $loadedslot->name);
    }

    public function test_new_booking() {
        $slotsettings = (object)[
            'courseid' => $this->course->id,
            'instance' => $this->roomplan->id,
            'starttime' => time(),
            'duration' => [
                'hours' => 1,
                'minutes' => 0
            ],
            'spots' => 1,
            'slottitle' => 'wonderful event',
            'room' => $this->roomspace->id
        ];
        $slot = new slot();
        $slot->set_slot_properties($slotsettings, $this->roomplan);
        $slot->save();

        $slot = new slot($slot->id);
        $slot->prepare_display(context_module::instance($this->roomplan->cmid));
        $this->assertNotNull($slot->spotbooking);
        
        $user = $this->datagenerator->create_user();
        $slot->new_booking($user->id);

        $slot->prepare_display(context_module::instance($this->roomplan->cmid));
        $this->assertNull($slot->spotbooking);

        // Check that booking is loaded correctly
        $loadedslot = new slot(['slotid' => $slot->slotid]);
        $loadedslot->prepare_display(context_module::instance($this->roomplan->cmid));
        $this->assertNull($loadedslot->spotbooking);
    }
}
