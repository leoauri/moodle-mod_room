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
 * Room plan renderable.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_room\output;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use html_writer;

/**
 * Room plan renderable.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class room_plan {
    /**
     * @var \context_module
     */
    public $modulecontext;

    public function __construct($modulecontext) {
        $this->modulecontext = $modulecontext;
    }

    public function render(int $hourstart = 9, int $hourend = 23) {
        global $DB;

        $output = '';

        if (has_capability('mod/room:editslots', $this->modulecontext)) {
            $url = new moodle_url(
                '/mod/room/slotedit.php', 
                array('id' => $this->modulecontext->get_course_context()->instanceid));
            $label = get_string('addslot', 'mod_room');
            $output .= html_writer::div(html_writer::link(
                $url, $label, array('class' => 'btn btn-secondary')), 'roomplan-slot-add');
        }

        $outputtable = new \html_table();
        $outputtable->id = 'room-plan';


        // First cell of each row is the hour value
        for ($hour = $hourstart; $hour < $hourend; $hour++) {
            $outputtable->data[] = array($hour);
        }

        // The header row has a blank where the times come,
        // then the room names, each spanning two columns
        $outputtable->head = array('');
        $outputtable->headspan = array(1);
        $rooms = $DB->get_records('room_space');
        foreach ($rooms as $room) {
            $outputtable->head[] = $room->name;
            $outputtable->headspan[] = 2;

            $firstrow = true;
            foreach ($outputtable->data as &$row) {
                if ($firstrow) {
                    $firstrow = false;
                    // The first row contains the container for the slots which spans all rows
                    $slotscontainer = new \html_table_cell();
                    $slotscontainer->rowspan = $hourend - $hourstart;
                    $row[] = $slotscontainer;
                }

                $row[] = new \html_table_cell('+');
            }
            unset($row);
        }

        $output .= html_writer::table($outputtable);
        return $output;
    }
}
