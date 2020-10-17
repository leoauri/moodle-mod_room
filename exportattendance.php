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
 * Prepare and send attendance export.
 *
 * @package     mod_room
 * @copyright   2020 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->libdir . '/csvlib.class.php');

require_login(0, false);
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/room/exportattendance.php');

if (!has_capability('mod/room:exportattendance', context_system::instance())) {
    print_error('accessdenied', 'admin');
}

$csv = new csv_export_writer();
$csv->set_filename('attendance_export');

// Add heading row
$csv->add_data(['datetime', 'slot', 'participant']);

// Get all bookings from database
global $DB;
// Add room_booking.id, because moodle wants first column to be unique
$sql = "SELECT rb.id, e.timestart, e.name, u.firstname, u.lastname
    FROM {room_booking} rb
    LEFT JOIN {user} u ON rb.userid = u.id
    LEFT JOIN {room_slot} s ON rb.slotid = s.id
    LEFT JOIN {event} e ON s.eventid = e.id
";
$bookings = array_values($DB->get_records_sql($sql));

// throw new Exception(var_dump($bookings));
foreach ($bookings as $booking) {
    $csv->add_data(
        [
            date_format_string($booking->timestart, '%G-%m-%d %H:%M'),
            $booking->name,
            "$booking->firstname $booking->lastname"
        ]
    );
}

$csv->download_file();
