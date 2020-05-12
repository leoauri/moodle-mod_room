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
 * Room module visual plan class.
 *
 * @package     mod_room
 * @copyright   2019 Leo Auri <code@leoauri.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_room\output;

use DateTimeImmutable;

defined('MOODLE_INTERNAL') || die();

class visual_plan extends plan_base {
    private function retrieve_events() {
        $options = ['start' => $this->date];
        $options['end'] = \mod_room\helper\date::one_day_later($this->date);

        $this->events = \mod_room\entity\slot_collection::retrieve($options);
        $this->events->prepare_display($this->modulecontext);
    }

    public static function hours_of_day() {
        $output = [];
        $dt = new \DateTime();
        for ($hour = 0; $hour < 24; $hour++) {
            $dt->setTime($hour, 0);
            array_push($output, $dt->format('ga'));
        }
        return $output;
    }

    public static function add_vertical_pos_attrs(&$slots) {
        foreach ($slots as &$slot) {
            // This grid height value is set here and in styles.scss!!
            // TODO: Find a way of defining one constant
            $gridheight = 40;
            
            $starttime = new \DateTime("@{$slot->timestart}");
            $starttime->setTimezone(new \DateTimeZone(\core_date::get_user_timezone()));
            $fractionalhour = (float)$starttime->format('G') + (float)$starttime->format('i') / 60;
            $slot->visualPlanTop = $fractionalhour * $gridheight;

            $fractionalduration = $slot->timeduration ? $slot->timeduration / (60 * 60) : 1;
            $slot->visualPlanHeight = $fractionalduration * $gridheight;
        }
        unset($slot);
    }

    public function export_for_template(\renderer_base $output) {
        $this->retrieve_events();
        $templatedata = new \stdClass();

        $locations = $this->events->get_used_locations();

        $templatedata->slots_by_location = [];

        foreach ($locations as $location) {
            $d = new \stdClass();
            $d->location = $location;
            $d->slots = $this->events->slots_for_location($location);
            self::add_vertical_pos_attrs($d->slots);
            array_push($templatedata->slots_by_location, $d);
        }

        $templatedata->hours_of_day = self::hours_of_day();

        return $templatedata;
    }
}