<?php

namespace App\Filament\Admin\Resources\StockOpnameResource\Pages;

use App\Filament\Admin\Resources\StockOpnameResource;
use App\Models\Product;
use App\Models\StockMovement;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStockOpname extends CreateRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        $product = Product::find($record->product_id);
        $before = $product->stock;

        $product->update([
            'stock' => $record->physical_stock,
        ]);

        StockMovement::create([
            'product_id'   => $product->id,
            'user_id'      => auth()->id(),
            'type'         => 'opname',
            'qty'          => $record->difference,
            'before_stock' => $before,
            'after_stock'  => $record->physical_stock,
            'notes'        => 'Stock opname',
        ]);
    }
}
