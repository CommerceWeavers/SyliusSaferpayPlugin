<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Processor\SaferpayPaymentProcessor;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProviderInterface;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PrepareAssertAction
{
    public function __construct(
        private RequestConfigurationFactoryInterface $requestConfigurationFactory,
        private MetadataInterface $orderMetadata,
        private PaymentProviderInterface $paymentProvider,
        private TokenProviderInterface $tokenProvider,
        private SaferpayPaymentProcessor $saferpayPaymentProcessor,
        private UrlGeneratorInterface $router,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request, string $tokenValue): RedirectResponse
    {
        $this->logger->debug('Synchronous processing started');

        try {
            $payment = $this->paymentProvider->provideForOrder($tokenValue);
            $this->saferpayPaymentProcessor->lock($payment);
        } catch (\Exception) {
            $this->logger->debug('Synchronous processing aborted - webhook handled the payment');

            $request->getSession()->getFlashBag()->add('success', 'sylius.payment.completed');

            return new RedirectResponse($this->router->generate('sylius_shop_order_thank_you'));
        }

        $requestConfiguration = $this->requestConfigurationFactory->create($this->orderMetadata, $request);
        $lastPayment = $this->paymentProvider->provideForAssert($tokenValue);

        $token = $this->tokenProvider->provideForAssert($lastPayment, $requestConfiguration);

        $this->logger->debug('Synchronous processing PrepareAssertAction succeeded');

        return new RedirectResponse($token->getTargetUrl());
    }
}
