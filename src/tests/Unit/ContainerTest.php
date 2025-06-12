<?php

use PHPUnit\Framework\TestCase;
use App\Core\Container;
use App\Services\InvoiceService;
use App\Services\SalesTaxService;
use App\Services\EmailService;
use App\Interface\PaymentGatewayInterface;
use App\Exception\ContainerException;

final class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
    }
    
    public function test_container_can_resolve_invoice_service_with_dependencies()
    {
        // Bind SalesTaxService
        $this->container->bind(SalesTaxService::class, fn () => new class extends SalesTaxService {
            public function calculate(array $customer, float $amount): float
            {
                return 15.0;
            }
        });

        // Bind EmailService
        $this->container->bind(EmailService::class, fn () => new class extends EmailService {
            public function send(array $customer, string $template): bool {
                return (bool) mt_rand(0, 1);
            }
        });

        // Mock and bind PaymentGatewayInterface
        $mockPayment = $this->createMock(PaymentGatewayInterface::class);
        $mockPayment->method('charge')->willReturn(true);
        $this->container->bind(PaymentGatewayInterface::class, fn () => $mockPayment);

        // Resolve InvoiceService
        $invoiceService = $this->container->get(InvoiceService::class);

        $this->assertInstanceOf(InvoiceService::class, $invoiceService);

        // Ensure process method works and returns true
        $result = $invoiceService->process(['name' => 'Test'], 100.0);
        $this->assertTrue($result);
    }

    public function test_container_throws_exception_if_interface_is_not_bound()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('is not instantiable');

        // Only bind EmailService and SalesTaxService
        $this->container->bind(SalesTaxService::class, fn () => new SalesTaxService());
        $this->container->bind(EmailService::class, fn () => new EmailService());

        // Do NOT bind PaymentGatewayInterface — this should cause an exception
        $this->container->get(InvoiceService::class);
    }

    public function test_container_can_resolve_simple_class()
    {
        $this->container->bind(Foo::class, Foo::class);

        $instance = $this->container->get(Foo::class);

        $this->assertInstanceOf(Foo::class, $instance);
    }

    public function test_container_can_resolve_callable()
    {
        $this->container->bind('message', fn () => 'Hello Container');

        $this->assertEquals('Hello Container', $this->container->get('message'));
    }
}

// Support classes for generic tests
class Foo
{
    public function say(): string
    {
        return 'bar';
    }
}
