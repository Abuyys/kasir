<?php

namespace App\Filament\Cashier\Resources\TransactionResource\Pages;

use App\Filament\Cashier\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;
}
