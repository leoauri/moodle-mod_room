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
 * @copyright   2020 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

$eventid = required_param('eventid', PARAM_INT);

$slot = new \mod_room\entity\slot($eventid);

$id = required_param('id',PARAM_INT);

$cm = get_coursemodule_from_id('room', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_login($course, false, $cm);

$thispageurl = new moodle_url('/mod/room/slotduplication.php', ['eventid' => $eventid, 'id' => $id]);
$form = new \mod_room\form\slot_duplication($thispageurl);

$modulecontext = context_module::instance($cm->id);

if ($form->is_cancelled()) {
    redirect($slot->get_editurl($modulecontext));
} else if ($data = $form->get_data()) {
    $duplicates = \mod_room\entity\slot_collection::duplicates_consecutive(
        $slot, 
        $data->numberofduplicates
    );

    $duplicates->save_all_as_new();

    redirect(
        new moodle_url(
            '/mod/room/view.php', 
            ['id' => $id, 'date' => $slot->midnight()]
        ), 
        get_string('duplicateslotssaved', 'mod_room')
    );
}

$moduleinstance = $DB->get_record('room', array('id' => $cm->instance), '*', MUST_EXIST);

$PAGE->set_url($thispageurl);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$PAGE->requires->js_call_amd('mod_room/slot_duplication', 'init', ['cmid' => $cm->id, 'eventid' => $slot->id, ]);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_room');

$slot->prepare_display($modulecontext);
$slot->canedit = null;
$slot->bookingsfree = null;
$slot->bookedby = null;
$slot->spotbooking = null;

echo $renderer->render_from_template('mod_room/slot_info', $slot);

$form->display();

echo $OUTPUT->footer();

