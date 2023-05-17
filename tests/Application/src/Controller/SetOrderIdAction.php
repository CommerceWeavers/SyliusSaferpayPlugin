<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Application\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetOrderIdAction
{
    public function __invoke(Request $request, int $orderId): Response
    {
        $request->getSession()->set('sylius_order_id', $orderId);

        return new Response();
    }
}
