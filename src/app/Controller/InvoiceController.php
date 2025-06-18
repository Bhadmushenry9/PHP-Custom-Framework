<?php
declare(strict_types=1);
namespace App\Controller;

use App\Model\Invoice;
use App\View;
use Illuminate\Container\Container;

class InvoiceController
{
    public function __construct(protected Container $container) {
    }
    public function index():View
    {
        return View::make('layouts.invoices.index', ['title' => 'Invoices', 'invoices' => Invoice::with('user')->get()]);
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
