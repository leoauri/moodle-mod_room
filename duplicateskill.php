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
 * Display information about all the mod_room modules in the requested course.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

require_once(__DIR__.'/lib.php');

require_login(0, false);
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/room/duplicateskill.php');

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('accessdenied', 'admin');
}

$allslots = new \mod_room\entity\slot_collection([]);
$keepslots = [];
$killslots = [];

function duplicate_in($slot, $slotcollection) {
    foreach ($slotcollection as $slotincol) {
        if (
            $slot->name == $slotincol->name &&
            $slot->timestart == $slotincol->timestart &&
            $slot->timeduration == $slotincol->timeduration &&
            $slot->courseid == $slotincol->courseid &&
            $slot->location == $slotincol->location &&
            $slot->spots == $slotincol->spots
        ) {
            return true;
        }
    }
    return false;
}

$nobookingslots = [];

// get all slots with bookings
foreach ($allslots as $slot) {
    // if it has bookings add it to the keepslots
    if (count($slot->bookings)) {
        $keepslots[] = $slot;
    } else {
        $nobookingslots[] = $slot;
    }
}

foreach ($nobookingslots as $slot) {
    // if it duplicates a slot in keepslots, add it to killslots
    if (duplicate_in($slot, $keepslots)) {
        $killslots[] = $slot;
    }
    // else add it to keepslots
    else {
        $keepslots[] = $slot;
    }
}

function print_line($s = '') {
    echo $s . '<br />';
}

function slotinfo($slot) {
    return implode(' ', [
        'Name:', $slot->name,
        'Start time:', userdate($slot->timestart, get_string('strftimedaydatetime', 'langconfig')),
        'Duration:', $slot->timeduration / (60 * 60),
        'Location:', $slot->location,
        'Course:', $slot->courseid,
        'Bookings:', count($slot->bookings)
    ]);
}


$confirm = optional_param('confirm', false, PARAM_BOOL);

if ($confirm) {
    if (!confirm_sesskey()) {
        print_error('confirmsesskeybad');
    }
    foreach ($killslots as $killslot) {
        $killslot->delete();
    }
    redirect(
        new moodle_url('/mod/room/duplicateskill.php'), 
        count($killslots) . ' slots deleted!', 
        0
    );
}


echo $OUTPUT->header();

print_line('Following slots will be kept:');
print_line(count($keepslots));

foreach ($keepslots as $keepslot) {
    print_line(slotinfo($keepslot));
}

print_line();

echo '<div style="color: red;">';

print_line('Following slots will be KILLED:');
print_line(count($killslots));

foreach ($killslots as $killslot) {
    print_line(slotinfo($killslot));
    assert(count($killslot->bookings) == 0);
}

echo '</div>';

$deleteurl = new moodle_url(
    '/mod/room/duplicateskill.php', 
    ['confirm' => true]
);

$buttons = $OUTPUT->single_button($deleteurl, get_string('confirmdelete', 'mod_room'));
echo $OUTPUT->box($buttons, 'buttons');



echo $OUTPUT->footer();
