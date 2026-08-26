# Features/Registration/Validation/Usergroup.feature
@Registration @RegistrationValidation @RegistrationUsergroup
Feature: Usergroup

  Scenario: Check if a usergroup from the allow list can be selected
    Given I am on "/index.php?id=80"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Username | [random] |
      | Password | test |
      | Repeat Password | test |
      | Email | [random]@einpraegsam.net |
    And I select "Group 2" from "tx_femanager_registration[user][usergroup][0]"
    And I press "Create Profile Now"
    Then I wait "4" seconds

    Then I should see "Profile successfully created"

    Given I am on "/index.php?id=89"
    Then I should see "[random:1]"
    Then I should see "usergroup => '2'"

  Scenario: Check if the usergroup selection is not offered without an allow list
    Given I am on "/index.php?id=22"
    Then I should see "Create a new user-profile"
    Then I should see "The usergroup selection is currently not available. Please contact the administrator."
    And the sourcecode should not contain 'femanager_field_usergroup'

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
