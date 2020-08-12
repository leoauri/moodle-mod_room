@mod @mod_room @mod_room_slot_display_filter @street_college @javascript
Feature: Filter displayed slots
  In order to quickly find slots which interest me
  As a student
  I need to be able to filter the displayed slots

  Scenario: Filter displayed slots
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "activities" exist:
      | activity | name      | course | idnumber | type     |
      | room     | Room Plan | C1     | roomplan | upcoming |
    And the following rooms are defined in the room module:
      | roomname |
      | Tower    |
    And the following "users" exist:
      | username | firstname | lastname | email          |
      | student  | student   | student  | student@e.mail |
    And the following "course enrolments" exist:
      | user    | course | role    |
      | student | C1     | student |
    And the following slots are defined in the room module:
      | roomplan  | slottitle                        | room  | starttime        | duration | spots | context |
      | Room Plan | Mixing Grundlagen                | Tower | 2030-07-16 10:00 | 2:00     | 1     |         |
      | Room Plan | Mixing Advanced mit Marc         | Tower | 2030-07-16 12:00 | 2:00     | 1     |         |
      | Room Plan | Frei                             | Tower | 2030-07-17 12:00 | 1:00     | 8     |         |
      | Room Plan | Frei mit Marc                    | Tower | 2030-07-17 15:30 | 1:00     | 8     |         |
      | Room Plan | Frei mit Luise                   | Tower | 2030-07-17 16:30 | 1:00     | 8     |         |
      | Room Plan | Signalfluss Grundlagen mit Luise | Tower | 2030-07-17 16:30 | 1:00     | 8     |         |
      | Room Plan | Signalfluss Advanced             | Tower | 2030-07-17 16:30 | 1:00     | 8     |         |
    And I log in as "admin"
    And I am on "Course 1" course homepage
    When I follow "Room Plan"
    Then I should not see "Filter slots"
    And "#mod-room-slot-filters" "css_element" should not exist
    When I navigate to "Edit settings" in current page administration
    And I set the following fields to these values:
      | Filters | mixing,Grundlagen,advanced,Marc,frei,Luise,Signalfluss,nonlabel |
    And I press "Save and display"
    Then I should see "Filter slots" in the "#mod-room-slot-filters" "css_element"
    And I should see "mixing" in the "#mod-room-slot-filters" "css_element"
    And I should see "Grundlagen" in the "#mod-room-slot-filters" "css_element"
    And I should see "advanced" in the "#mod-room-slot-filters" "css_element"
    And I should see "Marc" in the "#mod-room-slot-filters" "css_element"
    And I should see "frei" in the "#mod-room-slot-filters" "css_element"
    And I should see "Luise" in the "#mod-room-slot-filters" "css_element"
    And I should see "Signalfluss" in the "#mod-room-slot-filters" "css_element"
    # TODO: only tokens which are present in current spots are made into filters
    # And I should not see "Nonlabel" in the "#mod-room-slot-filters" "css_element"
    # And I should not see "nonlabel" in the "#mod-room-slot-filters" "css_element"
    When I click on "//div[@id='mod-room-slot-filters']//span[text()='mixing']" "xpath_element"
    Then I should see "Mixing Grundlagen" in the "#mod-room-room-plan" "css_element"
    And I should see "Mixing Advanced mit Marc" in the "#mod-room-room-plan" "css_element"
    And I should not see "Frei" in the "#mod-room-room-plan" "css_element"
    And I should not see "Luise" in the "#mod-room-room-plan" "css_element"
    And I should not see "Signalfluss" in the "#mod-room-room-plan" "css_element"
    When I click on "//div[@id='mod-room-slot-filters']//span[text()='Grundlagen']" "xpath_element"
    Then I should see "Mixing Grundlagen" in the "#mod-room-room-plan" "css_element"
    And I should see "Signalfluss Grundlagen mit Luise" in the "#mod-room-room-plan" "css_element"
    And I should not see "Advanced" in the "#mod-room-room-plan" "css_element"
    And I should not see "Frei" in the "#mod-room-room-plan" "css_element"
    And I should not see "Marc" in the "#mod-room-room-plan" "css_element"
    When I click on "//div[@id='mod-room-slot-filters']//span[text()='frei']" "xpath_element"
    Then I should see "Frei" in the "#mod-room-room-plan" "css_element"
    And I should see "Frei mit Luise" in the "#mod-room-room-plan" "css_element"
    And I should not see "Advanced" in the "#mod-room-room-plan" "css_element"
    And I should not see "Mixing" in the "#mod-room-room-plan" "css_element"
    And I should not see "Signalfluss" in the "#mod-room-room-plan" "css_element"
    When I click on "//div[@id='mod-room-slot-filters']//span[text()='frei']" "xpath_element"
    Then I should see "Frei" in the "#mod-room-room-plan" "css_element"
    And I should see "Frei mit Luise" in the "#mod-room-room-plan" "css_element"
    And I should see "Advanced" in the "#mod-room-room-plan" "css_element"
    And I should see "Mixing" in the "#mod-room-room-plan" "css_element"
    And I should see "Signalfluss" in the "#mod-room-room-plan" "css_element"

    # Check edit filters is not available to student