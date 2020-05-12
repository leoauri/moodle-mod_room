@mod @mod_room @mod_room_duplicate_consecutively @street_college
Feature: Consecutive duplication
  In order to quickly set up slots
  As a teacher
  I need to be able to create multiple, consecutive copies of a slot

  @javascript
  Scenario: Duplicate a slot consecutively
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
    And the following slots are defined in the room module:
      | roomplan  | slottitle    | room     | starttime        | duration | spots |
      | Room Plan | singing slot | The Room | 2020-07-31 23:00 | 0:30     | 1     |
    And I log in as "teacher1"
    And I view "roomplan" room module for date "2020-07-31"
    And I click on "[data-event-title=\"singing slot\"] [data-action=\"edit\"]" "css_element"
    When I press "Slot duplication"
    Then I should see "singing slot"
    And I should see "31 July 2020, 11:00 PM » 11:30 PM"
    And I should see "The Room"
    When I set the following fields to these values:
      | Duplication mode     | Consecutive |
      | Number of duplicates | 5           |
    Then I should see "Slots to be created:"
    And I should see "11:30 PM" in the "#mod-room-duplication-preview" "css_element"
    And I should see "31 July 2020, 11:30 PM" in the "#mod-room-duplication-preview" "css_element"
    And I should see "1 August 2020, 12:00 AM" in the "#mod-room-duplication-preview" "css_element"
    And I should see "1 August 2020, 12:30 AM » 1:00 AM" in the "#mod-room-duplication-preview" "css_element"
    And I should see "1 August 2020, 1:00 AM » 1:30 AM" in the "#mod-room-duplication-preview" "css_element"
    And I should see "1 August 2020, 1:30 AM » 2:00 AM" in the "#mod-room-duplication-preview" "css_element"
    When I press "Duplicate slots"
    # When I click on "#id_submitbutton" "css_element"
    # And I log out
    # And I log in as "admin"
    # And I view "roomplan" room module for date "2020-07-31"
    Then I should see "31 July 2020, 11:00 PM » 11:30 PM"
    And I should see "31 July 2020, 11:30 PM » Saturday, 1 August 2020, 12:00 AM"
    When I view "roomplan" room module for date "2020-08-01"
    Then I should see "1 August 2020, 12:00 AM » 12:30 AM"
    And I should see "1 August 2020, 12:30 AM » 1:00 AM"
    And I should see "1 August 2020, 1:00 AM » 1:30 AM"
