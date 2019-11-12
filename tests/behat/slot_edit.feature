@mod @mod_room @mod_room_slot_edit @street_college
Feature: Edit slots
  In order to correct mistakes or adapt to changing reality
  As an organiser
  I need to be able edit previously input slots

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
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Room Plan" to section "1" and I fill the form with:
      | Display name | Room Plan |
    And I follow "Room Plan"
    And I navigate to "New room" in current page administration
    And I set the following fields to these values:
      | Room name | The Room |
    And I press "Add room"
    And I navigate to "New room" in current page administration
    And I set the following fields to these values:
      | Room name | Main Hall |
    And I press "Add room"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Wrong event |
      | Room | The Room |
      | starttime[day] | 1 |
      | starttime[month] | December |
      | starttime[year] | 2019 |
      | starttime[hour] | 12 |
      | starttime[minute] | 30 |
    And I press "Add slot"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Correct event |
      | Room | The Room |
      | starttime[day] | 1 |
      | starttime[month] | December |
      | starttime[year] | 2019 |
      | starttime[hour] | 12 |
      | starttime[minute] | 30 |
    And I press "Add slot"

  Scenario: Edit all slot fields
    Given I set the following fields to these values:
      | displaydate[day] | 1 |
      | displaydate[month] | December |
      | displaydate[year] | 2019 |
    And I press "Display"
    When I click on "[data-event-title=\"Wrong event\"] [data-action=\"edit\"]" "css_element"
    And I set the following fields to these values:
      | Slot title | Gonna happen |
      | Room | Main Hall |
      | starttime[day] | 2 |
      | starttime[month] | January |
      | starttime[year] | 2020 |
      | starttime[hour] | 12 |
      | starttime[minute] | 45 |
    And I press "Update slot"
    And I set the following fields to these values:
      | displaydate[day] | 1 |
      | displaydate[month] | December |
      | displaydate[year] | 2019 |
    And I press "Display"
    Then I should not see "Wrong event"
    And I should not see "Gonna happen"
    When I set the following fields to these values:
      | displaydate[day] | 2 |
      | displaydate[month] | January |
      | displaydate[year] | 2020 |
    And I press "Display"
    Then I should see "Gonna happen"
    And I should see "Main Hall"
    And I should see "2 January 2020, 12:45 PM"

  Scenario: Edit individual slot fields and return to date of updated slot
    Given I set the following fields to these values:
      | displaydate[day] | 1 |
      | displaydate[month] | December |
      | displaydate[year] | 2019 |
    And I press "Display"
    When I click on "[data-event-title=\"Wrong event\"] [data-action=\"edit\"]" "css_element"
    And I set the following fields to these values:
      | Slot title | Amazing slot |
    And I press "Update slot"
    Then I should see "Amazing slot"
    And I should see "1 December 2019, 12:30 PM"
    And I should not see "Wrong event"
    And I should see "Correct event"
    When I click on "[data-event-title=\"Amazing slot\"] [data-action=\"edit\"]" "css_element"
    And I set the following fields to these values:
      | starttime[minute] | 15 |
    And I press "Update slot"
    Then I should see "1 December 2019, 12:15 PM" in the "[data-event-title=\"Amazing slot\"]" "css_element"
    When I click on "[data-event-title=\"Amazing slot\"] [data-action=\"edit\"]" "css_element"
    And I set the following fields to these values:
      | starttime[month] | February |
      | starttime[year] | 2021 |
    And I press "Update slot"
    Then I should not see "Correct event"
    And I should see "Amazing slot"
    And I should see "1 February 2021, 12:15 PM"
    When I set the following fields to these values:
      | displaydate[day] | 1 |
      | displaydate[month] | December |
      | displaydate[year] | 2019 |
    And I press "Display"
    Then I should see "Correct event"
    And I should not see "Wrong event"
    And I should not see "Amazing slot"
    When I click on "[data-event-title=\"Correct event\"] [data-action=\"edit\"]" "css_element"
    And I set the following fields to these values:
      | Room | Main Hall |
    And I press "Update slot"
    Then I should see "Correct event"
    And I should see "Main Hall"
    And I should see "1 December 2019, 12:30 PM"
    And I should not see "The Room"
