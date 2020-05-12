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
$id = required_param('id',PARAM_INT);

$cm             = get_coursemodule_from_id('room', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$moduleinstance = $DB->get_record('room', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);

// TODO: check if we are outside of course context, set up accordingly
$modulecontext = context_module::instance($cm->id);

$PAGE->set_url('/mod/room/slotedit.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$eventid = optional_param('eventid', 0, PARAM_INT);

// Set up slot and properties for either saving the slot or passing to the form
$slot = new \mod_room\entity\slot($eventid);

// Take path of contexts from context_module
$contexts = explode('/', $modulecontext->path);

// strip off module and site context, and empty element
$contexts = array_slice($contexts, 2, -1);

// Dirty fix for master plans: add slot context if it is not present
if ($slotcontext = $slot->context_or_course_context()) {
    if (!in_array($slotcontext, $contexts)) {
        $contexts[] = $slotcontext;
    }
}

// pass in as options to the form
$contextoptions = [];
foreach ($contexts as $context) {
    $contextoptions[$context] = context_helper::instance_by_id($context)->get_context_name(false);
}    

$mform = new \mod_room\form\slot_edit(
    new moodle_url(
        '/mod/room/slotedit.php', 
        array('id' => $id, 'eventid' => $eventid)
    ), 
    [
        'eventid' => $eventid,
        'contextoptions' => $contextoptions
    ]
);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/mod/room/view.php', array('id' => $id)));
} else if ($data = $mform->get_data()) {
    // TODO: check capability in context received context from the form, not just here
    if (confirm_sesskey() && has_capability('mod/room:editslots', context_course::instance($course->id))) {
        $slot->set_slot_properties($data, $moduleinstance);
        if (!empty($data->saveasnewslot)) {
            $slot->save_as_new();
        } elseif (!empty($data->deleteslot)) {
            redirect($slot->get_deleteurl($modulecontext));
        } elseif (!empty($data->slotduplication)) {
            redirect(new \moodle_url(
                '/mod/room/slotduplication.php', 
                ['id' => $id, 'eventid' => $eventid]
            ));
        } else {
            $slot->save();
        }

        redirect(
            new moodle_url(
                '/mod/room/view.php', 
                array('id' => $cm->id, 'date' => $slot->midnight())
            ), 
            get_string('changessaved'), 
            0
        );
    }
}

// If we reach here we are not receiving a submitted form, we are displaying one

$formproperties = $slot->form_properties();

// Set new event to start on viewed date or today at 10AM by default
if (!isset($formproperties->starttime)) {
    $vieweddate = optional_param('date', 0, PARAM_INT);
    $formproperties->starttime = usergetmidnight($vieweddate ? $vieweddate : time()) + 10 * 60 * 60;
}
// Set new slot to be in this course context
if (!isset($formproperties->context)) {
    $formproperties->context = context_course::instance($course->id)->id;
}

$mform->set_data($formproperties);

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
