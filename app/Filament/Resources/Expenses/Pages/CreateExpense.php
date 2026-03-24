<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Models\Expense;
use Filament\Actions\ExportAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Filament\Exports\ExpenseExporter;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\Expenses\ExpenseResource;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $account = \App\Models\Account::findOrFail($data['account_id']);
            $amount  = (float) $data['amount'];

            // Final backend safety check
            if ($amount > $account->balance) {
                throw ValidationException::withMessages([
                    'amount' => "Insufficient balance in {$account->name}. Available: TSH {$account->balance}",
                ]);
            }

            // Create the expense record
            $expense = Expense::create([
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'amount'      => $amount,
                'account_id'  => $account->id,
                'store_id'    => $data['store_id'],
                'incurred_on' => $data['incurred_on'] ?? now(),
                'created_by'  => Auth::id(),
                'updated_by'  => Auth::id(),
            ]);

            // Decrease account balance
            $account->decrement('balance', $amount);

            return $expense;
        });



    }



    protected function getCreatedNotification(): ?Notification
{
    return Notification::make()
        ->success()
        ->title('Expense recorded')
        ->body('Account balance updated successfully.');
}





}
