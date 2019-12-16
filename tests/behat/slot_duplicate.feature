@mod @mod_room @mod_room_slot_duplicate @street_college
Feature: Duplicate slots
  In order to enable rapid ongoing organisation
  As an organiser
  I need to be able to duplicate existing slots

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | teacher1 | Teacher   | One      | teacher1@e.mail   |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | name      | course | idnumber |
      | room     | Room Plan | C1     | roomplan |
    And the following rooms are defined in the room module:
      | roomname |
      | The Room |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Weekly event |
      | Room | The Room |
      | starttime[day] | 1 |
      | starttime[month] | December |
      | starttime[year] | 2019 |
      | starttime[hour] | 12 |
      | starttime[minute] | 30 |
      | spots | 7 |
    And I press "Add slot"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Also weekly |
      | Room | The Room |
      | starttime[day] | 2 |
      | starttime[month] | December |
      | starttime[year] | 2019 |
      | starttime[hour] | 11 |
      | starttime[minute] | 30 |
      | duration[hours] | 3 |
      | duration[minutes] | 45 |
    And I press "Add slot"

  Scenario: Duplicate slots
    When I navigate to "Slot duplication tool" in current page administration
    And I set the following fields to these values:
      | startdate[day] | 1 |
      | startdate[month] | December |
      | startdate[year] | 2019 |
      | enddate[day] | 2 |
      | enddate[month] | December |
      | enddate[year] | 2019 |
    And I press "Duplicate"
    Then I should see "Weekly event"
    And I should see "8 December 2019, 12:30 PM"
    And I should see "Free spots: 7" in the "[data-event-title=\"Weekly event\"]" "css_element"
    When I set the following fields to these values:
      | displaydate[day] | 9 |
      | displaydate[month] | December |
      | displaydate[year] | 2019 |
    And I press "Display"
    Then I should see "Also weekly"
    And I should see "9 December 2019, 11:30 AM"
    And I should see "» 3:15 PM"

  Scenario: Duplicate selected slots
    When I navigate to "Slot duplication tool" in current page administration
    And I set the following fields to these values:
      | startdate[day] | 1 |
      | startdate[month] | December |
      | startdate[year] | 2019 |
      | enddate[day] | 1 |
      | enddate[month] | December |
      | enddate[year] | 2019 |
    And I press "Duplicate"
    Then I should see "Weekly event"
    And I should see "8 December 2019, 12:30 PM"
    And I should see "Free spots: 7" in the "[data-event-title=\"Weekly event\"]" "css_element"
    When I set the following fields to these values:
      | displaydate[day] | 9 |
      | displaydate[month] | December |
      | displaydate[year] | 2019 |
    And I press "Display"
    Then I should not see "Also weekly"
    And I should not see "9 December 2019, 11:30 AM"
    And I should not see "» 3:15 PM"

  Scenario: Duplicate other slots
    When I navigate to "Slot duplication tool" in current page administration
    And I set the following fields to these values:
      | startdate[day] | 2 |
      | startdate[month] | December |
      | startdate[year] | 2019 |
      | enddate[day] | 2 |
      | enddate[month] | December |
      | enddate[year] | 2019 |
    And I press "Duplicate"
    Then I should not see "Weekly event"
    And I should not see "8 December 2019, 12:30 PM"
    And I should see "Also weekly"
    And I should see "9 December 2019, 11:30 AM"
    And I should see "» 3:15 PM"
