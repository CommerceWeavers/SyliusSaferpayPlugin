@managing_payment_methods
Feature: Adding a new Saferpay payment method
    In order to diversify payment methods in the store
    As an Administrator
    I want to be able to add the Saferpay as a payment method

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui
    Scenario: Adding a new Saferpay payment method
        When I want to create a payment method with Saferpay gateway factory
        And I name it "Saferpay" in "English (United States)"
        And I specify its code as "saferpay"
        And I configure it with provided Saferpay credentials
        And I add it
        Then I should be notified that it has been successfully created
        And the payment method "Saferpay" should appear in the registry
