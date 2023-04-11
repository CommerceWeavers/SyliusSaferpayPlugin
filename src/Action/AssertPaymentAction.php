<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use Exception;
use Payum\Core\Payum;
use Sylius\Bundle\PayumBundle\Factory\ResolveNextRouteFactoryInterface;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

final class AssertPaymentAction
{
    public function __construct(
        private Payum $payum,
        private PaymentRepositoryInterface $paymentRepository,
        private ResolveNextRouteFactoryInterface $resolveNextRouteRequestFactory,
        private RouterInterface $router,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(Request $request, int $paymentId): Response
    {
        $payment = $this->paymentRepository->find($paymentId);
        $gateway = $payment->getMethod()->getGatewayConfig()->getGatewayName();

        if (!$payment instanceof PaymentInterface) {
            throw new NotFoundHttpException(sprintf('Payment with id %s does not exist', $paymentId));
        }

        $assert = new Assert($payment);
        $this->payum->getGateway($gateway)->execute($assert);

        $status = new GetStatus($payment);
        $this->payum->getGateway($gateway)->execute($status);

        $resolveNextRoute = $this->resolveNextRouteRequestFactory->createNewWithModel($payment);
        $this->payum->getGateway($gateway)->execute($resolveNextRoute);

        return new RedirectResponse($this->router->generate($resolveNextRoute->getRouteName(), $resolveNextRoute->getRouteParameters()));
    }
}
