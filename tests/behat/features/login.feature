@api @drupal
Feature: User login

  Background:
    Given users:
      | name      | mail                     | pass    |
      | test-user | test-user@localhost.test | test123 |

  Scenario: I'm on the login page and I login with the user's name.
    Given I am an anonymous user
    Then I am on "/user/login"
    And I fill in the following:
      | name | test-user |
      | pass | test123   |
    And I press the "Log in" button
    Then I am logged in as "test-user"
