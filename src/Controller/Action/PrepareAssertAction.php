<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentBeingProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Processor\SaferpayPaymentProcessorInterface;
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
        private SaferpayPaymentProcessorInterface $saferpayPaymentProcessor,
        private UrlGeneratorInterface $router,
        private LoggerInterface $logger,
        private int $maxAttempts = 100,
    ) {
    }

    public function __invoke(Request $request, string $tokenValue): RedirectResponse
    {
        $this->logger->debug('PrepareAssertAction: Synchronous processing started');

        $count = 0;
        do {
            try {
                $payment = $this->paymentProvider->provideForOrder($tokenValue);
                $this->saferpayPaymentProcessor->lock($payment);
            } catch (PaymentBeingProcessedException) {
                $this->logger->debug('Synchronous processing suspended - webhook handled the payment');

                if (++$count >= $this->maxAttempts) {
                    $this->logger->debug('Synchronous processing aborted - webhook handled the payment');

                    return new RedirectResponse($this->router->generate(
                        'commerce_weavers_sylius_after_unsuccessful_payment',
                        ['tokenValue' => $tokenValue],
                    ));
                }

                continue;
            } catch (PaymentAlreadyProcessedException) {
                $this->logger->debug('Synchronous processing aborted - webhook handled the payment');

                return new RedirectResponse($this->router->generate(
                    'commerce_weavers_sylius_after_unsuccessful_payment',
                    ['tokenValue' => $tokenValue],
                ));
            }

            break;
        } while ($count < $this->maxAttempts);

        $requestConfiguration = $this->requestConfigurationFactory->create($this->orderMetadata, $request);
        $lastPayment = $this->paymentProvider->provideForAssert($tokenValue);

        $token = $this->tokenProvider->provideForAssert($lastPayment, $requestConfiguration);

        $this->logger->debug('Synchronous processing PrepareAssertAction succeeded');

        return new RedirectResponse($token->getTargetUrl());
    }
}
