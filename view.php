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
 * Prints an instance of mod_room.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

use mod_room\output\room_plan;
use mod_room\form\date_selector;

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$r  = optional_param('r', 0, PARAM_INT);

$date = optional_param('date', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('room', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('room', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($r) {
    $moduleinstance = $DB->get_record('room', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('room', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_room'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_room\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('room', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/room/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

if ($date) {
    $dateselector = new date_selector(new moodle_url('/mod/room/view.php', array('id' => $id)), ['date' => $date]);
} else {
    $dateselector = new date_selector(new moodle_url('/mod/room/view.php', array('id' => $id)));
}

if ($dateselected = $dateselector->get_data()) {
    // $dateselected = $dateselected->displaydate;
    redirect(new moodle_url('/mod/room/view.php', array('id' => $cm->id, 'date' => $dateselected->displaydate)));
}

echo $OUTPUT->header();

echo $OUTPUT->heading(format_string($moduleinstance->name));

$roomplan = new room_plan($modulecontext, $course->id, $date);

// echo $roomplan->render();

$dateselector->display();

$renderer = $PAGE->get_renderer('mod_room');
echo $renderer->render($roomplan);

// echo $roomplan->list_slots();

echo $roomplan->edit_slot_button();

echo $roomplan->room_admin_button();


echo $OUTPUT->footer();
