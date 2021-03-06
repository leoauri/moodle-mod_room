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
 * Room module date helper class.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_room\helper;

use DateTimeImmutable;

defined('MOODLE_INTERNAL') || die();

class date {
    /**
     * Given a timestamp, return a timestamp one day later
     */
    public static function one_day_later($date) {
        return (new DateTimeImmutable())->setTimestamp($date)->modify('+1 day')->getTimestamp();
    }

    public static function modified_timestamp(int $timestamp, string $modifier) {
        // convert to DateTime object
        $timestamp = new \DateTime('@' . $timestamp);
        // apply the modifier
        $timestamp->modify($modifier);
        // convert back to timestamp
        return $timestamp->getTimestamp();
    }
}