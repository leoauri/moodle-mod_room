@mod @mod_room @mod_room_spot_book @street_college
Feature: Book slots
  In order to participate in activities
  As a student
  I need to be able to book a spot in slots

  Scenario: Book a spot
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
      | starttime[day] | 6 |
      | starttime[month] | November |
      | starttime[year] | 2019 |
      | starttime[hour] | 11 |
      | starttime[minute] | 30 |
      | Spots | 1 |
    And I press "Add slot"
    And I follow "Add slot"
    And I set the following fields to these values:
      | Slot title | Second event |
      | Room | The Room |
      | starttime[day] | 6 |
      | starttime[month] | November |
      | starttime[year] | 2019 |
      | starttime[hour] | 12 |
      | starttime[minute] | 30 |
      | Spots | 2 |
    And I press "Add slot"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I follow "Room Plan"
    And I set the following fields to these values:
      | displaydate[day] | 6 |
      | displaydate[month] | November |
      | displaydate[year] | 2019 |
    And I press "Display"
    Then I should see "Free spots: 1" in the "[data-event-title=\"Nice event\"]" "css_element"
    And I should see "Free spots: 2" in the "[data-event-title=\"Second event\"]" "css_element"
    When I click on "[data-event-title=\"Second event\"] [data-action=\"book-spot-button\"]" "css_element"
    Then I should see "Free spots: 1" in the "[data-event-title=\"Nice event\"]" "css_element"
    And I should see "Free spots: 1" in the "[data-event-title=\"Second event\"]" "css_element"
    And I should not see "Book spot" in the "[data-event-title=\"Second event\"]" "css_element"
    And I should see "Booked by: Student One" in the "[data-event-title=\"Second event\"]" "css_element"
    When I click on "[data-event-title=\"Nice event\"] [data-action=\"book-spot-button\"]" "css_element"
    Then I should not see "Free spots" in the "[data-event-title=\"Nice event\"]" "css_element"
    And I should see "Booked by: Student One" in the "[data-event-title=\"Nice event\"]" "css_element"

    # Test for student cannot book slot from non-enrolled course
    