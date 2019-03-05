@ticket-CRM-8968
@fixture-OroCampaignBundle:CampaignFixture.yml
@fixture-OroContactBundle:LoadContactEntitiesFixture.yml
@fixture-OroEmailBundle:templates.yml

Feature: Campaign sending
  In order to send email campaign
  As an Administrator
  I should be able to send emails to all recipients from email campaign

  Scenario: Send email campaign
    Given I login as administrator
    And I load Marketing List fixture
    And I should see Marketing/Email Campaigns in main menu
    And I go to Marketing/Email Campaigns
    Then I click "Create Email Campaign"
    And fill form with:
      | Name           | Test email campaign          |
      | Marketing List | Contact Email Marketing List |
    Then should see the following options for "Template" select:
      | test_template |
    And should not see the following options for "Template" select:
      | not_system_email_1 |
      | not_system_email_2 |
      | not_system_email_3 |
      | system_email       |
      | non_entity_related |
    Then I fill form with:
      | Campaign | Campaign 1    |
      | Template | test_template |
    And I save and close form
    Then I should see "Email campaign saved" flash message
    And I should see following grid:
      | Contact Email  |
      | test1@test.com |
      | test2@test.com |
    And number of records should be 2

    When I click "Send"
    Then I should see "Email campaign was sent" flash message
    And I should see following grid:
      | Contact Email  |
      | test1@test.com |
      | test2@test.com |
    And number of records should be 2
