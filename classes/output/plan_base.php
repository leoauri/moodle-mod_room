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
 * Room plan base class.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_room\output;

use html_writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Room plan renderable.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class plan_base implements \renderable, \templatable {
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
            $url = new \moodle_url(
                '/mod/room/slotedit.php', 
                array('id' => $this->modulecontext->instanceid, 'date' => $this->date));
            $label = get_string('addslot', 'mod_room');
            return html_writer::div(html_writer::link(
                $url, $label, array('class' => 'btn btn-secondary')), 'roomplan-slot-add m-t-1');
        }
    }

}
