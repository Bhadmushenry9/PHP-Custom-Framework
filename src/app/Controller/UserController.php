<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\User;
use App\Http\Request;
use App\Services\Validator;

class UserController
{
    public function index(): array
    {
        return User::all();
    }

    public function show(string $id): array
    {
        $user = User::find($id);

        if (!$user) {
            http_response_code(404);
            return ['error' => 'User not found'];
        }

        return (array)$user;
    }

    public function store(): array
    {
        $data = Request::json();

        $validated = $this->validate($data, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $id = User::create($data);

        http_response_code(201);
        return ['message' => 'User created', 'id' => $id];
    }

    public function update(string $id): array
    {
        $user = User::find($id);
        if (!$user) {
            http_response_code(404);
            return ['error' => 'User not found'];
        }

        $data = Request::json();

        $validated = $this->validate($data, [
            'name' => 'sometimes|required',
            'email' => 'sometimes|required|email',
            'password' => 'nullable|min:6',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        User::update((array)$id, $data);

        return ['message' => 'User updated'];
    }

    public function destroy(string $id): array
    {
        $user = User::find($id);
        if (!$user) {
            http_response_code(404);
            return ['error' => 'User not found'];
        }

        User::delete($id);
        return ['message' => 'User deleted'];
    }

    private function validate(array $data, array $rules): array
    {
        $validator = new Validator($data, $rules);

        if ($validator->fails()) {
            http_response_code(422);
            // Here you can customize the error response structure
            echo json_encode(['errors' => $validator->errors()]);
            exit; // stop further execution on validation error
        }

        return $validator->validated(); // returns filtered & valid data
    }
}
