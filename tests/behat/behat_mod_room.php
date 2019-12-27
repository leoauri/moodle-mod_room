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
 * Behat step definitions for room module.
 *
 * @package   mod_room
 * @category  test
 * @copyright 2019 Leo Auri <code@leoauri.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode;

class behat_mod_room extends behat_base {
    /**
     * @Given /^the following rooms are defined in the room module:$/
     */
    public function define_rooms(TableNode $table) {
        global $USER;
        global $DB;
        
        foreach ($table->getHash() as $roomdata) {
            if (empty($roomdata['roomname'])) {
                throw new Exception('Rooms require a roomname');
            }
            $newroom = new stdClass();
            $newroom->name = $roomdata['roomname'];
            $newroom->usermodified = $USER->id;
            $newroom->timecreated = time();
            $newroom->timemodified = time();
    
            $DB->insert_record('room_space', $newroom);    
        }
    }

    /**
     * @Given /^the following slots are defined in the room module:$/
     */
    public function define_slots(TableNode $table) {
        global $USER;
        global $DB;
        
        foreach ($table->getHash() as $slotdata) {
            if (empty($slotdata['roomplan'])) {
                throw new Exception('Slots must be created in a room plan module');
            }
            if (empty($slotdata['room'])) {
                throw new Exception('Slots must be created in a room');
            }

            $moduleinstance = $DB->get_record('room', ['name' => $slotdata['roomplan']], '*', MUST_EXIST);

            $starttime = new DateTime($slotdata['starttime']);
            $starttime = $starttime->getTimestamp();

            $roomid = $DB->get_field_select('room_space', 'id', 'name = :name', [
                'name' => $slotdata['room']
            ], MUST_EXIST);
            
            if ($slotdata['duration']) {
                $parts = explode(':', $slotdata['duration']);
                $duration['hours'] = (int)$parts[0];
                $duration['minutes'] = (int)$parts[1];
            } else {
                $duration = null;
            }

            $properties = (object)[
                'starttime' => $starttime,
                'slottitle' => $slotdata['slottitle'],
                'room' => $roomid,
                'duration' => $duration,
                'spots' => $slotdata['spots']
            ];

            if (!empty($slotdata['context'])) {
                $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
                $cm = get_coursemodule_from_instance('room', $moduleinstance->id, $course->id, false, MUST_EXIST);
                $modulecontext = context_module::instance($cm->id);

                $possiblecontexts = array_slice(explode('/', $modulecontext->path), 1);

                foreach ($possiblecontexts as $possiblecontext) {
                    if ($slotdata['context'] == context_helper::instance_by_id($possiblecontext)->get_context_name(false)) {
                        $properties->context = $possiblecontext;
                    }
                }
                if (!isset($properties->context)) {
                    throw new Exception('Specified slot context not found');
                }
            }

            $newslot = new \mod_room\entity\slot();
            $newslot->set_slot_properties($properties, $moduleinstance);
            
            $newslot->save();
        }
    }

    /**
     * @Then dump room
     */
    public function dumpRoom() {
        global $DB;
        $dump = $DB->get_records('event');
        throw new Exception(var_export($dump, true));
    }
}
