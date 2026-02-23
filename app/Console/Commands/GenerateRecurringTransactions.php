<?php

namespace App\Console\Commands;

use App\Services\RecurringTransactionService;
use Illuminate\Console\Command;

class GenerateRecurringTransactions extends Command
{
    protected $signature   = 'transactions:generate-recurring';
    protected $description = 'Generate due recurring transactions';

    public function handle(RecurringTransactionService $service): void
    {
        $count = $service->generateDue();
        $this->info("Generated {$count} recurring transaction(s).");
    }
}
