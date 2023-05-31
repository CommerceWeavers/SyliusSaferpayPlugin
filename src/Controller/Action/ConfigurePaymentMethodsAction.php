<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Controller\Action;

use CommerceWeavers\SyliusSaferpayPlugin\Command\ConfigurePaymentMethods;
use CommerceWeavers\SyliusSaferpayPlugin\Form\Type\SaferpayPaymentMethodsConfigurationType;
use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;

final class ConfigurePaymentMethodsAction
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private FormFactoryInterface $formFactory,
        private Environment $twig,
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $paymentMethodId = $request->attributes->get('id');

        /** @var PaymentMethodInterface|null $paymentMethod */
        $paymentMethod = $this->paymentMethodRepository->find($paymentMethodId);
        if (null === $paymentMethod) {
            throw new NotFoundHttpException('The payment method has not been found');
        }

        /** @var GatewayConfigInterface|null $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        if (null === $gatewayConfig) {
            throw new NotFoundHttpException('The gateway config for payment method has not been found');
        }

        $form = $this->formFactory->create(
            SaferpayPaymentMethodsConfigurationType::class,
            $gatewayConfig->getConfig(),
            ['paymentMethod' => $paymentMethod]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->commandBus->dispatch(new ConfigurePaymentMethods($paymentMethodId, $data['allowed_payment_methods']));

            $this->addFlashMessage($request, 'success', 'sylius_saferpay.payment_method.configure_payment_methods');

            /** @var string $referer */
            $referer = $request->headers->get('referer');

            return new RedirectResponse($referer);
        }

        return new Response($this->twig->render(
            '@CommerceWeaversSyliusSaferpayPlugin/Admin/PaymentMethod/configurePaymentMethods.html.twig',
            [
                'form' => $form->createView(),
                'payment_method' => $paymentMethod,
            ],
        ));
    }

    private function addFlashMessage(Request $request, string $type, string $message): void
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add($type, $message);
    }
}
