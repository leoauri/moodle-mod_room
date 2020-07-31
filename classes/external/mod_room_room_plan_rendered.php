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
 * Rendered room plan external function.
 *
 * @package     mod_room
 * @copyright   2020 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_room\external;

use external_function_parameters;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

class mod_room_room_plan_rendered extends \external_api {
    public static function mod_room_room_plan_rendered_parameters() {
        return new external_function_parameters([
            'date' => new external_single_structure([
                'day' => new external_value(PARAM_INT, 'day of month'),
                'month' => new external_value(PARAM_INT, 'number of month'),
                'year' => new external_value(PARAM_INT, 'year')
            ], 'selected date'),
            'cmid' => new external_value(PARAM_INT, 'module id')
        ]);
    }

    public static function mod_room_room_plan_rendered_returns() {
        return new external_single_structure([
            'html' => new external_value(PARAM_RAW, 'rendered room plan'),
            'url' => new external_value(PARAM_TEXT, 'updated url search component')
        ]);
    }

    public static function mod_room_room_plan_rendered($date, $cmid) {
        // Validate parameters!

        // Validate context!

        // Convert received date components to timestamp
        $calendartype = \core_calendar\type_factory::get_calendar_instance();
        $gregoriandate = $calendartype->convert_to_gregorian($date['year'], $date['month'], $date['day']);
        $timestamp = make_timestamp(
            $gregoriandate['year'],
            $gregoriandate['month'],
            $gregoriandate['day'],
        );

        $cm = get_coursemodule_from_id('room', $cmid, 0, false, MUST_EXIST);
        
        global $DB;
        $moduleinstance = $DB->get_record('room', array('id' => $cm->instance), '*', MUST_EXIST);

        // Need to include mod room lib manually
        global $CFG;
        require_once("$CFG->dirroot/mod/room/lib.php");
        
        $modulecontext = \context_module::instance($cm->id);
        
        global $PAGE;
        $PAGE->set_context($modulecontext);
        $renderer = $PAGE->get_renderer('mod_room');

        $roomplan = \mod_room\output\renderer::get_room_plan_type(
            $modulecontext, 
            $moduleinstance, 
            $timestamp
        );

        // create serach component for URL update
        $url = new \moodle_url(null, ['id' => $cmid, 'date' => $timestamp]);

        return [
            'html' => $renderer->render($roomplan),
            'url' => $url->get_query_string(false)
        ];
    }
}