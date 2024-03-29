<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Exception\PaymentAlreadyProcessedException;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Provider\TokenProviderInterface;
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
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PrepareCaptureActionSpec extends ObjectBehavior
{
    function let(
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        MetadataInterface $orderMetadata,
        PaymentProviderInterface $paymentProvider,
        TokenProviderInterface $tokenProvider,
        RequestConfiguration $requestConfiguration,
        LoggerInterface $logger,
        UrlGeneratorInterface $router,
    ): void {
        $requestConfigurationFactory->create($orderMetadata, Argument::type(Request::class))->willReturn($requestConfiguration);

        $this->beConstructedWith(
            $requestConfigurationFactory,
            $orderMetadata,
            $paymentProvider,
            $tokenProvider,
            $logger,
            $router,
        );
    }

    function it_throws_an_exception_when_last_payment_for_given_order_token_value_does_not_exist(
        PaymentProviderInterface $paymentProvider,
        Request $request,
    ): void {
        $paymentProvider->provideForCapture('TOKEN')->willThrow(NotFoundHttpException::class);

        $this->shouldThrow(NotFoundHttpException::class)->during('__invoke', [$request, 'TOKEN']);
    }

    function it_returns_to_thank_you_page_if_payment_is_already_processed(
        PaymentProviderInterface $paymentProvider,
        UrlGeneratorInterface $router,
        Request $request,
        PaymentInterface $payment,
        Session $session,
        FlashBagInterface $flashBag
    ): void {
        $paymentProvider->provideForCapture('TOKEN')->willThrow(PaymentAlreadyProcessedException::class);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('success', 'sylius.payment.completed')->shouldBeCalled();

        $router->generate('sylius_shop_order_thank_you')->willReturn('https://thank-you.com');

        $this($request, 'TOKEN')->shouldBeLike(new RedirectResponse('https://thank-you.com'));
    }

    function it_returns_redirect_response_to_target_url_from_token(
        PaymentProviderInterface $paymentProvider,
        TokenProviderInterface $tokenProvider,
        RequestConfiguration $requestConfiguration,
        Request $request,
        PaymentInterface $payment,
        TokenInterface $token,
    ): void {
        $paymentProvider->provideForCapture('TOKEN')->willReturn($payment);
        $tokenProvider->provideForCapture($payment, $requestConfiguration)->willReturn($token);
        $token->getTargetUrl()->willReturn('/url');

        $this($request, 'TOKEN')->shouldBeLike(new RedirectResponse('/url'));
    }
}
