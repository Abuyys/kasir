<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'barcode',
        'name',
        'description',
        'image',
        'purchase_price',
        'selling_price',
        'stock',
        'min_stock',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

     public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getProfitAttribute(): float
    {
        return $this->selling_price - $this->purchase_price;
    }

    public function transactionDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    protected static function booted()
    {
        static::updated(function ($product) {
            if ($product->is_active && $product->stock <= $product->min_stock) {
                $owners = \App\Models\User::where('role', 'owner')->get();
                foreach ($owners as $owner) {
                    \Filament\Notifications\Notification::make()
                        ->title('Peringatan Stok Menipis!')
                        ->body("Stok produk {$product->name} tersisa {$product->stock} (Batas Min: {$product->min_stock})")
                        ->warning()
                        ->sendToDatabase($owner)
                        ->send();
                }
            }
        });
    }
}
