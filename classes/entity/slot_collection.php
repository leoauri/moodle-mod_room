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
 * Room module slot collection class.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_room\entity;

defined('MOODLE_INTERNAL') || die();

use \mod_room\entity\slot;

/**
 * Room module slot collection.
 *
 * @package    mod_room
 * @copyright  2019 Leo Auri <code@leoauri.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class slot_collection implements \IteratorAggregate, \Countable {
    /**
     * @var array collection of slots
     */
    protected $slots;

    public function __construct(array $options) {
        // Convert any DateTime objects to timestamps
        foreach (['start', 'end'] as $timeoption) {
            if ($options[$timeoption] instanceof \DateTime) {
                $options[$timeoption] = $options[$timeoption]->getTimestamp();
            }
        }
        // FIX: also return in-progress events, i.e. that start before day and haven't ended  :|
        $sql = "SELECT e.id 
            FROM {event} e
            WHERE modulename = 'room'";
        $searchoptions = [];

        if (isset($options['start'])) {
            $sql .= ' AND timestart >= :start';
            $searchoptions['start'] = $options['start'];
        }

        if (isset($options['end'])) {
            $sql .= ' AND timestart <= :end';
            $searchoptions['end'] = $options['end'];
        }

        if (isset($options['instance'])) {
            $sql .= ' AND instance = :instance';
            $searchoptions['instance'] = $options['instance'];
        }

        global $DB;

        // TODO: make this an array of slot objects
        $results = array_values($DB->get_records_sql($sql, $searchoptions));
        $this->slots = [];
        foreach ($results as $result) {
            $this->slots[] = new slot((int)$result->id);
        }
    }

    public function prepare_display(\context_module $modulecontext) {
        foreach ($this->slots as &$slot) {
            $slot->prepare_display($modulecontext);
        }
        unset($slot);
    }

    public function getIterator() {
        foreach ($this->slots as $slot) {
            yield $slot;
        }
    }

    public function count() {
        return count($this->slots);
    }

    public static function modified_timestamp(int $timestamp, string $modifier) {
        // convert to DateTime object
        $timestamp = new \DateTime('@' . $timestamp);
        // apply the modifier to the slot
        $timestamp->modify($modifier);
        // convert back to timestamp
        return $timestamp->getTimestamp();
    }

    public function modify_starttimes($modifier) {
        foreach ($this->slots as &$slot) {
            $slot->timestart = $this::modified_timestamp($slot->timestart, $modifier);
        }
        unset($slot);
    }

    public function save_as_new() {
        foreach ($this->slots as $slot) {
            $clone = new slot();
            $clone->clone_slot($slot);
            $clone->save();
        }
    }
}