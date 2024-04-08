<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Form\Type;

use CommerceWeavers\SyliusSaferpayPlugin\Provider\SaferpayPaymentMethodsProviderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

final class SaferpayPaymentMethodsConfigurationType extends AbstractType
{
    public function __construct(private SaferpayPaymentMethodsProviderInterface $paymentMethodsProvider)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $options['paymentMethod'];

        $builder
            ->add('allowed_payment_methods', ChoiceType::class, [
                'attr' => [
                    'class' => 'saferpay-allowed-payment-methods',
                ],
                'choices' => $this->paymentMethodsProvider->provide($paymentMethod),
                'data' => $this->getAllowedPaymentMethodsData($paymentMethod),
                'choice_label' => function (string $paymentMethodData): string {
                    return $paymentMethodData;
                },
                'property_path' => 'gatewayConfig.config[allowed_payment_methods]',
                'expanded' => true,
                'label' => 'commerce_weavers_saferpay.ui.allowed_payment_methods',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('paymentMethod')
            ->setAllowedTypes('paymentMethod', [PaymentMethodInterface::class])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'commerce_weavers_sylius_saferpay_payment_methods_configuration';
    }

    private function getAllowedPaymentMethodsData(PaymentMethodInterface $paymentMethod): array
    {
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        Assert::notNull($gatewayConfig);

        $configuration = $gatewayConfig->getConfig();
        if (isset($configuration['allowed_payment_methods']) && \is_array($configuration['allowed_payment_methods'])) {
            return $configuration['allowed_payment_methods'];
        }

        return $this->paymentMethodsProvider->provide($paymentMethod);
    }
}
