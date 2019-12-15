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
 * Room module booking collection class.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_room\entity;

defined('MOODLE_INTERNAL') || die();

/**
 * Room module booking collection.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class booking_collection implements \IteratorAggregate, \Countable {
    /**
     * @var array collection of bookings
     */
    protected $bookings;

    /**
     * @var int slot the bookings are for
     */
    public $slotid;

    public function getIterator() {
        $refresh = false;
        foreach ($this->bookings as $booking) {
            if (!isset($booking->firstname) || !isset($booking->lastname)) {
                $refresh = true;
            }
        }
        if ($refresh) {
            $this->refresh_records();
        }

        foreach ($this->bookings as $booking) {
            yield $booking;
        }
    }

    public function count() {
        return count($this->bookings);
    }

    private function refresh_records() {
        global $DB;
        $sql = "SELECT rb.id, rb.slotid, rb.userid, u.firstname, u.lastname
            FROM {room_booking} rb
            LEFT JOIN {user} u ON rb.userid = u.id
            WHERE rb.slotid = :slotid";
        $params = ['slotid' => $this->slotid];
        $this->bookings = array_values($DB->get_records_sql($sql, $params));
    }

    public function __construct(int $slotid) {
        if (!$slotid) {
            throw new \InvalidArgumentException(
                __FUNCTION__ . 'must be passed a slotid to be associated to'
            );
        }
        $this->slotid = $slotid;

        $this->refresh_records();
    }

    public function new_booking(int $userid) {
        if ($this->user_has_booked($userid)) {
            throw new \moodle_exception('A user can only book one spot in a slot');
        }

        $newbooking = new \stdClass();

        $newbooking->slotid = $this->slotid;

        $newbooking->userid = $userid;

        $newbooking->usermodified = $userid;
        $newbooking->timemodified = time();
        $newbooking->timecreated = time();

        global $DB;
        $newbooking->id = $DB->insert_record('room_booking', $newbooking);

        $this->bookings[] = $newbooking;
    }

    /**
     * Whether the given or current user has booked a spot
     * @return bool
     */
    public function user_has_booked(int $userid = null) {
        if (!$userid) {
            global $USER;
            $userid = $USER->id;
        }

        foreach ($this->bookings as $booking) {
            if ($booking->userid == $userid) {
                return $booking;
            }
        }
        return false;
    }

    public function booking_cancel(int $userid) {
        if ($booking = $this->user_has_booked($userid)) {
            // delete from database
            global $DB;
            $DB->delete_records('room_booking', ['id' => $booking->id]);
            // refresh records
            $this->refresh_records();
            // TODO: log event
        } else {
            throw new \moodle_exception('No booking by user to cancel');
        }
        
    }
}