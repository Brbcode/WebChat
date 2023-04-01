Feature:
    In order to ensure are handled correctly
    As a user and developer
    I want to test exception listener

    Scenario: Unhandled exception return a json response with message and code
        When I send fake Request that throw an unhandled exception
        And the exception response node "message" should be equal to "Internal server error"
        And the exception response node "code" should be equal to 500

    Scenario: Handled exception return a json response with message and code
        When I send fake Request that throw an handled exception with code 400 and message:
            """
            Custom exception message
            """
        And the exception response node "message" should be equal to "Custom exception message"
        And the exception response node "code" should be equal to 400

    Scenario: Any exception return a json with a detail on test environment
        When I send fake Request that throw an unhandled exception
        And the exception response node "environment" should be equal to "test"
        And the exception response node "detail.trace" should have elements
        And the exception response node "detail.message" should exist
        And the exception response node "detail.code" should exist
        And the exception response node "detail.type" should exist

    Scenario: Behat "Given no exception" rule make return no detail.trace
        Given no exception trace
        When I send fake Request that throw an unhandled exception
        And the exception response node "detail.trace" should have no elements

    Scenario: Behat "Given (\d+) exception" rule make return specific count detail.trace
        Given 5 exception trace
        When I send fake Request that throw an unhandled exception
        And the exception response node "detail.trace" should have 5 elements