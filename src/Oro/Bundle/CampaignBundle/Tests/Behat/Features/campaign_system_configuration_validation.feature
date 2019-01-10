@ticket-BAP-18094

Feature: Campaign system configuration validation
  In order to configure Campaign feature
  As an Administrator
  I should be able to enter only valid data in system configuration

  Scenario: Check Campaign settings validation
    Given I login as administrator
    And I go to System/ Configuration
    And I follow "System Configuration/General Setup/Email Configuration" on configuration sidebar
    And uncheck "Use default" for "Sender Email" field in section "Campaign"
    And I type "" in "Sender Email" from "Campaign Section"
    And I click on empty space
    Then I should see "This value should not be blank"

    When I type "aaa" in "Sender Email" from "Campaign Section"
    And I click on empty space
    Then I should not see "This value should not be blank"
    And I should see "This value is not a valid email address"

    When I type "test@test.com" in "Sender Email" from "Campaign Section"
    And I click on empty space
    Then I should not see "This value is not a valid email address"

    When uncheck "Use default" for "Sender Name" field in section "Campaign"
    And I type "" in "Sender Name" from "Campaign Section"
    And I click on empty space
    Then I should see "This value should not be blank"

    When I type "TEST_NAME" in "Sender Name" from "Campaign Section"
    And I click on empty space
    Then I should not see "This value should not be blank"
