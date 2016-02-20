# Features/Registration/Default/SmallAdminConfirm.feature
@Registration @RegistrationDefault @RegistrationDefaultSmallAdminConfirm
Feature: SmallAdminConfirm

  Scenario: Check if a small registration is possible with admin confirmation
    Given I am on "/index.php?id=25"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Username | [random] |
      | Password | test |
      | Repeat Password | test |
      | Email | alex@einpraegsam.net |
    And I press "Create Profile Now"

    Then I should see "profile will be available as soon as the admin confirms"

    # Check if user is disabled
    Given I am on "/index.php?id=48&pid=25"
    Then I should see "[random:1]"
    Then I should see "status: disabled"

    # Click admin confirmation link
    Then I follow "Admin confirmation link"

    # Check if user is now enabled
    Given I am on "/index.php?id=48&pid=25"
    Then I should see "[random:1]"
    Then I should see "status: enabled"

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
