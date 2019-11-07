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
     * @var int start timestamp
     */
    public $timestart;

    /**
     * @var int duration in seconds
     */
    public $timeduration;

    /**
     * @var int id of event
     */
    public $id;

    /**
     * @var int id of slot record
     */
    private $slotid;

    /**
     * @var int id of course event belongs to
     */
    public $courseid;

    /**
     * @var int id of module instance
     */
    public $instance;

    /**
     * @var string name of slot
     */
    public $name;

    /**
     * @var string location of event
     */
    public $location;

    // Default event properties
    public $modulename = 'room';
    public $groupid = 0;
    public $userid = 0;
    public $type = CALENDAR_EVENT_TYPE_STANDARD;
    public $eventtype = ROOM_EVENT_TYPE_SLOT;

    /**
     * @var int number of bookable spots
     */
    public $spots;

    /**
     * @var string human-readable start time
     */
    public $userdatestart;

    /**
     * @var string human-readable end time
     */
    public $userdateend;

    /**
     * @var bool whether the user can edit, template property
     */
    public $canedit;

    /**
     * @var string delete action url
     */
    public $deleteurl;

    /**
     * @var string edit action url
     */
    public $editurl;

    /**
     * @var int number of free spots to display
     */
    public $bookingsfree;

    /**
     * Prepare properties for display by a template
     */
    public function prepare_display(\context_module $modulecontext) {
        // Calculate human-readable date strings for start and end
        $this->userdatestart = userdate(
            $this->timestart, 
            get_string('strftimedaydatetime', 'langconfig')
        );

        // Pass human-readable end data if event has duration
        if ($this->timeduration) {
            // calculate end time, or, if other day, date
            $endtime = $this->timestart + $this->timeduration;
            $formatstring = (
                (usergetmidnight($this->timestart) == usergetmidnight($endtime)) ? 
                'strftimetime' : 
                'strftimedaydatetime'
            );
            $this->userdateend = userdate($endtime, get_string($formatstring, 'langconfig'));
        }

        // Pass edit and delete actions if user is capable
        $this->canedit = has_capability('mod/room:editslots', $modulecontext);
        if ($this->canedit) {
            $this->deleteurl = new \moodle_url(
                '/mod/room/slotdelete.php', 
                [
                    'slotid' => $this->id,
                    'id' => $modulecontext->instanceid
                ]
            );
            $this->editurl = new \moodle_url(
                '/mod/room/slotedit.php',
                [
                    'slotid' => $this->id,
                    'id' => $modulecontext->instanceid
                ]
            );
        }

        $this->bookingsfree = $this->spots;
    }


    /**
     * Slot constructor. Passing an event id will load it, otherwise construct a fresh slot.
     */
    public function __construct(int $eventid = null) {
        if ($eventid) {
            $this->event = \calendar_event::load($eventid);

            $eventproperties = $this->event->properties();
            $this->id = $eventproperties->id;
            $this->timestart = $eventproperties->timestart;
            $this->timeduration = $eventproperties->timeduration;
            $this->courseid = $eventproperties->courseid;
            $this->instance = $eventproperties->instance;
            $this->name = $eventproperties->name;
            $this->location = $eventproperties->location;

            // Load slot database record, if it exists
            global $DB;
            if ($slotproperties = $DB->get_record('room_slot', array('eventid' => $eventid), '*')) {
                $this->slotid = $slotproperties->id;
                $this->spots = $slotproperties->spots;
            }
        }
    }

    public function set_slot_properties(\stdClass $data, $moduleinstance) {
        // TODO: validate data
        if (!isset($data->starttime)) {
            throw new \coding_exception('Slot must be given a starttime property.');
        }
        
        $this->courseid = $moduleinstance->course;
        $this->instance = $moduleinstance->id;
        
        $this->timestart = $data->starttime;
        $this->timeduration = 
            $data->duration['hours'] * 60 * 60 + $data->duration['minutes'] * 60;
        $this->name = $data->slottitle;
        
        // This saves the string room name to the calendar event, because it's the only way to display 
        // it in moodle calendar.  Kind of silly because we just did the db lookup to build the form...
        global $DB;
        $this->location = $DB->get_field('room_space', 'name', ['id' => $data->room]);
        
        $this->spots = $data->spots;
    }

    private function event_properties() {
        return (object)[
            'id' => $this->id,
            'courseid' => $this->courseid,
            'instance' => $this->instance,
            'timestart' => $this->timestart,
            'timeduration' => $this->timeduration,
            'name' => $this->name,
            'location' => $this->location,
            'modulename' => $this->modulename,
            'groupid' => $this->groupid,
            'userid' => $this->userid,
            'type' => $this->type,
            'eventtype' => $this->eventtype
        ];
    }

    private function slot_properties() {
        global $USER;
        $slotproperties = [
            'eventid' => $this->id,
            'spots' => $this->spots,
            'usermodified' => $USER->id,
            'timemodified' => time(),
        ];

        if ($this->slotid) {
            // Updating a slot record, provide existing id
            $slotproperties['id'] = $this->slotid;
        } else {
            // We know it's a new record since id is not set, so set create time
            $slotproperties['timecreated'] = time();
        }

        return (object)$slotproperties;
    }

    public function save() {
        global $DB;
        if ($this->event) {
            // construct object from appropriate properties
            $this->event->update($this->event_properties());

            // TODO: event logging
        } else {
            $this->event = \calendar_event::create($this->event_properties(), false);
            $this->id = $this->event->id;
            
            // TODO: event logging
        }

        if ($this->slotid) {
            // update room_slot record
            $DB->update_record('room_slot', $this->slot_properties());
        } else {
            // create room_slot record
            $this->slotid = $DB->insert_record('room_slot', $this->slot_properties());
        }
    }

    public function midnight() {
        return usergetmidnight($this->timestart);
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
            // Pass non-event slot properties back to form
            $formproperties->spots = $this->spots;
        }
        return $formproperties;
    }

    public function clone_slot($slot) {
        $this->courseid = $slot->courseid;
        $this->instance = $slot->instance;
        
        $this->timestart = $slot->timestart;
        $this->timeduration = $slot->timeduration;
        $this->name = $slot->name;
        
        $this->location = $slot->location;
        
        // Clone non-event slot properties
        $this->spots = $slot->spots;
    }
}
