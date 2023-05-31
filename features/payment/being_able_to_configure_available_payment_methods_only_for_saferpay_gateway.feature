@managing_payment_methods
Feature: Being able to configure available payment methods only for Saferpay gateway
    In order not to mislead with other payment methods than Saferpay
    As an Administrator
    I want to be able to configure available payment methods only for Saferpay gateway

    Background:
        Given the store operates on a single channel in "United States"
        And the store allows paying with Saferpay
        And the store allows paying with "Cash on Delivery"
        And I am logged in as an administrator

    @ui
    Scenario: Being able to configure available payment methods only for Saferpay gateway
        When I browse payment methods
        Then I should be able to configure the available payment methods for "Saferpay"
        But I should not be able to configure the available payment methods for "Cash on Delivery"
