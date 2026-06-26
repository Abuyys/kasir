<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Product;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShopOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Total Produk',
                Product::count()
            )
            ->description('Produk aktif')
            ->icon('heroicon-o-cube'),

            Stat::make(
                'Total Stok',
                Product::sum('stock')
            )
            ->description('Jumlah barang tersedia')
            ->icon('heroicon-o-archive-box'),

            Stat::make(
                'Stok Menipis',
                Product::whereColumn('stock', '<', 'min_stock')->count()
            )
            ->description('Perlu restock')
            ->color('danger')
            ->icon('heroicon-o-exclamation-triangle'),

            Stat::make(
                'Penjualan Hari Ini',
                'Rp ' . number_format(
                    Transaction::whereDate('created_at', today())->sum('final_price'),
                    0, ',', '.'
                )
            )
            ->icon('heroicon-o-banknotes'),
        ];
    }
}
