# Features/Invitation/InvitationOnlyGroup1.feature
@Invitation @InvitationOnlyGroup1
Feature: InvitationOnlyGroup1
  Scenario: Check if anonymous can invite
    Given I am on "/index.php?id=139"
    Then I should see "You tried to open a restricted page"
    Then I should not see "Invite a new user"

  Scenario: Check if user in Group1 can invite
    Given I am on "/index.php?id=2"
    Then I should see "You are not yet logged in as frontend user"
    Then I fill in "Username" with "usercaninvite"
    Then I fill in "Password" with "in2code"
    Then I press "Login"
    Then I wait "4" seconds
    Then I should see "You are logged in as frontend user"

    Given I am on "/index.php?id=139"
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

  Scenario: Check if user in Group2 can NOT invite
    Given I am on "/index.php?id=2"
    Then I should see "You are not yet logged in as frontend user"
    Then I fill in "Username" with "usercannotinvite"
    Then I fill in "Password" with "in2code"
    Then I press "Login"
    Then I wait "4" seconds
    Then I should see "You are logged in as frontend user"

    Given I am on "/index.php?id=139"
    Then I should see "You tried to open a restricted page"
    Then I should not see "Invite a new user"

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
