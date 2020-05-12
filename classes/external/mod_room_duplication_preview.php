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
 * Slot duplication preview external function.
 *
 * @package     mod_room
 * @copyright   2020 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_room\external;

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

class mod_room_duplication_preview extends \external_api {
    public static function mod_room_duplication_preview_parameters() {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'id of the record from {course_modules} table'),
            'eventid' => new external_value(PARAM_INT, 'id of associated event'),
            'n_duplicates' => new external_value(PARAM_INT, 'number of duplicates to preview'),
        ]);
    }

    public static function mod_room_duplication_preview_returns() {
        return new external_single_structure([
            'message' => new external_value(PARAM_TEXT, 'Explanatory message', VALUE_OPTIONAL),
            'slots' => new external_multiple_structure(
                new external_single_structure([
                    'name' => new external_value(PARAM_TEXT, 'Slot name'),
                    'time' => new external_value(PARAM_TEXT, 'Slot time string')
                ]), 'Preview of to-be-duplicated slots', VALUE_OPTIONAL
            )
        ]);
    }

    public static function mod_room_duplication_preview($cmid, $eventid, $n_duplicates) {
        // Validate parameters!
        $params = self::validate_parameters(
            self::mod_room_duplication_preview_parameters(), 
            ['cmid' => $cmid, 'eventid' => $eventid, 'n_duplicates' => $n_duplicates]
        );
        list($cmid, $eventid, $n_duplicates) = [$params['cmid'], $params['eventid'], $params['n_duplicates']];

        // Validate context!
        $context = \context_module::instance($cmid);
        self::validate_context($context);
        require_capability('mod/room:editslots', $context);

        $slot = new \mod_room\entity\slot($eventid);
        $duplicates = \mod_room\entity\slot_collection::duplicates_consecutive($slot, $n_duplicates);

        $duplicates->prepare_display($context);

        $duplicateslotsinfo = [];

        reset($duplicates);
        foreach ($duplicates as $duplicate) {
            $time = $duplicate->userdatestart;
            if ($duplicate->userdateend) {
                $time .= ' » ' . $duplicate->userdateend;
            }
            $info = [
                'name' => $duplicate->displayname,
                'time' => $time
            ];
            $duplicateslotsinfo[] = $info;
        }

        return [
            'message' => get_string('slotstobecreated', 'mod_room'),
            'slots' => $duplicateslotsinfo
        ];
    }
}