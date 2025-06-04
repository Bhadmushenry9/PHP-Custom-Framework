<?php
declare(strict_types=1);
namespace App\Controllers;

use App\View;

class InvoiceController
{
    public function index():View
    {
        return View::make('layouts/invoices/index', ['title' => 'Invoices']);
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
