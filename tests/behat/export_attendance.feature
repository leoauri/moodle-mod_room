@mod @mod_room @mod_room_export_attendance @street_college
Feature: Export attendance
  As a manager
  I need an export of attendance data
  In order to manage

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email           |
      | student1 | Student   | One      | student1@e.mail |
      | student2 | Student   | Two      | student2@e.mail |
      | student3 | Student   | Three    | student3@e.mail |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
      | Course 2 | C2        | topics |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student2 | C1     | student |
      | student2 | C2     | student |
      | student3 | C2     | student |
    And the following "activities" exist:
      | activity | name      | course | idnumber | type     |
      | room     | Room Plan | C1     | roomplan | upcoming |
      | room     | Doom Plan | C2     | roomplan | upcoming |
    And the following rooms are defined in the room module:
      | roomname |
      | A Room   |
      | Big      |
    And the following slots are defined in the room module:
      | roomplan  | slottitle | room   | starttime        | duration | spots |
      | Room Plan | upcoming1 | Big    | 2030-01-01 16:00 |          | 4     |
      | Room Plan | upcoming2 | Big    | 2030-01-02 16:00 | 1:00     | 4     |
      | Doom Plan | upcoming3 | Big    | 2035-01-01 11:00 | 2:30     | 4     |
      | Doom Plan | upcoming4 | A Room | 2036-01-01 16:00 |          | 4     |
      | Doom Plan | upcoming5 | A Room | 2036-07-01 16:00 |          | 4     |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I click on "[data-event-title=\"upcoming1\"] [data-action=\"book-spot-button\"]" "css_element"
    # And I don't book upcoming2
    And I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I click on "[data-event-title=\"upcoming1\"] [data-action=\"book-spot-button\"]" "css_element"
    And I click on "[data-event-title=\"upcoming2\"] [data-action=\"book-spot-button\"]" "css_element"
    And I am on "Course 2" course homepage
    And I follow "Doom Plan"
    And I click on "[data-event-title=\"upcoming3\"] [data-action=\"book-spot-button\"]" "css_element"
    And I log out
    And I log in as "student3"
    And I am on "Course 2" course homepage
    And I follow "Doom Plan"
    And I click on "[data-event-title=\"upcoming3\"] [data-action=\"book-spot-button\"]" "css_element"
    And I click on "[data-event-title=\"upcoming4\"] [data-action=\"book-spot-button\"]" "css_element"
    And I click on "[data-event-title=\"upcoming5\"] [data-action=\"book-spot-button\"]" "css_element"
    And I log out

  Scenario: Export attendance data
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    And I navigate to "Export attendance records" in current page administration
    Then I should see "datetime,slot,participant"
    And I should see "\"2030-01-01 16:00\",upcoming1,\"Student One\""
    And I should not see "\"2030-01-02 16:00\",upcoming2,\"Student One\""
    And I should see "\"2030-01-01 16:00\",upcoming1,\"Student Two\""
    And I should see "\"2030-01-02 16:00\",upcoming2,\"Student Two\""
    And I should see "\"2035-01-01 11:00\",upcoming3,\"Student Two\""
    And I should see "\"2035-01-01 11:00\",upcoming3,\"Student Three\""
    And I should see "\"2036-01-01 16:00\",upcoming4,\"Student Three\""
    And I should see "\"2036-07-01 16:00\",upcoming5,\"Student Three\""

  Scenario: Other user cannot export
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Room Plan"
    Then "//a[text()='Export attendance records']" "xpath_element" should not exist
