Feature: Days Without counter works for anon user
    Site must provide creation/reseting of counters
    To an unknown user
    So that anyone can use the site

    Scenario: Front page shows expected
        Given "/" page is loaded
        When "/" page is loaded
        Then page has "Days without"
            And page has button "Public"

    Scenario: User creates new counter
        Given "/" page is loaded
        When user posts new counter "Smoking"
        Then user is redirected to "/smoking"

    Scenario: User views existing counter
        Given "public" counter "Foobar" with "19" days exists
        When "/foobar" page is loaded
        Then the page exists
        And the counter is "19"
        And page has "Days without Foobar"

    Scenario: User resets existing counter
        Given "public" counter "Resetme" with "1" days exists
        When user resets counter "Resetme"
        And the counter is "0"
        And page has "Days without Resetme"