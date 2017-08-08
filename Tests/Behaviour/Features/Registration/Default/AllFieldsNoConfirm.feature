# Features/Registration/Default/AllFieldsNoConfirm.feature
@Registration @RegistrationDefault @RegistrationDefaultAllFieldsNoConfirm
Feature: AllFieldsNoConfirm

  Scenario: Check if a large registration is possible
    Given I am on "/index.php?id=22"
    Then I should see "Create a new user-profile"
    And I fill in the following:
      | Username | [random] |
      | Password | test |
      | Repeat Password | test |
      | Full Name | Alexander Markus Kellner |
      | Firstname | Randy |
      | Middlename | Rudolph |
      | Lastname | Rentier |
      | Address | Kunstmühlstr. 12a |
      | Telephone | 123456789 |
      | Fax | 123456780 |
      | Email | alex@einpraegsam.net |
      | Title | Prof. Dr. Dr. |
      | ZIP | 89999 |
      | City | Rosenheim |
      | Website | www.in2code.de |
      | Company | in2code GmbH |
      | Birthdate | 20/01/1979 |
    And I select "Group 2" from "tx_femanager_pi1[user][usergroup][0]"
    And I select "Deutschland" from "tx_femanager_pi1[user][country]"
    And I press "Create Profile Now"

    Then I should see "User registration"

    Given I am on "/index.php?id=33"
    Then I should see "[random:1]"
    Then I should see "Alexander Markus Kellner"
    Then I should see "Randy"
    Then I should see "Rudolph"
    Then I should see "Rentier"
    Then I should see "Kunstmühlstr. 12a"
    Then I should see "123456789"
    Then I should see "123456780"
    Then I should see "alex@einpraegsam.net"
    Then I should see "Prof. Dr. Dr."
    Then I should see "89999"
    Then I should see "Rosenheim"
    Then I should see "www.in2code.de"
    Then I should see "in2code GmbH"
    Then I should see "333928800"

  # Clean up
  Scenario: Delete all temporary fe_users entries
    Given I am on "/index.php?id=31"
    Then I should see "All content elements deleted that have no in2code.de email address"
