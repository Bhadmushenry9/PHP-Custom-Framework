<?php
declare(strict_types=1);
namespace App\Controller;

use App\Core\Container;
use App\Model\Invoice;
use App\Services\InvoiceService;
use App\View;

class InvoiceController
{
    public function __construct(protected Container $container) {
    }
    public function index():View
    {
        //$this->container->get(InvoiceService::class)->process([], 25);
        return View::make('layouts.invoices.index', ['title' => 'Invoices', 'invoices' => (new Invoice)->with('user')->all()]);
    }

    public function create(): string
    {
        return "<form action='/invoices/store' method='post'>Amount: <label for='amount'></label><input type='text' name='amount'></form>";
    }

    public function store(): string
    {
        return $_POST['amount'];
    }
}
