Feature: System entities should be available as ML targets
  In order to manage marketing lists
  As administrator
  I need to be able to create marketing list based on system entities

  Scenario Outline: Successful creating marketing list based on system entities
    Given I login as administrator
    And I go to Marketing/ Marketing Lists
    And I press "Create Marketing List"
    When I fill form with:
      | Name   | <Marketing List Name> |
      | Entity | <Entity Name>         |
      | Type   | Dynamic               |
    Then I should see that "Available contact information fields" contains "Contact Information"
    When I add the following columns:
      | Contact Information |
    And I save and close form
    Then I should see "Marketing list saved" flash message

    Examples:
      | Marketing List Name            | Entity Name   |
      | Marketin list by Account       | Account       |
      | Marketin list by Order         | Order         |
      | Marketin list by Quote         | Quote         |
      | Marketin list by Shopping List | Shopping List |
      | Marketin list by User          | User          |
