<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Invoice;
use App\Models\SignUp;
use App\Models\User;
use App\View;

class HomeController
{
    public function index(): View
    {
        $email = 'hbtechng@gmail.com';
        $fullName = 'Bhadmus Henry';
        $amount = 200;

        $userModel = new User();
        $invoiceModel = new Invoice();

        // $invoiceId = (new SignUp($userModel, $invoiceModel))->register(
        //     [
        //         'email' => $email,
        //         'full_name' => $fullName,
        //         'is_active' => true
        //     ]
        //     ,[
        //         'amount' => $amount
        //     ]
        // );
        return View::make('layouts/home/index', ['invoice' => $invoiceModel->with('user')->findOrFail('26525d2d-0608-4601-b25b-57a0239d2bf6')]);
    }
}
