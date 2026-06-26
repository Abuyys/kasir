<?php

namespace App\Filament\Admin\Widgets;

use App\Models\TransactionDetail;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Filament\Tables\Table;

class BestSeller extends BaseWidget
{
    protected static ?string $heading = 'Produk Terlaris';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransactionDetail::query()
                    ->selectRaw('product_id, SUM(quantity) as total')
                    ->groupBy('product_id')
                    ->orderByDesc('total')
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Terjual'),
            ]);
    }

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return (string) $record->product_id;
    }
}
