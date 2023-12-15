@regression
@ticket-BB-23317

Feature: Create marketing list and check multi enum filters

  Scenario: Create Multi-Select field
    Given I login as administrator
    And go to System/Entities/Entity Management
    And filter Name as is equal to "Customer"
    And click view Customer in grid
    When I click on "Create Field"
    And fill form with:
      | Field Name | MultiSelectField |
      | Type       | Multi-Select     |
    And click "Continue"
    And set Options with:
      | Label |
      | 1     |
      | 2     |
      | 3     |
    And save and close form
    Then I should see "Field saved" flash message

  Scenario: Create Customer relation
    Given I go to System/Entities/Entity Management
    And filter Name as is equal to "CustomerUser"
    And click view CustomerUser in grid
    When I click "Create field"
    And fill form with:
      | Field name   | ManyToOneRelation |
      | Storage Type | Table column      |
      | Type         | Many to one       |
    And click "Continue"
    And fill form with:
      | Target Entity | Customer |
      | Target Field  | Id       |
    And save and close form
    Then I should see "Field saved" flash message
    When I click update schema
    Then I should see "Schema updated" flash message

  Scenario: Create Marketing List with multi select filter
    Given I go to Marketing/Marketing Lists
    When I click "Create Marketing List"
    And fill form with:
      | Name   | Marketing list |
      | Entity | Customer User  |
      | Type   | Dynamic        |
    And add the following columns:
      | Contact Information |
    And add the following filters:
      | Field Condition | ManyToOneRelation > MultiSelectField | is any of | 1 |
    And save and close form
    Then I should see "Marketing list saved" flash message
