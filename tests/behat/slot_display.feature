@mod @mod_room @mod_room_slot_display @street_college
Feature: Slot display
  In order to comprehend use of physical spaces
  As an organiser
  I need to see a chart of space organised by time and room

  Scenario: See booked slots in the room plan
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
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Room Plan" to section "1" and I fill the form with:
      | Display name | Room Plan |
    And I follow "Room Plan"
    And I navigate to "New room" in current page administration
    And I set the following fields to these values:
      | Room name | The Room |
    And I press "Add room"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
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
