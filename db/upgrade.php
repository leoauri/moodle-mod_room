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
 * Plugin upgrade steps are defined here.
 *
 * @package     mod_room
 * @category    upgrade
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/upgradelib.php');

/**
 * Execute mod_room upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_room_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019112602) {
        // Define key spaceid (foreign) to be dropped form room_slot.
        $table = new xmldb_table('room_slot');
        $key = new xmldb_key('spaceid', XMLDB_KEY_FOREIGN, ['spaceid'], 'room_space', ['id']);

        // Launch drop key spaceid.
        $dbman->drop_key($table, $key);

        // Define field spaceid to be dropped from room_slot.
        $field = new xmldb_field('spaceid');

        // Conditionally launch drop field spaceid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field starttime to be dropped from room_slot.
        $field = new xmldb_field('starttime');

        // Conditionally launch drop field starttime.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field duration to be dropped from room_slot.
        $field = new xmldb_field('duration');

        // Conditionally launch drop field duration.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define key owner (foreign) to be dropped form room_slot.
        $key = new xmldb_key('owner', XMLDB_KEY_FOREIGN, ['owner'], 'user', ['id']);

        // Launch drop key owner.
        $dbman->drop_key($table, $key);

        // Define field owner to be dropped from room_slot.
        $field = new xmldb_field('owner');

        // Conditionally launch drop field owner.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        // Define field title to be dropped from room_slot.
        $field = new xmldb_field('title');

        // Conditionally launch drop field title.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field eventid to be added to room_slot.
        $field = new xmldb_field('eventid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');

        // Conditionally launch add field eventid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define key eventid (foreign-unique) to be added to room_slot.
        $key = new xmldb_key('eventid', XMLDB_KEY_FOREIGN_UNIQUE, ['eventid'], 'event', ['id']);

        // Launch add key eventid.
        $dbman->add_key($table, $key);

        // Define field spots to be added to room_slot.
        $field = new xmldb_field('spots', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'eventid');

        // Conditionally launch add field spots.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Room savepoint reached.
        upgrade_mod_savepoint(true, 2019112602, 'room');
    }

    return true;
}
