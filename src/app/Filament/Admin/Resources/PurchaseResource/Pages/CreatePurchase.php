<?php

namespace App\Filament\Admin\Resources\PurchaseResource\Pages;

use App\Filament\Admin\Resources\PurchaseResource;
use App\Models\Product;
use App\Models\StockMovement;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        
        // Calculate total_price
        $total = 0;
        if (isset($data['details'])) {
            foreach ($data['details'] as $detail) {
                $qty = (int)($detail['quantity'] ?? 0);
                $price = (float)($detail['purchase_price'] ?? 0);
                $total += $qty * $price;
            }
        }
        $data['total_price'] = $total;

        return $data;
    }

    protected function afterCreate(): void
    {
        $purchase = $this->record;

        foreach ($purchase->details as $detail) {
            $product = Product::find($detail->product_id);
            if ($product) {
                $before = $product->stock;
                
                // Increment product stock
                $product->increment('stock', $detail->quantity);
                
                // Record stock movement
                StockMovement::create([
                    'product_id'   => $product->id,
                    'user_id'      => auth()->id(),
                    'type'         => 'purchase',
                    'qty'          => $detail->quantity,
                    'before_stock' => $before,
                    'after_stock'  => $product->stock,
                    'notes'        => 'Pembelian masuk - ' . $purchase->purchase_code,
                ]);
            }
        }
    }
}
