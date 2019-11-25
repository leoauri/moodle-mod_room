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
 * Room module slot class.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_room\entity;

defined('MOODLE_INTERNAL') || die();

/**
 * Room module slot class.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class slot {
    /**
     * @var \calendar_event associated calendar event of the slot
     * Unset until event saved
     */
    private $event;

    /**
     * @var \stdClass associated calendar event properties
     */
    private $eventproperties;

    /**
     * Slot constructor. Passing an event id will load it, otherwise construct a fresh slot.
     */
    public function __construct(int $eventid = null) {
        if ($eventid) {
            $this->eventid = $eventid;
            $this->event = \calendar_event::load($eventid);
            $this->eventproperties = $this->event->properties();
        } else {
            $this->eventproperties = new \stdClass();
        }
    }

    public function set_slot_properties(\stdClass $data, $moduleinstance) {
        // TODO: validate data
        if (!isset($data->starttime)) {
            throw new \coding_exception('Slot must be given a starttime property.');
        }
        
        $this->eventproperties->courseid = $moduleinstance->course;
        $this->eventproperties->instance = $moduleinstance->id;
        
        $this->eventproperties->timestart = $data->starttime;
        $this->eventproperties->timeduration = 
            $data->duration['hours'] * 60 * 60 + $data->duration['minutes'] * 60;
        $this->eventproperties->name = $data->slottitle;
        
        // This saves the string room name to the calendar event, because it's the only way to display 
        // it in moodle calendar.  Kind of silly because we just did the db lookup to build the form...
        global $DB;
        $this->eventproperties->location = $DB->get_field('room_space', 'name', ['id' => $data->room]);
        
        $this->eventproperties->modulename = 'room';
        $this->eventproperties->groupid = 0;
        $this->eventproperties->userid = 0;
        $this->eventproperties->type = CALENDAR_EVENT_TYPE_STANDARD;
        $this->eventproperties->eventtype = ROOM_EVENT_TYPE_SLOT;
    }

    public function save() {
        if ($this->event) {
            $this->event->update($this->eventproperties);
            // TODO: event logging

        } else {
            $this->event = \calendar_event::create($this->eventproperties, false);
            // debugging('saving new event');
            // TODO: event logging
        }
    }

    public function midnight() {
        return usergetmidnight($this->eventproperties->timestart);
    }

    public function form_properties() {
        $formproperties = new \stdClass();
        if ($this->event) {
            $formproperties->slottitle = $this->event->name;
            // Another silly lookup of the roomid this time
            global $DB;
            $formproperties->room = $DB->get_field_select(
                'room_space', 
                'id', 
                'name = :name', 
                ['name' => $this->event->location]
            );
            $formproperties->starttime = $this->event->timestart;
            if ($duration = (int)($this->event->timeduration / 60)) {
                $formproperties->duration = [];
        
                $formproperties->duration['hours'] = intdiv($duration, 60);
                $formproperties->duration['minutes'] = $duration % 60;
            }
        }
        return $formproperties;
    }

    public function clone_slot($slot) {
        $this->eventproperties->courseid = $slot->courseid;
        $this->eventproperties->instance = $slot->instance;
        
        $this->eventproperties->timestart = $slot->timestart;
        $this->eventproperties->timeduration = $slot->timeduration;
        $this->eventproperties->name = $slot->name;
        
        $this->eventproperties->location = $slot->location;
        
        $this->eventproperties->modulename = 'room';
        $this->eventproperties->groupid = 0;
        $this->eventproperties->userid = 0;
        $this->eventproperties->type = CALENDAR_EVENT_TYPE_STANDARD;
        $this->eventproperties->eventtype = ROOM_EVENT_TYPE_SLOT;

    }
}
