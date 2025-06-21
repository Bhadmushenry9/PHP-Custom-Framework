<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class InvoiceController
{
    public function index(Request $request): string
    {
        return View::make('layouts.invoices.index', [
            'invoices' => Invoice::with('user')->paginate(10),
            'title' => 'Invoices',
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
