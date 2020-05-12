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

defined('MOODLE_INTERNAL') || die();

/**
 * Base test class.
 *
 * @package    mod_room
 * @copyright  2020 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class mod_room_test_base extends advanced_testcase {
    protected $course;
    protected $roomplan;
    protected $roomspace;
    protected $datagenerator;

    protected function setup() {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->datagenerator = $this->getDataGenerator();
        $this->course = $this->datagenerator->create_course();
        $this->roomplan = $this->datagenerator->create_module('room', ['course' => $this->course->id]);

        // Set up space
        $this->roomspace = new stdClass();
        $this->roomspace->name = 'Test room';

        global $DB;
        $this->roomspace->id = $DB->insert_record('room_space', $this->roomspace);
    }
}
