Feature: User can create, reset and delete private/protected/public counters
    In order to create password protected counters
    User must provide nick & password
    So that created counter can be protected

Background:
  Given user "Mee" with password "fuubar123"

Scenario: Front page for not logged in doesn't show button for Private counter
  When "/" page is loaded
  Then page has button "Public"
    But page does not have button "Protected"

Scenario: Front page for not logged in doesn't show button for Private counter
  Given user "Mee" is logged in
  When "/" page is loaded
  Then page has button "Public"
    And page has button "Protected"

Scenario: User opens protected counter
  Given user "Mee" has protected counter "Foobar" with "19" days
  When "/foobar/Mee" page is loaded
    And the counter is "19"
    And page has "Days without Foobar"

Scenario: User creates protected counter
  Given user "Mee" is logged in
  When user posts protected counter "being sober"
    Then response says "Counter created"

Scenario: User page lists users counters
  Given user "Alpha" with password "fuubar123"
    And system has counters:
    | Owner     | Counter  | Days |
    | Mee       | First    | 0    |
    | Mee       | Last     | 99   |
    | Alpha     | Second   | 1    |
    |           | Third    | 1    |
  When "/user/Mee" page is loaded
  Then page has "First"
    And page has "Last"
    But page doesn't have "Second"
    But page doesn't have "Third"

Scenario: Front page has link to user's counters
  Given user "Alpha" with password "fuubar123"
  And system has counters:
    | Owner     | Counter  | Days |
    | Mee       | First    | 1    |
    | Alpha     | Second   | 2    |
    |           | Third    | 3    |
  When "/" page is loaded
  Then page has "/user/Mee"
    And page has "/user/Alpha"

Scenario: Only public/protected counters shown for others
  Given user "Alpha" with password "fuubar123"
    And user "Mee" is logged in
    And system has counters:
    | Owner     | Counter   | Days | Visibility |
    | Alpha     | PublicOne    | 2    | public    |
    | Alpha     | ProtectedOne | 3    | protected |
    | Alpha     | PrivateOne   | 4    | private   |
  When "/user/Alpha" page is loaded
  Then page has "PublicOne"
    And page has "ProtectedOne"
    But page doesn't have "PrivateOne"

Scenario: All counters are shown to owner
  Given user "Alpha" with password "fuubar123"
    And user "Alpha" is logged in
    And system has counters:
    | Owner     | Counter   | Days | Visibility |
    | Alpha     | PublicOne    | 2    | public     |
    | Alpha     | ProtectedOne | 3    | protected  |
    | Alpha     | PrivateOne   | 4    | private    |
  When "/user/Alpha" page is loaded
  Then page has "PublicOne"
    And page has "ProtectedOne"
    And page has "PrivateOne"

Scenario: Protected counter has link to its owner
  Given user "Mee" has protected counter "Foobar" with "19" days
  When "/foobar/Mee" page is loaded
  Then page has "/user/Mee"

Scenario: User can view his own private counter
  Given user "Mee" has private counter "My own" with "7" days
    And user "Mee" is logged in
  When "/my-own/Mee" page is loaded
  Then page has "My own"
    And the counter is "7"

Scenario: Other people cannot see private counters
  Given user "Someone" with password "fuubar123"
    And user "Someone" has private counter "My own" with "7" days
    And user "Mee" is logged in
  When "/my-own/Someone" page is loaded
    Then page has "Page not found"

Scenario: Counter has link to delete counter
  Given user "Mee" has private counter "removable" with "7" days
    And user "Mee" is logged in
  When "/removable/Mee" page is loaded
  Then page has "Delete"

Scenario: Counter has link to delete counter - only for the owner
  Given user "Yuu" with password "irrelevant"
    And user "Yuu" has private counter "removable" with "7" days
    And user "Mee" is logged in
  When "/removable/Yuu" page is loaded
  Then page doesn't have "Delete"

Scenario: Counter-list has link to delete counter
  Given user "Mee" has private counter "removable" with "7" days
  And user "Mee" is logged in
  When "/user/Mee" page is loaded
  Then page has "Delete"

Scenario: Counter-list has link to delete counter - only for the owner
  Given user "Yuu" with password "irrelevant"
  And user "Yuu" has private counter "removable" with "7" days
  And user "Mee" is logged in
  When "/user/Yuu" page is loaded
  Then page doesn't have "Delete"

Scenario: Counter can be removed
  Given user "Mee" has private counter "removable" with "7" days
    And user "Mee" is logged in
  When user deletes counter "removable" by "Mee"
    Then json response has message "Counter removed"
    And  counter "removable" by "Mee" doesn't exist

Scenario: 0-Counter can be removed
  Given user "Mee" has private counter "new" with "0" days
    And user "Mee" is logged in
  When user deletes counter "new" by "Mee"
    Then json response has message "Counter removed"
    And  counter "new" by "Mee" doesn't exist

Scenario: User cannot remove other users counters
  Given user "Bertha" with password "irrelevant"
    And user "Bertha" has protected counter "removable" with "47" days
    And user "Mee" is logged in
  When user deletes counter "removable" by "Bertha"
    Then json response has message "Unauthorized action"
    And counter "removable" by "Bertha" exists

Scenario: Delete action doesn't reveal existence of private counter
  Given user "Bertha" with password "irrelevant"
    And user "Bertha" has private counter "Sex with an ex" with "3" days
    And user "Mee" is logged in
  When user deletes counter "Sex with an ex" by "Bertha"
    Then json response has message "Counter not found"
