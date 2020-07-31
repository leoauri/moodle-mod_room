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
 * Web service specification.
 *
 * @package     mod_room
 * @category    upgrade
 * @copyright   2020 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_room_duplication_preview' => [
        'classname' => 'mod_room\\external\\mod_room_duplication_preview',
        'methodname' => 'mod_room_duplication_preview',
        'description' => 'Fetch a preview of slots that a given duplication operation should generate.',
        'type' => 'read',
        'ajax' => true,
    ],
    'mod_room_room_plan_rendered' => [
        'classname' => 'mod_room\\external\\mod_room_room_plan_rendered',
        'methodname' => 'mod_room_room_plan_rendered',
        'description' => 'Return a rendered view of a room plan for a given date.',
        'type' => 'read',
        'ajax' => true,
    ]
];
