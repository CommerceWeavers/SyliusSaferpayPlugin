<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\Request\Assert;
use Exception;
use Payum\Core\Payum;
use Sylius\Bundle\PayumBundle\Factory\GetStatusFactoryInterface;
use Sylius\Bundle\PayumBundle\Factory\ResolveNextRouteFactoryInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

final class AssertAction
{
    public function __construct (
        private Payum $payum,
        private GetStatusFactoryInterface $getStatusRequestFactory,
        private ResolveNextRouteFactoryInterface $resolveNextRouteRequestFactory,
        private RouterInterface $router,
        private PaymentRepositoryInterface $paymentRepository,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(Request $request): Response
    {
        $token = $this->payum->getHttpRequestVerifier()->verify($request);

        $assert = new Assert($token);
        $this->payum->getGateway($token->getGatewayName())->execute($assert);

        $status = $this->getStatusRequestFactory->createNewWithModel($assert->getFirstModel());
        $this->payum->getGateway($token->getGatewayName())->execute($status);

        $resolveNextRoute = $this->resolveNextRouteRequestFactory->createNewWithModel($assert->getFirstModel());
        $this->payum->getGateway($token->getGatewayName())->execute($resolveNextRoute);

        $this->payum->getHttpRequestVerifier()->invalidate($token);

        return new RedirectResponse($this->router->generate($resolveNextRoute->getRouteName(), $resolveNextRoute->getRouteParameters()));
    }
}
