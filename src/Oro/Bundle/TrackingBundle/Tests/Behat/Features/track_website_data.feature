Feature: Track website data
  In order to have ability to collect website tracking data
  As an Administrator
  I need create tracking website and configure dynamic tracking in configuration

  Scenario: Create different window session
    Given sessions active:
      | Admin | first_session  |
      | User  | second_session |

  Scenario: Add website tracking
    Given I reset tracking settings file
    And I proceed as the Admin
    And login as administrator
    And go to Marketing/ Tracking Websites
    And I click "Create Tracking Website"
    And I fill form with:
      | Owner      | John Doe               |
      | Name       | Default                |
      | Identifier | default                |
      | Url        | http://www.example.com |
    When I save and close form
    Then I should see "Tracking Website saved" flash message

  #Todo: replace after #BAP-15242 done, merge scenarios
  Scenario: Collect tracking data
    Given I proceed as the User
    And I generate html page with tracking code from website "default"
    When I open html page with tracking code for website "default"
    And I proceed as the Admin

  Scenario: Enable dynamic tracking
    Given go to System/ Configuration
    And I follow "System Configuration/General Setup/Tracking" on configuration sidebar
    And uncheck "Use default" for "Enable dynamic tracking" field
    And I check "Enable dynamic tracking"
    When I save form
    Then the "Enable dynamic tracking" checkbox should be checked

  #Todo: replace after #BAP-15242 done, merge scenarios
  Scenario: Collect tracking data with dynamic tracking
    Given I proceed as the User
    When I open html page with tracking code for website "default"
    And I proceed as the Admin

  Scenario: Check events added as an Admin
    And go to Marketing/ Tracking Websites
    And I should see "Default"
    And I click view Default in grid
    Then there is one record in grid
