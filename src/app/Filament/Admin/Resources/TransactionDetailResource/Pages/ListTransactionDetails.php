<?php

namespace App\Filament\Admin\Resources\TransactionDetailResource\Pages;

use App\Filament\Admin\Resources\TransactionDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactionDetails extends ListRecords
{
    protected static string $resource = TransactionDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
