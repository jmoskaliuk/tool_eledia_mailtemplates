@tool_eledia_mailtemplates
Feature: Manage email templates
  As an administrator
  I can create, edit and delete email templates
  So that outgoing Moodle notifications use my custom content

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | admin1   | Admin     | User     | admin1@example.com|
    And I log in as "admin"

  Scenario: View the template list page
    When I navigate to "Plugins > Admin tools > eLeDia Mail Templates" in site administration
    Then I should see "eLeDia Mail Templates"
    And I should see "Create template"
    And I should see "No templates defined yet."

  Scenario: Create a new template
    When I navigate to "Plugins > Admin tools > eLeDia Mail Templates" in site administration
    And I click on "Create template" "link"
    Then I should see "Create template"
    And I set the following fields to these values:
      | Notification type  | Password reset                                |
      | Subject            | Reset your password on {{site_name}}          |
      | Active             | 1                                             |
    And I set the field "Message body (HTML)" to "<p>Hello {{recipient_firstname}}, click here: {{reset_url}}</p>"
    And I press "Save changes"
    Then I should see "Template saved successfully."
    And I should see "Password reset"
    And I should see "Reset your password on {{site_name}}"

  Scenario: Edit an existing template
    Given the following "tool_eledia_mailtemplates > templates" exist:
      | notification_type | subject                | body_html                        | active |
      | password_reset    | Reset your password    | <p>Click {{reset_url}}</p>       | 1      |
    When I navigate to "Plugins > Admin tools > eLeDia Mail Templates" in site administration
    Then I should see "Reset your password"
    When I click on "Edit" "link" in the "Reset your password" "table_row"
    And I set the field "Subject" to "Password reset for {{site_name}}"
    And I press "Save changes"
    Then I should see "Template saved successfully."
    And I should see "Password reset for {{site_name}}"

  Scenario: Delete a template
    Given the following "tool_eledia_mailtemplates > templates" exist:
      | notification_type   | subject              | body_html                    | active |
      | course_enrolment    | Welcome to the course| <p>You are enrolled.</p>     | 1      |
    When I navigate to "Plugins > Admin tools > eLeDia Mail Templates" in site administration
    Then I should see "Welcome to the course"
    When I click on "Delete" "link" in the "Welcome to the course" "table_row"
    Then I should see "Template deleted."
    And I should see "No templates defined yet."

  Scenario: Non-admin cannot access template management
    Given I log out
    And the following "users" exist:
      | username | firstname | lastname | email              |
      | teacher1 | Teacher   | One      | teacher1@example.com|
    When I log in as "teacher1"
    And I am on site homepage
    Then "Plugins" "link" should not exist in current page administration
