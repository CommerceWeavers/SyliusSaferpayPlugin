<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Persistence\ObjectManager;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

/**
 * Creates the logged-in customer's cart directly, so the scenario does not depend on the Sylius Behat cart setup
 * steps, which behave differently across Sylius 2.x versions. The shop picks the cart up through its customer-based
 * cart context.
 */
final class CartContext implements Context
{
    /**
     * @param FactoryInterface<OrderInterface> $orderFactory
     * @param FactoryInterface<OrderItemInterface> $orderItemFactory
     */
    public function __construct(
        private SharedStorageInterface $sharedStorage,
        private FactoryInterface $orderFactory,
        private FactoryInterface $orderItemFactory,
        private OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private OrderModifierInterface $orderModifier,
        private ProductVariantResolverInterface $productVariantResolver,
        private ObjectManager $orderManager,
    ) {
    }

    /**
     * @Given /^I have (product "[^"]+") in my cart$/
     */
    public function iHaveProductInMyCart(ProductInterface $product): void
    {
        /** @var ChannelInterface $channel */
        $channel = $this->sharedStorage->get('channel');
        /** @var ShopUserInterface $user */
        $user = $this->sharedStorage->get('user');

        $currency = $channel->getBaseCurrency();
        $locale = $channel->getDefaultLocale();
        Assert::notNull($currency);
        Assert::notNull($locale);

        $cart = $this->orderFactory->createNew();
        $cart->setChannel($channel);
        // a cart is only restored for a logged-in customer when it was not created as a guest
        $cart->setCustomerWithAuthorization($user->getCustomer());
        $cart->setCurrencyCode($currency->getCode());
        $cart->setLocaleCode($locale->getCode());

        /** @var ProductVariantInterface|null $variant */
        $variant = $this->productVariantResolver->getVariant($product);
        Assert::notNull($variant);

        $item = $this->orderItemFactory->createNew();
        $item->setVariant($variant);
        $this->orderItemQuantityModifier->modify($item, 1);
        // addToOrder() also runs the order processor
        $this->orderModifier->addToOrder($cart, $item);

        $this->orderManager->persist($cart);
        $this->orderManager->flush();

        $this->sharedStorage->set('product', $product);
    }
}
