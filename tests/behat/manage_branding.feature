@tool_eledia_mailtemplates
Feature: Configure email branding
  As an administrator
  I can configure branding settings for outgoing emails
  So that all notifications have a consistent visual identity

  Background:
    Given I log in as "admin"

  Scenario: Access the branding page
    When I navigate to "Plugins > Admin tools > eLeDia Mail Templates" in site administration
    And I click on "Manage branding" "link"
    Then I should see "Branding"
    And I should see "Primary colour"
    And I should see "Footer content"

  Scenario: Save branding settings
    When I navigate to "Plugins > Admin tools > eLeDia Mail Templates" in site administration
    And I click on "Manage branding" "link"
    And I set the following fields to these values:
      | Primary colour | #336699 |
    And I press "Save changes"
    Then I should see "Branding settings saved."

  Scenario: Cancel branding returns to template list
    When I navigate to "Plugins > Admin tools > eLeDia Mail Templates" in site administration
    And I click on "Manage branding" "link"
    And I press "Cancel"
    Then I should see "eLeDia Mail Templates"
    And I should see "Create template"
