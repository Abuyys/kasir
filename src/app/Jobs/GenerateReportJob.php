<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $period;

    /**
     * Create a new job instance.
     */
    public function __construct(string $period = 'today')
    {
        $this->period = $period;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query = Transaction::with(['user', 'details'])
            ->where('status', 'success')
            ->latest();

        if ($this->period === 'today') {
            $query->whereDate('created_at', today());
        }

        $data = $query->get();
        $csvHeader = ['Kode Transaksi', 'Kasir', 'Total Kotor (Rp)', 'Diskon (Rp)', 'Total Bersih (Rp)', 'Uang Bayar (Rp)', 'Kembalian (Rp)', 'Waktu'];
        
        $tempFile = tempnam(sys_get_temp_dir(), 'report_');
        $file = fopen($tempFile, 'w');
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

        $filename = 'reports/laporan-' . $this->period . '-' . now()->format('YmdHis') . '.csv';
        Storage::disk('local')->put($filename, file_get_contents($tempFile));
        unlink($tempFile);
    }
}
