@managing_payment_methods
Feature: Adding a new Saferpay payment method
    In order to pay for orders in different ways
    As an Administrator
    I want to add a new payment method to the registry

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @todo @ui
    Scenario: Adding a new Saferpay payment method
        When I want to create a new payment method with "Saferpay" gateway factory
        And I name it "Saferpay" in "English (United States)"
        And I specify its code as "saferpay"
        And I configure it with test Saferpay credentials
        And I add it
        Then I should be notified that it has been successfully created
        And the payment method "Saferpay" should appear in the registry
