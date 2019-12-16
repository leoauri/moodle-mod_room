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

use mod_room\output\room_list;

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
// }

$sitecontext = context_system::instance();

// TODO: must also require login
require_login($course, true, $cm);
require_capability('mod/room:editrooms', $sitecontext);



if ($id) {
    $modulecontext = context_module::instance($cm->id);
    $PAGE->set_context($modulecontext);
} else {
    $PAGE->set_context($sitecontext);
}

// TODO: set url id param if id passed in
$PAGE->set_url('/mod/room/roomedit.php', array('id' => $id));
$PAGE->set_title(get_string('roomadministration', 'mod_room'));
$PAGE->set_heading(get_string('roomadministration', 'mod_room'));



echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('availablerooms', 'mod_room'));

$roomlist = new room_list();

$renderer = $PAGE->get_renderer('mod_room');
echo $renderer->render($roomlist);

echo $roomlist->button_room_new($id);

echo $OUTPUT->footer();
