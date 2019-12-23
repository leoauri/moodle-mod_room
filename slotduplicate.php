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
 * Duplicate mod_room slots.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \mod_room\entity\slot_collection;

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot.'/calendar/lib.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('room', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_login($course, false, $cm);

$PAGE->set_url('/mod/room/slotduplicate.php', array('id' => $cm->id));

$moduleinstance = $DB->get_record('room', array('id' => $cm->instance), '*', MUST_EXIST);
$pagetitle = format_string($moduleinstance->name) . ': ' . 
    strtolower(get_string('slotduplicationtool', 'mod_room'));
$PAGE->set_title($pagetitle);
$PAGE->set_heading(format_string($course->fullname));

$modulecontext = context_module::instance($cm->id);
$PAGE->set_context($modulecontext);

// setup start and end of week
$now = new DateTime('now', core_date::get_user_timezone_object());
$startdate = new DateTime();
$startdate->setISODate($now->format('o'), $now->format('W'));
$enddate = clone $startdate;
$enddate->modify('+6 days');

$mform = new \mod_room\form\slot_duplicate(
    new moodle_url('/mod/room/slotduplicate.php', array('id' => $cm->id)),
    ['startdate' => $startdate->getTimestamp(), 'enddate' => $enddate->getTimestamp()]
);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/room/view.php', array('id' => $id)));
} else if ($data = $mform->get_data()) {
    // we are here if duplicate slots has been confirmed
    if (confirm_sesskey() && has_capability('mod/room:editslots', context_course::instance($course->id))) {
        // Add 24hrs to end date to include specified day
        $data->enddate = slot_collection::modified_timestamp($data->enddate, '+1 day');

        // retrieve all slots in the date range
        $slots = new slot_collection(
            ['start' => $data->startdate, 'end' => $data->enddate]
        );

        // for each slot add one week to starttime
        $slots->modify_starttimes('+1 week');

        // save each slot as new slot
        $slots->save_as_new();

        redirect(
            new moodle_url(
                '/mod/room/view.php', 
                ['id' => $cm->id, 'date' => slot_collection::modified_timestamp($data->startdate, '+1 week')]
            ), 
            get_string('changessaved'), 
            0
        );
    }
}

echo $OUTPUT->header();

echo $OUTPUT->heading($pagetitle);

$mform->display();

echo $OUTPUT->footer();
