@logging
Feature: Logging communication with Saferpay
    In order to ensure that communication with the Saferpay is working correctly
    As an Administrator
    I want to be able to browse logs of the communication with the Saferpay

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "PHP T-Shirt"
        And the store ships everywhere for Free
        And the store allows paying with Saferpay
        And there is a customer "john.doe@gmail.com" that placed an order "#00000022"
        And the customer bought a single "PHP T-Shirt"
        And the customer chose "Free" shipping method to "United States" with "Saferpay" payment
        And I am logged in as an administrator

    @ui
    Scenario: Logging of communication with the Saferpay with enabled debug mode
        Given the payment method's debug mode is enabled
        And the order payment failed on the assertion step
        But the order has been paid successfully with Saferpay payment method on the second try
        When I check the Saferpay's transaction logs
        Then I should see the error transaction log for order "#00000022", described as "Payment authorization"
        And I should see the informational transaction log for order "#00000022", described as "Payment authorization"
        And I should see the informational transaction log for order "#00000022", described as "Payment assertion"
        And I should see the informational transaction log for order "#00000022", described as "Payment capture"

    @ui
    Scenario: Logging of communication with the Saferpay with disabled debug mode
        Given the payment method's debug mode is disabled
        And the order payment failed on the assertion step
        But the order has been paid successfully with Saferpay payment method on the second try
        When I check the Saferpay's transaction logs
        Then I should see 1 transaction log in the list
        And I should see the error transaction log for order "#00000022", described as "Payment assertion"

    @ui
    Scenario: Checking details of a given transaction log
        Given the payment method's debug mode is enabled
        And the order has been paid successfully with Saferpay payment method
        When I check the Saferpay's transaction logs
        And I open details of the "Payment authorization succeeded" log for order "#00000022"
        Then I should see details of the log with "Payment authorization succeeded" description and "Informational" type
        And I should see the context information about communication with Saferpay
