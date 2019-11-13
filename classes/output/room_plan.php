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
use renderable;
use renderer_base;
use templatable;

// use \core_calendar\local\api as calendar_api;

/**
 * Room plan renderable.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class room_plan implements renderable, templatable {
    /**
     * @var \context_module
     */
    public $modulecontext;

    /**
     * @var \stdClass module instance
     */
    protected $moduleinstance;

    /**
     * @var int date to render
     */
    public $date;

    /**
     * @var array of events to render
     */
    protected $events;

    public function __construct($modulecontext, $moduleinstance, $date) {
        $this->modulecontext = $modulecontext;
        $this->moduleinstance = $moduleinstance;
        $this->date = $date ? $date : usergetmidnight(time());
    }

    

    public function edit_slot_button() {
        if (has_capability('mod/room:editslots', $this->modulecontext)) {
            $url = new moodle_url(
                '/mod/room/slotedit.php', 
                array('id' => $this->modulecontext->instanceid, 'date' => $this->date));
            $label = get_string('addslot', 'mod_room');
            return html_writer::div(html_writer::link(
                $url, $label, array('class' => 'btn btn-secondary')), 'roomplan-slot-add m-t-1');
        }
    }

    public function room_admin_button() {
        if (has_capability('mod/room:editrooms', \context_system::instance())) {
            $url = new moodle_url('/mod/room/roomadmin.php', ['id' => $this->modulecontext->instanceid]);
            $label = get_string('roomadministration', 'mod_room');
            return html_writer::div(html_writer::link(
                $url, $label, array('class' => 'btn btn-secondary')), 'roomplan-slot-add m-t-1');
        }
    }


    public function retrieve_events() {

        if (!empty($this->events)) {
            return;
        }

        $timestartfrom = $this->date;
        $timestartto = $timestartfrom + 24 * 60 * 60;

        // FIX: also return in-progress events, i.e. that start before day and haven't ended  :|
        $sql = "SELECT e.* 
            FROM {event} e
            WHERE timestart >= :timefrom 
                AND timestart <= :timeto
                AND modulename = :modulename
                AND instance = :instance";

        global $DB;
        $this->events = array_values(
            $DB->get_records_sql($sql, [
                'timefrom' => $timestartfrom,
                'timeto' => $timestartto,
                'modulename' => 'room',
                'instance' => $this->moduleinstance->id,
            ])
        );

        // Calculate human-readable date strings for start and end
        foreach ($this->events as &$event) {
            $event->userdatestart = userdate(
                $event->timestart, 
                get_string('strftimedaydatetime', 'langconfig')
            );
            // TODO: pass human-readable end time, or, if other day, date
            // if ($event->duration) {
            //     // If 
            // }
            $event->canedit = has_capability('mod/room:editslots', $this->modulecontext);
            if ($event->canedit) {
                $event->deleteurl = new moodle_url(
                    '/mod/room/slotdelete.php', 
                    [
                        'slotid' => $event->id,
                        'id' => $this->modulecontext->instanceid
                    ]
                );
                $event->editurl = new moodle_url(
                    '/mod/room/slotedit.php',
                    [
                        'slotid' => $event->id,
                        'id' => $this->modulecontext->instanceid
                    ]
                );
            }
        }
        unset($event);
    }

    /**
     * Export data so this can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $renderer) {
        $this->retrieve_events();
        $output = new \stdClass();
        $output->events = $this->events;
        if (! $output->events) {
            $output->noslotsmessage = get_string('noslots', 'mod_room');
        }
        return $output;
    }

    public function render(int $hourstart = 9, int $hourend = 23) {
        global $DB;

        $output = '';


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
