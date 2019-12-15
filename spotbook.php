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
 * Book a spot in a slot.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

$id = required_param('id', PARAM_INT);
$slotid = required_param('slotid', PARAM_INT);

// require login
$cm = get_coursemodule_from_id('room', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, true, $cm);
$PAGE->set_url('/mod/room/spotbook.php', array('id' => $id, 'slotid' => $slotid));

$action = optional_param('action', 'book', PARAM_ALPHA);

$slot = new \mod_room\entity\slot(['slotid' => $slotid]);
global $USER;

if ($action == 'cancel') {
    $slot->booking_cancel($USER->id);
    $message = get_string('bookingcancelled', 'mod_room');
    // TODO: trigger event
} elseif ($action == 'book') {
    $slot->new_booking($USER->id);
    $message = get_string('spotbooked', 'mod_room');
    // TODO: trigger booking event
}

$redirectparams = ['id' => $id];

// TODO: remove this param and use the date from the slot itself
// TODO: add anchor to the booked slot
$date = optional_param('date', 0, PARAM_INT);
if ($date) {
    $redirectparams['date'] = $date;
}

redirect(new moodle_url('/mod/room/view.php', $redirectparams), $message, 0);