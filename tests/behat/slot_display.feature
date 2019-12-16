@mod @mod_room @mod_room_slot_display @street_college
Feature: Slot display
  In order to comprehend use of physical spaces
  As an organiser
  I need to see a chart of space organised by time and room

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | teacher1 | Teacher   | One      | teacher1@e.mail   |
      | student1 | Student   | One      | student1@e.mail   |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | name      | course | idnumber |
      | room     | Room Plan | C1     | roomplan |
    And the following rooms are defined in the room module:
      | roomname |
      | The Room |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"

  Scenario: Display start and end time of slot
    Given I set the following fields to these values:
      | Slot title | Lengthy event |
      | Room | The Room |
      | starttime[day] | 2 |
      | starttime[month] | January |
      | starttime[year] | 2020 |
      | starttime[hour] | 10 |
      | starttime[minute] | 30 |
      | duration[minutes] | 65 |
    When I press "Add slot"
    Then I should see "2 January 2020, 10:30 AM » 11:35 AM"

  Scenario: Display start and end date of multi-day events
    Given I set the following fields to these values:
      | Slot title | Ongoin |
      | Room | The Room |
      | starttime[day] | 31 |
      | starttime[month] | January |
      | starttime[year] | 2021 |
      | starttime[hour] | 23 |
      | starttime[minute] | 00 |
      | duration[minutes] | 180 |
    When I press "Add slot"
    Then I should see "Sunday, 31 January 2021, 11:00 PM » Monday, 1 February 2021, 2:00 AM"

  Scenario: No end time for slot without duration
    Given I set the following fields to these values:
      | Slot title | Short |
      | Room | The Room |
      | starttime[day] | 10 |
      | starttime[month] | April |
      | starttime[year] | 2021 |
      | starttime[hour] | 12 |
      | starttime[minute] | 00 |
    When I press "Add slot"
    Then I should see "10 April 2021, 12:00 PM"
    And I should not see "»"

  Scenario: See booked slots in the room plan
    Given I set the following fields to these values:
      | Slot title | Nice event |
      | Room | The Room |
      | starttime[day] | 30 |
      | starttime[month] | October |
      | starttime[year] | 2019 |
      | starttime[hour] | 12 |
      | starttime[minute] | 30 |
    And I press "Add slot"
    And I set the following fields to these values:
      | displaydate[day] | 30 |
      | displaydate[month] | October |
      | displaydate[year] | 2019 |
    When I press "Display"
    Then I should see "Nice event"
    And I should see "The Room"
    And I should see "30 October 2019, 12:30"
    When I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I set the following fields to these values:
      | displaydate[day] | 30 |
      | displaydate[month] | October |
      | displaydate[year] | 2019 |
    And I press "Display"
    Then I should see "Nice event"
    And I should see "The Room"
    And I should see "30 October 2019, 12:30"
