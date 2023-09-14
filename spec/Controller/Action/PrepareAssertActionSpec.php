<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentBeingProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Processor\SaferpayPaymentProcessorInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProviderInterface;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PrepareAssertActionSpec extends ObjectBehavior
{
    function let(
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        MetadataInterface $orderMetadata,
        PaymentProviderInterface $paymentProvider,
        TokenProviderInterface $tokenProvider,
        RequestConfiguration $requestConfiguration,
        SaferpayPaymentProcessorInterface $saferpayPaymentProcessor,
        UrlGeneratorInterface $router,
        LoggerInterface $logger,
    ): void {
        $requestConfigurationFactory->create($orderMetadata, Argument::type(Request::class))->willReturn($requestConfiguration);

        $this->beConstructedWith(
            $requestConfigurationFactory,
            $orderMetadata,
            $paymentProvider,
            $tokenProvider,
            $saferpayPaymentProcessor,
            $router,
            $logger,
            1,
        );
    }

    function it_throws_an_exception_when_last_payment_for_given_order_token_value_does_not_exist(
        PaymentProviderInterface $paymentProvider,
        PaymentInterface $payment,
        Request $request,
    ): void {
        $paymentProvider->provideForOrder('TOKEN')->willReturn($payment);
        $paymentProvider->provideForAssert('TOKEN')->willThrow(PaymentAlreadyProcessedException::class);

        $this->shouldThrow(PaymentAlreadyProcessedException::class)->during('__invoke', [$request, 'TOKEN']);
    }

    function it_returns_to_thank_you_page_if_payment_is_already_processed(
        PaymentProviderInterface $paymentProvider,
        SaferpayPaymentProcessorInterface $saferpayPaymentProcessor,
        UrlGeneratorInterface $router,
        Request $request,
        PaymentInterface $payment,
    ): void {
        $paymentProvider->provideForOrder('TOKEN')->willReturn($payment);
        $saferpayPaymentProcessor->lock($payment)->willThrow(PaymentAlreadyProcessedException::class);

        $router
            ->generate('commerce_weavers_sylius_after_unsuccessful_payment', ['tokenValue' => 'TOKEN'])
            ->willReturn('https://after-unsuccessful-payment.com/TOKEN')
        ;

        $this($request, 'TOKEN')->shouldBeLike(new RedirectResponse('https://after-unsuccessful-payment.com/TOKEN'));
    }

    function it_returns_to_thank_you_page_if_payment_is_being_processed(
        PaymentProviderInterface $paymentProvider,
        SaferpayPaymentProcessorInterface $saferpayPaymentProcessor,
        UrlGeneratorInterface $router,
        Request $request,
        PaymentInterface $payment,
    ): void {
        $paymentProvider->provideForOrder('TOKEN')->willReturn($payment);
        $saferpayPaymentProcessor->lock($payment)->willThrow(PaymentBeingProcessedException::class);

        $router
            ->generate('commerce_weavers_sylius_after_unsuccessful_payment', ['tokenValue' => 'TOKEN'])
            ->willReturn('https://after-unsuccessful-payment.com/TOKEN')
        ;

        $this($request, 'TOKEN')->shouldBeLike(new RedirectResponse('https://after-unsuccessful-payment.com/TOKEN'));
    }

    function it_returns_redirect_response_to_target_url_from_token(
        PaymentProviderInterface $paymentProvider,
        SaferpayPaymentProcessorInterface $saferpayPaymentProcessor,
        TokenProviderInterface $tokenProvider,
        RequestConfiguration $requestConfiguration,
        Request $request,
        PaymentInterface $payment,
        TokenInterface $token,
    ): void {
        $paymentProvider->provideForOrder('TOKEN')->willReturn($payment);
        $saferpayPaymentProcessor->lock($payment)->shouldBeCalled();

        $paymentProvider->provideForAssert('TOKEN')->willReturn($payment);
        $tokenProvider->provideForAssert($payment, $requestConfiguration)->willReturn($token);
        $token->getTargetUrl()->willReturn('/url');

        $this($request, 'TOKEN')->shouldBeLike(new RedirectResponse('/url'));
    }
}
