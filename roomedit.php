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
 * Add a room to the mod_room database.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course_module ID, or
$id = optional_param('id', null, PARAM_INT);

// // ... module instance id.
// $r  = optional_param('r', 0, PARAM_INT);

$cm = null;
$course = null;
$moduleinstance = null;

if ($id) {
    $cm             = get_coursemodule_from_id('room', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('room', array('id' => $cm->instance), '*', MUST_EXIST);
} 
// else if ($r) {
//     $moduleinstance = $DB->get_record('room', array('id' => $n), '*', MUST_EXIST);
//     $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
//     $cm             = get_coursemodule_from_instance('room', $moduleinstance->id, $course->id, false, MUST_EXIST);
// } else {
//     print_error(get_string('missingidandcmid', 'mod_room'));
// }

$sitecontext = context_system::instance();

require_login($course, true, $cm);
require_capability('mod/room:editrooms', $sitecontext);

if ($id) {
    $modulecontext = context_module::instance($cm->id);
    $PAGE->set_context($modulecontext);
} else {
    $PAGE->set_context($sitecontext);
}

$PAGE->set_url('/mod/room/roomedit.php', array('id' => $id));
$PAGE->set_title(get_string('roomadministration', 'mod_room'));
$PAGE->set_heading(get_string('roomadministration', 'mod_room'));

$mform = new \mod_room\form\room_edit(new moodle_url('/mod/room/roomedit.php', array('id' => $id)));

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/room/roomadmin.php', array('id' => $id)));
} else if ($data = $mform->get_data()) {
    if (confirm_sesskey() && has_capability('mod/room:editrooms', context_system::instance())) {
        $newroom = new stdClass();
        $newroom->name = $data->roomname;
        global $USER;
        $newroom->usermodified = $USER->id;
        $newroom->timecreated = time();
        $newroom->timemodified = time();

        $DB->insert_record('room_space', $newroom);
        // TODO: trigger add room event
        $url = new moodle_url('/mod/room/roomadmin.php');
        if ($id) {
            $url->param('id', $id);
        }
        redirect($url, get_string('changessaved'), 0);
    }
}


echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
