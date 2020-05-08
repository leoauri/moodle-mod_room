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

use plugin_renderer_base;

class renderer extends plugin_renderer_base {
    protected function render_room_plan(room_plan $room_plan) {
        return $this->render_from_template('mod_room/room_plan', $room_plan->export_for_template($this));
    }

    protected function render_room_list(room_list $room_list) {
        return $this->render_from_template('mod_room/room_list', $room_list->export_for_template($this));
    }

    protected function render_visual_plan(visual_plan $visual_plan) {
        return $this->render_from_template('mod_room/visual_plan', $visual_plan->export_for_template($this));
    }
}
