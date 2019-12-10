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
 * File containing tests for version upgrades.
 *
 * @package     mod_room
 * @category    test
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The upgrade test class.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_room_upgrade_testcase extends advanced_testcase {
    public function test_upgrade_from_2019112600() {
        $this->resetAfterTest(true);

        global $DB;
        $dbman = $DB->get_manager();

        // Set old schema version
        require_once(__DIR__ . '/../../../lib/upgradelib.php');
        set_config('version', 2019112600, 'mod_room');

        // Drop current version table and set up outdated schema
        $dbman->drop_table(new xmldb_table('room_slot'));

        $table = new xmldb_table('room_slot');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('spaceid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'id');
        $table->add_field('title', XMLDB_TYPE_TEXT, null, null, null, null, null, 'spaceid');
        $table->add_field('owner', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'title');
        $table->add_field('starttime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'owner');
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'starttime');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'duration');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'usermodified');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'timecreated');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);
        $table->add_key('spaceid', XMLDB_KEY_FOREIGN, ['spaceid'], 'room_space', ['id']);
        $table->add_key('owner', XMLDB_KEY_FOREIGN, ['owner'], 'user', ['id']);
        $table->setComment('Slots are appointments in rooms.');

        $dbman->create_table($table);

        $tables = $DB->get_tables(false);
        $this->assertArrayHasKey('room_slot', $tables);

        // Apply upgrade script
        require_once(__DIR__ . '/../db/upgrade.php');
        xmldb_room_upgrade(2019112600);

        // Check that old fields are not present
        $columns = $DB->get_columns('room_slot', false);
        foreach (['spaceid', 'starttime', 'duration', 'owner', 'title'] as $nonfield) {
            $this->assertArrayNotHasKey($nonfield, $columns);
        }
        // Check that new fields are present
        foreach (['eventid', 'spots'] as $newfield) {
            $this->assertArrayHasKey($newfield, $columns);
        }

        // No way of testing keys!  Apparently

        // Check that booking table is present
        $tables = $DB->get_tables(false);
        $this->assertArrayHasKey('room_booking', $tables);
        
        $columns = $DB->get_columns('room_booking', false);
        $this->assertEquals(
            ['id', 'slotid', 'userid', 'usermodified', 'timecreated', 'timemodified'], 
            array_keys($columns)
        );
    }
}
