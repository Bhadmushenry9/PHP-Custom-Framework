<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class UserController
{
    public function index(Request $request): string
    {
        return View::make('layouts.users.index', [
            'users' => User::paginate(10),
            'title' => 'Users',
        ])->render();
    }

    public function create(): string
    {
        return View::make('layouts.users.create', [
            'errors' => [],
            'success' => '',
            'old' => [],
        ])->render();
    }

    public function store(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            session()->flash('errors', $validator->errors()->toArray());
            session()->flash('old', $request->all());

            return Redirect::back();
        }

        try {
            User::create([
                'full_name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => password_hash($request->input('password'), PASSWORD_DEFAULT),
            ]);

            session()->flash('success', 'User created successfully.');
            return Redirect::back();

        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while creating the user.');
            session()->flash('old', $request->all());

            return Redirect::back();
        }
    }

}
