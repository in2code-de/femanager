# Features/Registration/Default/SmallUserConfirm.feature
@Registration @RegistrationDefault @RegistrationDefaultSmallUserConfirmForm
Feature: SmallUserConfirmForm

  Scenario: Check if a small registration is possible with user confirmation and confirmation form
    Given I am on "/index.php?id=144"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Username | [random] |
      | Password | test |
      | Repeat Password | test |
      | Email | alex@einpraegsam.net |
    And I press "Create Profile Now"
    Then I wait "4" seconds

    Then I should see "Thank you for your request. Please check your mail account to confirm the profile."

    # Check if user is disabled
    Given I am on "/index.php?id=48&pid=144"
    Then I should see "[random:1]"
    Then I should see "status: disabled"

    # Click user confirmation link
    Then I follow "User confirmation link"
    Then I wait "4" seconds
    Then I should see "Please confirm the creation of your account."
    Then I press "Create Profile Now"
    Then I wait "4" seconds

    # Check if user is now enabled
    Given I am on "/index.php?id=48&pid=46"
    Then I should see "[random:1]"
    Then I should see "status: enabled"

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
