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
 * Add a slot to the mod_room database.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot.'/calendar/lib.php');


// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$r  = optional_param('r', 0, PARAM_INT);

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

require_login($course, false, $cm);

// TODO: check if we are outside of course context, set up accordingly
$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/room/slotedit.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$slotid = optional_param('slotid', 0, PARAM_INT);

$mform = new \mod_room\form\slot_edit(
    new moodle_url(
        '/mod/room/slotedit.php', 
        array('id' => $id, 'slotid' => $slotid)
    ), 
    ['slotid' => $slotid]
);

// Set up slot and properties for either saving the slot or passing to the form
if ($slotid) {
    $slot = calendar_event::load($slotid);
    // var_dump($slot);
    $properties = $slot->properties();
} else {
    $properties = new stdClass();
}


if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/room/view.php', array('id' => $id)));
} else if ($data = $mform->get_data()) {
    if (confirm_sesskey() && has_capability('mod/room:editslots', context_course::instance($course->id))) {
        $properties->modulename = 'room';
        $properties->courseid = $moduleinstance->course;
        $properties->groupid = 0;
        $properties->userid = 0;
        $properties->instance = $moduleinstance->id;

        $properties->type = CALENDAR_EVENT_TYPE_STANDARD;
        $properties->eventtype = ROOM_EVENT_TYPE_SLOT;
        $properties->timestart = $data->starttime;
        $properties->timeduration = $data->duration * 60;
        $properties->name = $data->slottitle;
        
        // This saves the string room name to the calendar event, because it's the only way to display 
        // it in moodle calendar.  Kind of silly because we just did the db lookup to build the form...
        global $DB;
        $properties->location = $DB->get_field('room_space', 'name', ['id' => $data->room]);

        if ($slotid) {
            $slot->update($properties);
        } else {
            calendar_event::create($properties, false);
        }


        // TODO: trigger add slot event
        redirect(
            new moodle_url(
                '/mod/room/view.php', 
                array('id' => $cm->id, 'date' => usergetmidnight($properties->timestart))
            ), 
            get_string('changessaved'), 
            0
        );
    }
}

// If we reach here we are not receiving a submitted form, we are displaying one

$formproperties = new stdClass();
// If we are editing an existing slot, send the existing data in
if ($slotid) {
    $formproperties->slottitle = $slot->name;
    // Another silly lookup of the roomid this time
    $formproperties->room = $DB->get_field_select(
        'room_space', 
        'id', 
        'name = :name', 
        ['name' => $slot->location]
    );
    $formproperties->starttime = $slot->timestart;
    if ($duration = (int)($slot->timeduration / 60)) {
        $formproperties->duration = $duration;
    }
} else {
    // Set new event to start on viewed date or today at midday by default
    $vieweddate = optional_param('date', 0, PARAM_INT);
    $formproperties->starttime = usergetmidnight($vieweddate ? $vieweddate : time()) + 12 * 60 * 60;
}
$mform->set_data($formproperties);

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
