<x-filament-panels::page>

    {{-- Period Filter & Export --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex flex-wrap gap-2">
            @foreach(['today' => 'Hari Ini', 'this_week' => 'Minggu Ini', 'this_month' => 'Bulan Ini', 'custom' => 'Custom'] as $value => $label)
                <button
                    wire:click="$set('period', '{{ $value }}')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition
                        {{ $period === $value
                            ? 'bg-primary-600 text-white'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <button
            wire:click="exportCSV"
            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition flex items-center gap-1 shadow-sm">
            📥 Export CSV
        </button>
    </div>

    {{-- Custom Date Range --}}
    @if($period === 'custom')
        <div class="flex gap-4 mb-6">
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Dari Tanggal</label>
                <input wire:model.live="dateFrom" type="date"
                    class="rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-500 uppercase block mb-1">Sampai Tanggal</label>
                <input wire:model.live="dateTo" type="date"
                    class="rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm">
            </div>
        </div>
    @endif

    {{-- Stats Cards --}}
    @php $stats = $this->getStats(); @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Total Penjualan</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Jumlah Transaksi</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_orders'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Rata-rata per Transaksi</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($stats['average_order'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Transaction List --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Daftar Transaksi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kasir</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Struk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($this->getTransactions() as $trx)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-4 py-3 font-mono text-xs text-gray-900 dark:text-white">{{ $trx->transaction_code }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $trx->user->name }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($trx->final_price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">{{ $trx->created_at->format('d/m H:i') }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('receipt.show', $trx) }}" target="_blank"
                                        class="text-xs text-primary-600 hover:underline">Cetak</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi di periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Products --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Produk Terlaris</h3>
            </div>
            <div class="p-4 space-y-3">
                @forelse($this->getTopProducts() as $i => $product)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-600 dark:text-primary-300 text-xs font-bold flex items-center justify-center">
                                {{ $i + 1 }}
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ \App\Models\Product::find($product['product_id'])?->name ?? 'Unknown' }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $product['total_qty'] }} terjual</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">
                            Rp {{ number_format($product['total_revenue'], 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <p class="text-center text-gray-400 text-sm py-4">Belum ada data</p>
                @endforelse
            </div>
        </div>
    </div>

</x-filament-panels::page>
