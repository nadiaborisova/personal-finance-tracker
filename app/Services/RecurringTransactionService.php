<?php 

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;

class RecurringTransactionService
{
    public function generateDue(): int
    {
        $count = 0;
        $today = Carbon::today();

        RecurringTransaction::where('is_active', true)
            ->where('next_due_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today);
            })
            ->each(function (RecurringTransaction $recurring) use (&$count) {
                [$start, $end] = $this->getPeriodRange($recurring);

                $alreadyExists = Transaction::where('user_id', $recurring->user_id)
                    ->where('category_id', $recurring->category_id)
                    ->where('type', $recurring->type)
                    ->where('amount', $recurring->amount)
                    ->whereBetween('transaction_date', [$start, $end])
                    ->exists();

                if (!$alreadyExists) {
                    Transaction::create([
                        'user_id'          => $recurring->user_id,
                        'category_id'      => $recurring->category_id,
                        'description'      => $recurring->description,
                        'amount'           => $recurring->amount,
                        'type'             => $recurring->type,
                        'transaction_date' => $recurring->next_due_date,
                    ]);

                    $count++;
                }

                $recurring->update([
                    'next_due_date' => $this->calculateNextDate($recurring),
                ]);
            });

        return $count;
    }

    public function create(array $data, int $userId): RecurringTransaction
    {
        $data['user_id']       = $userId;
        $data['next_due_date'] = $data['starts_at'];

        return RecurringTransaction::create($data);
    }

    public function update(RecurringTransaction $recurring, array $data): RecurringTransaction
    {
        $recurring->update($data);
        return $recurring->fresh();
    }

    public function delete(RecurringTransaction $recurring): void
    {
        $recurring->delete();
    }

    private function calculateNextDate(RecurringTransaction $recurring): Carbon
    {
        return match ($recurring->frequency) {
            'daily'   => $recurring->next_due_date->addDay(),
            'weekly'  => $recurring->next_due_date->addWeek(),
            'monthly' => $recurring->next_due_date->addMonth(),
            'yearly'  => $recurring->next_due_date->addYear(),
        };
    }
    private function getPeriodRange(RecurringTransaction $recurring): array
    {
        $date = $recurring->next_due_date;

        return match ($recurring->frequency) {
            'daily'   => [$date->copy(), $date->copy()],
            'weekly'  => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'monthly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'yearly'  => [$date->copy()->startOfYear(), $date->copy()->endOfYear()],
        };
    }
}