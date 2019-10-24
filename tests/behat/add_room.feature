@mod @mod_room @street_college
Feature: Add rooms to be managed
  In order to manage use of physical spaces
  As a manager of my institution
  I want to add rooms to the room management database
    
  Background: 
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | student1 | Student   | One      | student1@e.mail   |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Room Plan" to section "1" and I fill the form with:
      | Display name | Room Plan |
    
  Scenario: Add a room to the Room Management plugin
    Given I follow "Room Plan"
    And I navigate to "New room" in current page administration
    And I set the following fields to these values:
      | Room name | The Room |
    And I press "Add room"
    Then I should see "The Room"