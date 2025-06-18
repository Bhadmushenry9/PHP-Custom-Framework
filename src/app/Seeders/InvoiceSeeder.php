<?php

declare(strict_types=1);

namespace App\Seeders;

use App\Enums\InvoiceStatus;
use App\Model\Invoice;
use App\Model\User;
class InvoiceSeeder
{
    public static function run(int $count = 10): void
    {
        try {
            $statuses = [
                InvoiceStatus::Pending,
                InvoiceStatus::Paid,
                InvoiceStatus::Void,
                InvoiceStatus::Failed,
            ];

            // Fetch all user IDs once
            $userIds = User::select('id')->where('is_active', 1)->get();

            if (empty($userIds)) {
                throw new \RuntimeException("No users found to assign invoices to.");
            }

            for ($i = 1; $i <= $count; $i++) {
                $randomStatus = $statuses[array_rand($statuses)];
                $randomUserIdArray = $userIds[array_rand($userIds)];
                $randomUserId = is_array($randomUserIdArray) ? $randomUserIdArray['id'] : $randomUserIdArray;

                Invoice::create([
                    'amount' => rand(1000, 100000) / 100,
                    'status' => $randomStatus->value,
                    'user_id' => $randomUserId,
                ]);
            }
        } catch (\Exception $e) {
            echo "❌ InvoiceSeeder failed: {$e->getMessage()}\n";
        }
    }
}
