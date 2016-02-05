# Features/Registration/Misc/SendPost.feature
@Registration @RegistrationMisc @RegistrationMiscSendPost
Feature: SendPost

  Scenario: Check if it's possible to send some parameters via SendPost
    Given I am on "/index.php?id=29"
    Then I should see "Create a new tt_content with this registration (over sendPost)"
    And I fill in the following:
      | Username | [random] |
      | Password | test |
      | Repeat Password | test |
      | Email | alex@einpraegsam.net |
    And I press "Create Profile Now"

    Then the sourcecode should contain '[email] =&gt; alex@einpraegsam.net'
    Then the sourcecode should contain '[username] =&gt; [random:1]'

  # Clean up
  Scenario: Delete all temporary tt_content entries
    Given I am on "/index.php?id=27"
    Then I should see "All content elements deleted with query"

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
