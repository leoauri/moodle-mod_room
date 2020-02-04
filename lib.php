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
 * Library of interface functions and constants.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('ROOM_EVENT_TYPE_SLOT', 'slot');

define('ROOM_PLAN_TYPE_STANDARD', 0);
define('ROOM_PLAN_TYPE_MASTER', 1);

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function room_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_room into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_room_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function room_add_instance($moduleinstance, $mform = null) {
    global $DB;
    global $USER;

    $moduleinstance->timecreated = time();
    $moduleinstance->usermodified = $USER->id;

    // Standard room plans are default
    if (empty($moduleinstance->type)) {
        $moduleinstance->type = ROOM_PLAN_TYPE_STANDARD;
    }

    $id = $DB->insert_record('room', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_room in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_room_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function room_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('room', $moduleinstance);
}

/**
 * Removes an instance of the mod_room from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function room_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('room', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('room', array('id' => $id));

    return true;
}

/**
 * Extends the global navigation tree by adding mod_room nodes if there is a relevant content.
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $roomnode An object representing the navigation tree node.
 * @param stdClass $course.
 * @param stdClass $module.
 * @param cm_info $cm.
 */
function room_extend_navigation($roomnode, $course, $module, $cm) {
}

/**
 * Extends the settings navigation with the mod_room settings.
 *
 * This function is called when the context for the page is a mod_room module.
 * This is not called by AJAX so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $roomnode {@link navigation_node}
 */
function room_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $roomnode) {
    global $PAGE;

    $cm = $PAGE->cm;
    $context = $cm->context;

    if (has_capability('mod/room:editrooms', $context)) {
        $url = new moodle_url('/mod/room/roomadmin.php', ['id' => $cm->id]);
        $label = get_string('roomadministration', 'mod_room');
        $roomnode->add($label, $url);
    }
}

/**
 * Get icon mapping for font-awesome.
 *
 * @return  array
 */
function mod_room_get_fontawesome_icon_map() {
    return [
        'mod_room:i/cookie' => 'fa-cookie',
        'mod_room:i/cookie-bite' => 'fa-cookie-bite',
    ];
}
