<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Invoice;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\View;

class InvoiceController
{
    public function __construct(protected Container $container) {}

    public function index(): string
    {
        return View::make('layouts.invoices.index', [
            'title' => 'Invoices',
            'invoices' => Invoice::with('user')->get()
        ])->render();
    }

    public function create(): string
    {
        return "<form action='/invoices/store' method='post'>
                    Amount: <label for='amount'></label>
                    <input type='text' name='amount'>
                </form>";
    }

    public function store(): string
    {
        return $_POST['amount'] ?? 'No amount submitted';
    }
}
