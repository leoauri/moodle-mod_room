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

use mod_room\form\date_selector;

// Course_module ID
$id = required_param('id', PARAM_INT);

$date = optional_param('date', 0, PARAM_INT);
if (empty($date)) {
    $date = usergetmidnight(time());
}

$cm = get_coursemodule_from_id('room', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('room', array('id' => $cm->instance), '*', MUST_EXIST);

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

$dateselector = new date_selector(new moodle_url('/mod/room/view.php', array('id' => $id)), ['date' => $date]);

if ($dateselected = $dateselector->get_data()) {
    redirect(new moodle_url('/mod/room/view.php', array('id' => $cm->id, 'date' => $dateselected->displaydate)));
}

echo $OUTPUT->header();

$heading = format_string($moduleinstance->name);
// Add (Master room plan) to master room plans
if ($moduleinstance->type == ROOM_PLAN_TYPE_MASTER) {
    $heading .= ' (' . get_string('masterroomplan', 'mod_room') . ')';
}
echo $OUTPUT->heading($heading);

if ($moduleinstance->type == ROOM_PLAN_TYPE_UPCOMING) {
    echo $OUTPUT->heading(get_string('upcomingslots', 'mod_room') . ':', 3);
}

// Show date selector for appropriate plan types
if ($moduleinstance->type != ROOM_PLAN_TYPE_UPCOMING) {
    $dateselector->display();

    // Javascript for reloading plan on date change
    $calendartype = \core_calendar\type_factory::get_calendar_instance();
    $datecomponents = $calendartype->timestamp_to_date_array($date);

    $PAGE->requires->js_call_amd(
        'mod_room/date_reload', 
        'init', 
        [
            'initialdate' => [
                'day' => $datecomponents['mday'], 
                'month' => $datecomponents['mon'], 
                'year' => $datecomponents['year']
            ], 
            'cmid' => $id
        ]
    );

}

$roomplan = mod_room\output\renderer::get_room_plan_type(
    $modulecontext, 
    $moduleinstance, 
    $date
);
$renderer = $PAGE->get_renderer('mod_room');
echo html_writer::tag('div', $renderer->render($roomplan), ['id' => 'mod-room-room-plan']);

if ($moduleinstance->type == ROOM_PLAN_TYPE_UPCOMING && $moduleinstance->filters) {
    $PAGE->requires->js_call_amd('mod_room/slot_display_filters', 'init', ['filterlist' => $moduleinstance->filters]);
}

echo $roomplan->edit_slot_button();

echo $OUTPUT->footer();
