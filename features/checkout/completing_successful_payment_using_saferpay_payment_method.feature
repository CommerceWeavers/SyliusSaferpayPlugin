@paying_with_saferpay
Feature: Completing a successful payment using Saferpay payment method
    In order to pay for my order with ease
    As a Customer
    I want to be able to complete the checkout using Saferpay as a payment method

    Background:
        Given the store operates on a single channel in "United States"
        And there is a user "john@example.com"
        And the store has a payment method "Saferpay" with a code "SAFERPAY" and Saferpay gateway
        And the store has a product "Commerce Weavers T-Shirt" priced at "$29.99"
        And the store ships everywhere for Free
        And I am logged in as "john@example.com"

    @ui
    Scenario: Completing a successful payment using Saferpay payment method
        Given I added product "Commerce Weavers T-Shirt" to the cart
        And I have proceeded selecting "Saferpay" payment method
        When I finalize successfully the payment on the Saferpay's page
        Then I should be notified that my payment has been completed
        And I should see the thank you page
        And the latest order should have a payment with state "completed"
