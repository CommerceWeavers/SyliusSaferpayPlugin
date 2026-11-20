<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Controller;

use CommerceWeavers\SyliusSaferpayPlugin\Controller\PaymentController;
use CommerceWeavers\SyliusSaferpayPlugin\Payum\Exception\PaymentRefundFailedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sylius\Bundle\ResourceBundle\Controller\RedirectHandlerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController as BaseResourceController;
use Sylius\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

final class PaymentControllerTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $decoratedResourceController;

    private ObjectProphecy $metadata;

    private ObjectProphecy $requestConfigurationFactory;

    private ObjectProphecy $redirectHandler;

    private PaymentController $controller;

    protected function setUp(): void
    {
        $this->decoratedResourceController = $this->prophesize(BaseResourceController::class);
        $this->metadata = $this->prophesize(MetadataInterface::class);
        $this->requestConfigurationFactory = $this->prophesize(RequestConfigurationFactoryInterface::class);
        $this->redirectHandler = $this->prophesize(RedirectHandlerInterface::class);

        $this->controller = new PaymentController(
            $this->decoratedResourceController->reveal(),
            $this->metadata->reveal(),
            $this->requestConfigurationFactory->reveal(),
            $this->redirectHandler->reveal(),
        );
    }

    public function test_it_handles_payment_refund_failed_exception_during_applying_state_machine_transition(): void
    {
        $request = $this->prophesize(Request::class);
        $configuration = $this->prophesize(RequestConfiguration::class);
        $session = $this->prophesize(Session::class);
        $flashBag = $this->prophesize(FlashBagInterface::class);

        $this->decoratedResourceController->applyStateMachineTransitionAction($request)->willThrow(PaymentRefundFailedException::class);

        $request->getSession()->willReturn($session);
        $session->getFlashBag()->willReturn($flashBag);
        $flashBag->add('error', 'sylius_saferpay.payment.refund_failed')->shouldBeCalled();

        $this->requestConfigurationFactory->create($this->metadata, $request)->willReturn($configuration);
        $this->redirectHandler->redirectToReferer($configuration)->shouldBeCalled();

        self::assertInstanceOf(Response::class, $this->controller->applyStateMachineTransitionAction($request->reveal()));
    }
}
