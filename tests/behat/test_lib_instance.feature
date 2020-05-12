@mod @mod_room @mod_room_test_test @street_college
Feature: Create instances of types
  In order to quickly test types of room module
  As a developer
  I need the API to support room module types

  Scenario: Create master type room module instance
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "activities" exist:
      | activity | name      | course | idnumber | type   |
      | room     | Room Plan | C1     | roomplan | master |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    When I follow "Room Plan"
    Then I should see "Master room plan"
