@logging
Feature: Logging refunding order payment with Saferpay
    In order to ensure that communication with the Saferpay is working correctly
    As an Administrator
    I want to be able to browse logs of the communication with the Saferpay after refunding order payment

    Background:
        Given the store operates on a single channel in "United States"
        And the store ships everywhere for free
        And the store has a payment method "Saferpay" with a code "SAFERPAY" and Saferpay gateway
        And the store has a product "Commerce Weavers T-Shirt" priced at "$29.99"
        And there is a customer "john@example.com" that placed an order "#00000001"
        And the customer bought a single "Commerce Weavers T-Shirt"
        And the customer chose "Free" shipping method to "United States" with "Saferpay" payment
        And this order is already paid with Saferpay payment
        And I am logged in as an administrator
        And I am viewing the summary of this order

    @ui
    Scenario: Logging refunding order payment with Saferpay
        When I mark this order's payment as refunded
        And I check the Saferpay's transaction logs
        Then I should see the successful transaction log for order "#00000001", described as "Payment refund authorization"
