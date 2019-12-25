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
 * Delete a slot from the mod_room database.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot.'/calendar/lib.php');

$eventid = required_param('eventid', PARAM_INT);
$confirm = optional_param('confirm', false, PARAM_BOOL);
$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('room', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('room', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);

$PAGE->set_url('/mod/room/slotdelete.php', array('id' => $cm->id, 'eventid' => $eventid));
$PAGE->set_title(format_string($moduleinstance->name) . ': ' . get_string('deleteslot', 'mod_room'));
$PAGE->set_heading(format_string($course->fullname));

$modulecontext = context_module::instance($cm->id);
$PAGE->set_context($modulecontext);

$slot = new \mod_room\entity\slot($eventid);

// Setup redirect url with id of module and time set to day of event
$returnurl = new moodle_url('/mod/room/view.php');
$returnurl->param('date', usergetmidnight($slot->timestart));
$returnurl->param('id', $cm->id);

if ($confirm) {
    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad');
    }
    $slot->delete();
    redirect($returnurl);
}

// output page
echo $OUTPUT->header();

$deleteurl = new moodle_url(
    '/mod/room/slotdelete.php', 
    ['eventid' => $eventid, 'id' => $id, 'confirm' => true]
);

$buttons = $OUTPUT->single_button($deleteurl, get_string('confirmdelete', 'mod_room'));
$buttons .= $OUTPUT->single_button($returnurl, get_string('cancel'));
echo $OUTPUT->box($buttons, 'buttons');

echo $OUTPUT->footer();
