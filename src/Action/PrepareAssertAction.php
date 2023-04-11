<?php

/** @noinspection PhpClassConstantAccessedViaChildClassInspection */

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Action;

use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfigurationFactoryInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Resource\Metadata\MetadataInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PrepareAssertAction
{
    public function __construct(
        private RequestConfigurationFactoryInterface $requestConfigurationFactory,
        private MetadataInterface $orderMetadata,
        private OrderRepositoryInterface $orderRepository,
        private Payum $payum,
    ) {
    }

    public function __invoke(Request $request, string $tokenValue): Response
    {
        $requestConfiguration = $this->requestConfigurationFactory->create($this->orderMetadata, $request);

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->findOneByTokenValue($tokenValue);

        if (null === $order) {
            throw new NotFoundHttpException(sprintf('Order with token "%s" does not exist.', $tokenValue));
        }

        $lastPayment = $order->getLastPayment(PaymentInterface::STATE_NEW);

        if (null === $lastPayment) {
            throw new BadRequestException(sprintf('Order with token "%s" does not have an active payment.', $tokenValue));
        }

        $assertRequestToken = $this->createAssertToken($lastPayment, $requestConfiguration);

        return new RedirectResponse($assertRequestToken->getTargetUrl());
    }

    private function createAssertToken(PaymentInterface $lastPayment, RequestConfiguration $requestConfiguration): TokenInterface
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $lastPayment->getMethod();
        $gatewayName = $paymentMethod->getGatewayConfig()->getGatewayName();
        $redirectOptions = $requestConfiguration->getParameters()->get('redirect');

        return $this->payum->getTokenFactory()->createToken(
            $gatewayName,
            $lastPayment,
            $redirectOptions['route'] ?? null,
            $redirectOptions['parameters'] ?? [],
        );
    }
}
