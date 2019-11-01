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
class room_list implements renderable, templatable {
    public function export_for_template(renderer_base $renderer) {
        global $DB;
        $rooms = array_values($DB->get_records('room_space'));
        // var_dump($rooms);
        $output = new \stdClass();
        $output->rooms = $rooms;
        return $output;
    }
}
