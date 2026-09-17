# Features/Registration/Default/UserConfirmWithRedirect.feature
@Registration @RegistrationDefault @RegistrationDefaultUserConfirmWithRedirect
Feature: UserConfirmWithRedirect

  Scenario: Check if a registration is possible with redirect after user confirmation
    Given I am on "/index.php?id=146"
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
    Given I am on "/index.php?id=48&pid=146"
    Then I should see "[random:1]"
    Then I should see "status: disabled"

    # Click user confirmation link
    Then I follow "User confirmation link"

    # Create Profile and redirect
    Then I should see "Please confirm the creation of your account."
    And I press "Create Profile Now"
    Then I should see "User Confirm Redirect Target"

    # Check if user is now enabled
    Given I am on "/index.php?id=48&pid=46"
    Then I should see "[random:1]"
    Then I should see "status: enabled"

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
