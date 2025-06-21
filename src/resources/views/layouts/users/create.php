<?php

use App\Helpers\ViewHelper;
use App\Views\Components\FormInput;
use App\Views\Components\Alert;
use App\Enums\AlertType;

// Flash data from session (after redirect)
$session = session();
$success = $session->get('success');
$error = $session->get('error');
$errors = $session->get('errors', $errors ?? []);
$old = $session->get('old', $old ?? []);

ViewHelper::setTitle('Create User');
ViewHelper::startSection('content');
?>

<div class="max-w-xl mx-auto mt-10 bg-white shadow-md rounded-lg p-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Create User</h1>

    <?php if (!empty($success)): ?>
        <?= (new Alert(
            message: $success,
            type: AlertType::Success
        ))->render(); ?>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <?= (new Alert(
            message: $error,
            type: AlertType::Error
        ))->render(); ?>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-300 rounded">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                <?php foreach ($errors as $fieldErrors): ?>
                    <?php foreach ((array) $fieldErrors as $errorMessage): ?>
                        <li><?= htmlspecialchars($errorMessage) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="/users/store" method="POST" class="space-y-4">
        <?= csrf_field() ?>

        <?= (new FormInput(
            name: 'name',
            label: 'Name',
            value: $old['name'] ?? '',
            required: true,
            error: $errors['name'][0] ?? null
        ))->render(); ?>

        <?= (new FormInput(
            name: 'email',
            label: 'Email',
            value: $old['email'] ?? '',
            required: true,
            error: $errors['email'][0] ?? null
        ))->render(); ?>

        <?= (new FormInput(
            name: 'password',
            label: 'Password',
            type: 'password',
            required: true,
            error: $errors['password'][0] ?? null
        ))->render(); ?>

        <div class="pt-4">
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1">
                Create Account
            </button>
        </div>
    </form>
</div>

<?php
ViewHelper::endSection();
echo ViewHelper::renderLayout();
