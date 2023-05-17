<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
use CommerceWeavers\SyliusSaferpayPlugin\Provider\PaymentProviderInterface;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class PrepareRefundActionSpec extends ObjectBehavior
{
    function let(
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        MetadataInterface $orderMetadata,
        PaymentProviderInterface $paymentProvider,
        TokenProviderInterface $tokenProvider,
        RequestConfiguration $requestConfiguration,
    ): void {
        $requestConfigurationFactory->create($orderMetadata, Argument::type(Request::class))->willReturn($requestConfiguration);

        $this->beConstructedWith($requestConfigurationFactory, $orderMetadata, $paymentProvider, $tokenProvider);
    }

    function it_returns_redirect_response_to_target_url_from_token(
        PaymentProviderInterface $paymentProvider,
        TokenProviderInterface $tokenProvider,
        RequestConfiguration $requestConfiguration,
        Request $request,
        PaymentInterface $payment,
        TokenInterface $token,
    ): void {
        $paymentProvider->provideForRefund('1', '1')->willReturn($payment);
        $tokenProvider->provide($payment, $requestConfiguration)->willReturn($token);
        $token->getTargetUrl()->willReturn('/url');

        $this($request, '1', '1')->shouldBeLike(new RedirectResponse('/url'));
    }
}
