<?php

namespace App\Filament\Admin\Pages;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Support\Carbon;

class SalesReport extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?string $title = 'Laporan Penjualan';
    protected static string $view = 'filament.admin.pages.sales-report';

    public string $period = 'today';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function mount(): void
    {
        $this->dateFrom = Carbon::today()->toDateString();
        $this->dateTo   = Carbon::today()->toDateString();
    }

    public function getStats(): array
    {
        $query = Transaction::where('status', 'success');
        $query = $this->applyPeriodFilter($query);

        $total   = $query->sum('final_price');
        $count   = $query->count();
        $average = $count > 0 ? $total / $count : 0;

        return [
            'total_sales'    => $total,
            'total_orders'   => $count,
            'average_order'  => $average,
        ];
    }

    public function getTransactions()
    {
        $query = Transaction::with(['user', 'details'])
            ->where('status', 'success')
            ->latest();

        return $this->applyPeriodFilter($query)->paginate(15);
    }

    public function getTopProducts(): array
    {
        $query = TransactionDetail::query()
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'success');

        if ($this->period === 'custom') {
            if ($this->dateFrom) {
                $query->whereDate('transactions.created_at', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $query->whereDate('transactions.created_at', '<=', $this->dateTo);
            }
        } elseif ($this->period === 'today') {
            $query->whereDate('transactions.created_at', Carbon::today());
        } elseif ($this->period === 'this_week') {
            $query->whereBetween('transactions.created_at', [Carbon::startOfWeek(), Carbon::endOfWeek()]);
        } elseif ($this->period === 'this_month') {
            $query->whereMonth('transactions.created_at', Carbon::now()->month)
                  ->whereYear('transactions.created_at', Carbon::now()->year);
        }

        return $query
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function applyPeriodFilter($query)
    {
        return match($this->period) {
            'today'      => $query->whereDate('created_at', Carbon::today()),
            'this_week'  => $query->whereBetween('created_at', [Carbon::startOfWeek(), Carbon::endOfWeek()]),
            'this_month' => $query->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year),
            'custom'     => $query->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                                  ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo)),
            default      => $query,
        };
    }

    public function exportCSV()
    {
        $query = Transaction::with(['user', 'details'])
            ->where('status', 'success')
            ->latest();
        $query = $this->applyPeriodFilter($query);
        $data = $query->get();

        $csvHeader = ['Kode Transaksi', 'Kasir', 'Total Kotor (Rp)', 'Diskon (Rp)', 'Total Bersih (Rp)', 'Uang Bayar (Rp)', 'Kembalian (Rp)', 'Waktu'];
        
        $callback = function() use ($data, $csvHeader) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $csvHeader);
            
            foreach ($data as $trx) {
                fputcsv($file, [
                    $trx->transaction_code,
                    $trx->user->name,
                    $trx->total_price,
                    $trx->discount,
                    $trx->final_price,
                    $trx->paid_amount,
                    $trx->change_amount,
                    $trx->created_at->toDateTimeString()
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan-penjualan-' . $this->period . '-' . now()->format('YmdHis') . '.csv"',
        ]);
    }
}
