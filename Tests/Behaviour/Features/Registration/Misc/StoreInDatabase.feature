# Features/Registration/Misc/StoreInDatabase.feature
@Registration @RegistrationMisc @RegistrationMiscStoreInDatabase
Feature: StoreInDatabase

  Scenario: Check if it's possible to create tt_content elements
    Given I am on "/index.php?id=26"
    Then I should see "Create a new tt_content with this registration"
    And I fill in the following:
      | Username | [random] |
      | Password | test |
      | Repeat Password | test |
      | Firstname | rand[random:1] |
      | Lastname | tt_content_lastname [deleteme] |
      | Email | alex@einpraegsam.net |
    And I press "Create Profile Now"

    Then I should see "tt_content_lastname"
    Then the sourcecode should contain 'Firstname: rand[random:1]'

  # Clean up
  Scenario: Delete all temporary tt_content entries
    Given I am on "/index.php?id=27"
    Then I should see "All content elements deleted with query"

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
