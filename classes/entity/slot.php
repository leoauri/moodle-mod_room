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

use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->dirroot . '/mod/room/lib.php');

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
    public $slotid;

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
     * @var object action and message for spot booking
     */
    public $spotbooking;

    /**
     * @var string users who have booked the spot message
     */
    public $bookedby;

    /**
     * @var booking_collection slot bookings
     */
    public $bookings;

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

        // prepare display for bookings if we have a bookings collection object
        if ($this->bookings) {
            $this->bookingsfree = max($this->spots - count($this->bookings), 0);
    
            // if there are free spots
            // TODO: if the user hasn't booked and the user can book
            if ($this->bookingsfree > 0 && !$this->bookings->user_has_booked()) {
                $this->spotbooking = (object)[
                    'action' => new moodle_url('/mod/room/spotbook.php', [
                        'slotid' => $this->slotid,
                        'id' => $modulecontext->instanceid,
                        'date' => usergetmidnight($this->timestart),
                    ]),
                    'message' => get_string('bookspot', 'mod_room'),
                    'buttonaction' => 'book-spot'
                ];
            } 
            // add cancel booking button if user has booked and slot in future
            elseif ($this->bookings->user_has_booked() && time() < $this->timestart) {
                $this->spotbooking = (object)[
                    'action' => new moodle_url('/mod/room/spotbook.php', [
                        'slotid' => $this->slotid,
                        'id' => $modulecontext->instanceid,
                        'date' => usergetmidnight($this->timestart),
                        'action' => 'cancel'
                    ]),
                    'message' => get_string('cancelbooking', 'mod_room'),
                    'buttonaction' => 'booking-cancel'
                ];
            } else {
                $this->spotbooking = null;
            }
    
            if (count($this->bookings) > 0) {
                $fullnames = [];
                foreach ($this->bookings as $booking) {
                    $fullnames[] = $booking->firstname . ' ' . $booking->lastname;
                }
                $this->bookedby = get_string('bookedby', 'mod_room') . ': ' . implode(', ', $fullnames);
            }
        }
    }

    /**
     * Load slot properties from the database
     * @param array passed to the database query
     */
    private function load_slotproperties(array $params) {
        global $DB;

        // check if record exists, since old versions didn't have this table
        if ($slotproperties = $DB->get_record('room_slot', $params)) {
            $this->slotid = (int)$slotproperties->id;
            $this->id = (int)$slotproperties->eventid;
            
            $this->spots = $slotproperties->spots;
            $this->bookings = new booking_collection($this->slotid);
        }
    }

    /**
     * Load calendar event from database
     * @param int eventid
     */
    private function load_event(int $eventid) {
        $this->event = \calendar_event::load($eventid);

        $eventproperties = $this->event->properties();
        $this->id = (int)$eventproperties->id;
        $this->timestart = $eventproperties->timestart;
        $this->timeduration = $eventproperties->timeduration;
        $this->courseid = $eventproperties->courseid;
        $this->instance = $eventproperties->instance;
        $this->name = $eventproperties->name;
        $this->location = $eventproperties->location;
}

    /**
     * Slot constructor. 
     * Accept eventid as int, or array defining 'slotid'
     * Accept null to construct a fresh slot.
     */
    public function __construct($param = null) {
        // TODO: refactor to static functions load_from_slotid and load_from_eventid
        // and have the constructor take all possible datas so it can be passed results from the database
        $eventid = null;
        $slotid = null;

        if (is_int($param)) {
            $eventid = $param;
        } elseif (is_array($param) && $param['slotid']) {
            $slotid = $param['slotid'];

            if (!is_int($slotid)) {
                throw new \InvalidArgumentException('slotid must be integer');
            }

        } elseif (!is_null($param)) {
            throw new \InvalidArgumentException(
                __CLASS__ . '::' . __FUNCTION__ . 
                ' accepts eventid as integer or array defining key slotid' . "\n" .
                'Passed param of type ' . gettype($param)
            );
        }

        if ($eventid) {
            $this->load_event($eventid);

            // Load slot database record, if it exists
            $this->load_slotproperties(['eventid' => $eventid]);
        } elseif ($slotid) {
            $this->load_slotproperties(['id' => $slotid]);
            $this->load_event($this->id);
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
        
        if (isset($data->duration)) {
            $this->timeduration = 
                $data->duration['hours'] * 60 * 60 + $data->duration['minutes'] * 60;
        }

        $this->name = $data->slottitle;
        
        // This saves the string room name to the calendar event, because it's the only way to display 
        // it in moodle calendar.  Kind of silly because we just did the db lookup to build the form...
        global $DB;
        $this->location = $DB->get_field('room_space', 'name', ['id' => $data->room]);
        
        if (isset($data->spots)) {
            $this->spots = $data->spots;
        }
    }

    private function event_properties() {
        return (object)[
            'id' => $this->id,
            'courseid' => $this->courseid,
            'instance' => $this->instance,
            'timestart' => $this->timestart,
            // event table for some reason defines timeduration as non-null
            'timeduration' => $this->timeduration ?? 0,
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
            $this->id = (int)$this->event->id;
            
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

    public function new_booking(int $userid) {
        if (!$this->bookings) {
            $this->bookings = new booking_collection($this->slotid);
        }

        if (count($this->bookings) >= $this->spots) {
            throw new \moodle_exception('Full slot cannot be booked further');
        }

        $this->bookings->new_booking($userid);
    }

    public function booking_cancel(int $userid) {
        if (!$this->bookings) {
            $this->bookings = new booking_collection($this->slotid);
        }
        if ($this->timestart < time()) {
            throw new \moodle_exception('Past booking cannot be cancelled');
        }

        $this->bookings->booking_cancel($userid);
    }
}
