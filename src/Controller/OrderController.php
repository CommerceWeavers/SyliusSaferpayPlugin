<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Controller;

use Sylius\Bundle\CoreBundle\Controller\OrderController as BaseOrderController;
use Sylius\Bundle\ResourceBundle\Controller\RedirectHandlerInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Webmozart\Assert\Assert;

final class OrderController
{
    public function __construct(
        private BaseOrderController $decoratedOrderController,
        private MetadataInterface $metadata,
        private RequestConfigurationFactoryInterface $requestConfigurationFactory,
        private RedirectHandlerInterface $redirectHandler,
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function showAction(Request $request): Response
    {
        return $this->decoratedOrderController->showAction($request);
    }

    public function indexAction(Request $request): Response
    {
        return $this->decoratedOrderController->indexAction($request);
    }

    public function createAction(Request $request): Response
    {
        return $this->decoratedOrderController->createAction($request);
    }

    public function updateAction(Request $request): Response
    {
        return $this->decoratedOrderController->updateAction($request);
    }

    public function deleteAction(Request $request): Response
    {
        return $this->decoratedOrderController->deleteAction($request);
    }

    public function bulkDeleteAction(Request $request): Response
    {
        return $this->decoratedOrderController->bulkDeleteAction($request);
    }

    public function applyStateMachineTransitionAction(Request $request): Response
    {
        return $this->decoratedOrderController->applyStateMachineTransitionAction($request);
    }

    public function summaryAction(Request $request): Response
    {
        return $this->decoratedOrderController->summaryAction($request);
    }

    public function widgetAction(Request $request): Response
    {
        return $this->decoratedOrderController->widgetAction($request);
    }

    public function saveAction(Request $request): Response
    {
        return $this->decoratedOrderController->saveAction($request);
    }

    public function clearAction(Request $request): Response
    {
        return $this->decoratedOrderController->clearAction($request);
    }

    public function thankYouAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $orderId = $request->getSession()->get('sylius_order_id', null);
        if (null === $orderId) {
            return $this->decoratedOrderController->thankYouAction($request);
        }

        $request->getSession()->remove('sylius_order_id');
        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->find($orderId);
        Assert::notNull($order);

        $lastPayment = $order->getLastPayment();
        $penultimatePayment = $this->getPenultimatePayment($order);

        if ($lastPayment->getState() === PaymentInterface::STATE_NEW) {
            if ($penultimatePayment !== null) {
                $this->addFlashMessage($request, 'error', $this->getMessageForPenultimatePayment($penultimatePayment));
            }

            return $this->redirectHandler->redirectToRoute(
                $configuration,
                'sylius_shop_order_show',
                ['tokenValue' => $order->getTokenValue()],
            );
        }

        if ($lastPayment->getState() === PaymentInterface::STATE_COMPLETED) {
            $this->addFlashMessage($request, 'success', 'sylius.payment.completed');
        }

        return $this->decoratedOrderController->thankYouAction($request);
    }

    private function getPenultimatePayment(OrderInterface $order): ?PaymentInterface
    {
        $payments = $order->getPayments()->toArray();

        array_pop($payments);

        return array_pop($payments);
    }

    private function addFlashMessage(Request $request, string $type, string $message): void
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add($type, $message);
    }

    private function getMessageForPenultimatePayment(PaymentInterface $payment): string
    {
        if ($payment->getState() === PaymentInterface::STATE_CANCELLED) {
            return 'sylius.payment.cancelled';
        }

        return 'sylius.payment.failed';
    }
}
