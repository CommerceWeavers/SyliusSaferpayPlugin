<?php

declare(strict_types=1);

namespace Tests\CommerceWeavers\SyliusSaferpayPlugin\Unit\Payum\ValueObject;

use CommerceWeavers\SyliusSaferpayPlugin\Payum\ValueObject\SaferpayApi;
use PHPUnit\Framework\TestCase;

final class SaferpayApiTest extends TestCase
{
    private SaferpayApi $saferpayApi;

    protected function setUp(): void
    {
        $this->saferpayApi = new SaferpayApi('username', 'password', 'customer_id', 'terminal_id', true);
    }

    public function test_it_returns_a_username(): void
    {
        self::assertSame('username', $this->saferpayApi->getUsername());
    }

    public function test_it_returns_a_password(): void
    {
        self::assertSame('password', $this->saferpayApi->getPassword());
    }

    public function test_it_returns_a_customer_id(): void
    {
        self::assertSame('customer_id', $this->saferpayApi->getCustomerId());
    }

    public function test_it_returns_a_terminal_id(): void
    {
        self::assertSame('terminal_id', $this->saferpayApi->getTerminalId());
    }

    public function test_it_returns_if_it_is_a_sandbox(): void
    {
        self::assertTrue($this->saferpayApi->isSandbox());
    }
}
