@managing_orders
Feature: Refunding order's payment with Saferpay payment method
    In order to return the money to the Customer
    As an Administrator
    I want to be able to refund a paid order with Saferpay payment method

    Background:
        Given the store operates on a single channel in "United States"
        And the store ships everywhere for free
        And the store allows paying with Saferpay
        And the store has a product "Commerce Weavers T-Shirt" priced at "$29.99"
        And there is a customer "john@example.com" that placed an order "#00000001"
        And the customer bought a single "Commerce Weavers T-Shirt"
        And the customer chose "Free" shipping method to "United States" with "Saferpay" payment
        And this order is already paid with Saferpay payment
        And I am logged in as an administrator

    @ui
    Scenario: Refunding order's payment with Saferpay payment method
        Given I am viewing the summary of this order
        When I mark this order's payment as refunded
        Then I should be notified that the order's payment has been successfully refunded
        And it should have payment with state refunded
        And it's payment state should be refunded
