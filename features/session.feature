Feature: Users are able to login/logout
  Site must provide forms for login/logout
  To a user
  So that he/she doesn't have to type credentials every time

Background:
  Given user "Mee" with password "fuubar123"
    And user "Mee" has a counter "Foobar" with "19" days

Scenario: Front-page has link to login
  When "/" page is loaded
    Then page has link "Login" to "/login"
