<?php

declare(strict_types=1);

namespace CommerceWeavers\SyliusSaferpayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class SaferpayGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, ['label' => 'sylius.ui.username'])
            ->add('password', PasswordType::class, ['label' => 'sylius.ui.password'])
            ->add('customer_id', TextType::class, ['label' => 'sylius_saferpay.ui.customer_id'])
            ->add('terminal_id', TextType::class, ['label' => 'sylius_saferpay.ui.terminal_id'])
            ->add('use_authorize', HiddenType::class, ['data' => true])
        ;
    }
}
