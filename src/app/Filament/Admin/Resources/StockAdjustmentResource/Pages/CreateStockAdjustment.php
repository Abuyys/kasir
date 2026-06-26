<?php

namespace App\Filament\Admin\Resources\StockAdjustmentResource\Pages;

use App\Filament\Admin\Resources\StockAdjustmentResource;
use App\Models\Product;
use App\Models\StockMovement;
use Filament\Resources\Pages\CreateRecord;

class CreateStockAdjustment extends CreateRecord
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $adjustment = $this->record;
        $product = Product::find($adjustment->product_id);

        if ($product) {
            $before = $product->stock;
            $qty = $adjustment->quantity;

            if ($adjustment->type === 'in') {
                $product->increment('stock', $qty);
                $after = $before + $qty;
                $movType = 'adjustment_in';
            } else {
                $product->decrement('stock', $qty);
                $after = $before - $qty;
                $movType = 'adjustment_out';
            }

            StockMovement::create([
                'product_id'   => $product->id,
                'user_id'      => auth()->id(),
                'type'         => $movType,
                'qty'          => $qty,
                'before_stock' => $before,
                'after_stock'  => $after,
                'notes'        => 'Adjustment: ' . $adjustment->notes,
            ]);
        }
    }
}
