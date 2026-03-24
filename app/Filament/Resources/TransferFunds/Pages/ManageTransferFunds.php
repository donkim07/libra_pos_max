<?php

namespace App\Filament\Resources\TransferFunds\Pages;

use App\Models\Account;
use App\Models\TransferFunds;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\TransferFunds\TransferFundsResource;

class ManageTransferFunds extends ManageRecords
{
    protected static string $resource = TransferFundsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    // Optional: any pre-save tweaks
                    return $data;
                })
                ->using(function (array $data) {
                    return DB::transaction(function () use ($data) {
                        $fromAccount = Account::findOrFail($data['from_account_id']);
                        $toAccount   = Account::findOrFail($data['to_account_id']);
                        $amount      = (float) $data['amount'];

                        // Final backend check (in case frontend bypassed)
                        if ($amount > $fromAccount->balance) {
                            throw ValidationException::withMessages([
                                'amount' => "Insufficient balance in {$fromAccount->name}. Available: {$fromAccount->balance}",
                            ]);
                        }

                        if ($fromAccount->id === $toAccount->id) {
                            throw ValidationException::withMessages([
                                'to_account_id' => 'Cannot transfer to the same account.',
                            ]);
                        }

                        // Create record
                        $transfer = TransferFunds::create([
                            'from_account_id' => $fromAccount->id,
                            'to_account_id'   => $toAccount->id,
                            'amount'          => $amount,
                            'note'            => $data['note'] ?? null,
                            'created_by'      => Auth::id(),
                        ]);

                        // Update balances
                        $fromAccount->decrement('balance', $amount);
                        $toAccount->increment('balance', $amount);

                        return $transfer;
                    });
                }),
        ];
    }
}
