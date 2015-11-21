# Features/Registration/Misc/GenerateTtContent.feature
@Registration @RegistrationMisc @RegistrationMiscGenerateTtContent
Feature: GenerateTtContent

  Scenario: Check if it's possible to create tt_content elements
    Given I am on "/index.php?id=26"
    Then I should see "Create a new tt_content with this registration"
    And I fill in the following:
      | Username | [random] |
      | Password | test |
      | Repeat Password | test |
      | Firstname | rand[random:1] |
      | Lastname | tt_content_lastname |
      | Email | alex@in2code.de |
    And I press "Create Profile Now"

    Then I should see "tt_content_lastname"
    Then the sourcecode should contain 'Firstname: rand[random:1]'

  Scenario: Delete all tt_content entries
    Given I am on "/index.php?id=27"
    Then I should see "All content elements deleted with query"
