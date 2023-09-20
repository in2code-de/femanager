# Features/Invitation/InvitationSmall.feature
@Invitation @InvitationSmall
Feature: InvitationSmall
  Scenario: Check if invitation works
    Given I am on "/index.php?id=51"
    Then I should see "Invite a new user"
    Then I should not see "You tried to open a restricted page"
    And I fill in the following:
      | Username | [random] |
      | Email | alex@einpraegsam.net |
    And I press "Create Profile Now"
    Then I wait "4" seconds
    Then I should see "Profile successfully created and user informed"

    Given I am on "/index.php?id=140"
    Then I should see "[random:1]"

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
