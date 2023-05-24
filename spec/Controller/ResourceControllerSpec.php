<?php

declare(strict_types=1);

namespace spec\CommerceWeavers\SyliusSaferpayPlugin\Controller;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\PaymentRefundFailedException;
use PhpSpec\ObjectBehavior;
use Sylius\Bundle\ResourceBundle\Controller\RedirectHandlerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController as BaseResourceController;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

final class ResourceControllerSpec extends ObjectBehavior
{
    function let(
        BaseResourceController $decoratedResourceController,
        MetadataInterface $metadata,
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        RedirectHandlerInterface $redirectHandler,
    ): void {
        $this->beConstructedWith($decoratedResourceController, $metadata, $requestConfigurationFactory, $redirectHandler);
    }

    function it_handles_payment_refund_failed_exception_during_applying_state_machine_transition(
        BaseResourceController $decoratedResourceController,
        MetadataInterface $metadata,
        RequestConfigurationFactoryInterface $requestConfigurationFactory,
        RedirectHandlerInterface $redirectHandler,
        Request $request,
        RequestConfiguration $configuration,
        Session $session,
        FlashBagInterface $flashBag,
    ): void {
        $decoratedResourceController->applyStateMachineTransitionAction($request)->willThrow(PaymentRefundFailedException::class);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('error', 'sylius_saferpay.payment.refund_failed')->shouldBeCalled();

        $requestConfigurationFactory->create($metadata, $request)->willReturn($configuration);
        $redirectHandler->redirectToReferer($configuration)->shouldBeCalled();

        $this->applyStateMachineTransitionAction($request)->shouldHaveType(Response::class);
    }
}
