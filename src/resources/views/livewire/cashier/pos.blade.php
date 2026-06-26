<div>
    {{-- ========== SUCCESS: Show Receipt Link ========== --}}
    @if($lastTransactionId)
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-900 rounded-lg flex items-center justify-between shadow-sm animate-pulse">
            <div>
                <p class="font-semibold text-emerald-800 dark:text-emerald-300">✅ Transaksi berhasil!</p>
                <p class="text-sm text-emerald-600 dark:text-emerald-400">Klik tombol di kanan untuk mencetak struk belanja.</p>
            </div>
            <a href="{{ route('receipt.show', $lastTransactionId) }}"
               target="_blank"
               class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition">
                🖨️ Cetak Struk
            </a>
        </div>
    @endif

    {{-- Error Banner --}}
    @error('barcode')
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950 border border-rose-200 dark:border-rose-900 rounded-lg">
            <p class="text-sm text-rose-600 dark:text-rose-400 font-semibold">{{ $message }}</p>
        </div>
    @enderror

    <div class="grid grid-cols-12 gap-6">

        {{-- ========== LEFT: Product Catalog ========== --}}
        <div class="col-span-12 lg:col-span-7">
            <x-filament::section>
                <x-slot name="heading">Katalog Produk</x-slot>

                {{-- Search and Category Filters --}}
                <div class="space-y-4 mb-6">
                    <div class="flex gap-2">
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                            placeholder="Cari nama produk atau scan barcode...">
                        
                        <input
                            wire:model="barcode"
                            wire:keydown.enter="searchProduct"
                            type="text"
                            class="w-40 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-gray-900 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                            placeholder="Barcode Enter">
                    </div>

                    {{-- Category Tabs --}}
                    <div class="flex flex-wrap gap-1.5 pb-2 border-b border-gray-100 dark:border-gray-700 overflow-x-auto">
                        <button
                            wire:click="selectCategory(null)"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition {{ is_null($selectedCategory) ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                            Semua Kategori
                        </button>
                        @foreach($categories as $category)
                            <button
                                wire:click="selectCategory({{ $category->id }})"
                                class="px-3 py-1.5 rounded-full text-xs font-semibold transition {{ $selectedCategory == $category->id ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200' }}">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Loading Indicator --}}
                <div wire:loading wire:target="search, selectedCategory" class="w-full text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-amber-500 border-t-transparent"></div>
                    <p class="text-sm text-gray-500 mt-2">Memuat produk...</p>
                </div>

                {{-- Product Grid --}}
                <div wire:loading.remove wire:target="search, selectedCategory" class="grid grid-cols-2 sm:grid-cols-3 gap-4 max-h-[600px] overflow-y-auto pr-1">
                    @forelse($products as $prod)
                        <div 
                            wire:click="addToCartById({{ $prod->id }})"
                            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-3 shadow-sm hover:shadow-md hover:border-amber-400 dark:hover:border-amber-500 transition cursor-pointer flex flex-col justify-between group">
                            
                            {{-- Image --}}
                            <div class="aspect-video w-full rounded-lg bg-gray-50 dark:bg-gray-900 mb-3 overflow-hidden flex items-center justify-center relative">
                                @if($prod->image)
                                    <img src="{{ asset('storage/' . $prod->image) }}" class="object-cover w-full h-full group-hover:scale-105 transition duration-300" alt="{{ $prod->name }}">
                                @else
                                    <span class="text-3xl text-gray-300 dark:text-gray-600">📦</span>
                                @endif

                                @if($prod->stock <= $prod->min_stock)
                                    <span class="absolute top-1 right-1 bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow">
                                        Limit
                                    </span>
                                @endif
                            </div>

                            {{-- Product Info --}}
                            <div>
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-0.5">{{ $prod->category->name }}</p>
                                <h4 class="text-sm font-bold text-gray-800 dark:text-white line-clamp-2 leading-snug min-h-[2.5rem]">
                                    {{ $prod->name }}
                                </h4>
                            </div>

                            <div class="mt-3 flex items-center justify-between border-t border-gray-50 dark:border-gray-750 pt-2">
                                <div>
                                    <p class="text-xs text-gray-500">Stok: <span class="font-semibold {{ $prod->stock <= 0 ? 'text-rose-500' : 'text-gray-700 dark:text-gray-300' }}">{{ $prod->stock }}</span></p>
                                    <p class="text-sm font-extrabold text-amber-600 dark:text-amber-400">
                                        Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                                    </p>
                                </div>
                                <button class="w-8 h-8 rounded-full bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400 group-hover:bg-amber-500 group-hover:text-white font-bold flex items-center justify-center shadow-sm transition">
                                    +
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-400">
                            <p class="text-4xl mb-2">🔍</p>
                            <p class="text-sm">Produk tidak ditemukan atau tidak aktif</p>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        {{-- ========== RIGHT: Cart & Checkout ========== --}}
        <div class="col-span-12 lg:col-span-5">
            <x-filament::section>
                <x-slot name="heading">Keranjang Belanja</x-slot>

                {{-- Cart Items --}}
                <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-72 overflow-y-auto mb-4 pr-1">
                    @forelse($cart as $item)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex-1 min-w-0 pr-2">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                    {{ $item['name'] }}
                                </p>
                                <p class="text-xs text-amber-600 font-semibold">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                </p>
                            </div>

                            {{-- Qty Controls --}}
                            <div class="flex items-center space-x-1.5">
                                <button wire:click="decrement({{ $item['id'] }})"
                                    class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white font-bold flex items-center justify-center hover:bg-gray-200 transition">
                                    −
                                </button>
                                <input 
                                    type="number" 
                                    value="{{ $item['qty'] }}" 
                                    wire:change="updateQty({{ $item['id'] }}, $event.target.value)"
                                    class="w-12 h-6 text-center text-xs font-semibold rounded border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-white p-0">
                                <button wire:click="increment({{ $item['id'] }})"
                                    class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white font-bold flex items-center justify-center hover:bg-gray-200 transition">
                                    +
                                </button>
                            </div>

                            {{-- Subtotal & Remove --}}
                            <div class="flex items-center space-x-2 ml-4">
                                <span class="text-sm font-extrabold text-gray-900 dark:text-white whitespace-nowrap">
                                    Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                                </span>
                                <button wire:click="remove({{ $item['id'] }})"
                                    class="text-rose-500 hover:text-rose-700 font-bold text-xl leading-none">
                                    ×
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16 text-gray-400">
                            <p class="text-4xl mb-2">🛒</p>
                            <p class="text-sm">Belum ada item di keranjang</p>
                        </div>
                    @endforelse
                </div>

                {{-- Checkout Calculations --}}
                @if(count($cart) > 0)
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-4">
                        
                        {{-- Subtotal --}}
                        <div class="flex justify-between text-sm font-semibold text-gray-600 dark:text-gray-400">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($this->total(), 0, ',', '.') }}</span>
                        </div>

                        {{-- Discount --}}
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Tipe Diskon</label>
                                <select 
                                    wire:model.live="discountType"
                                    class="w-full text-xs rounded-lg border-gray-300 p-1.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                    <option value="nominal">Nominal (Rp)</option>
                                    <option value="percent">Persentase (%)</option>
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Nilai Diskon</label>
                                <input
                                    wire:model.live="discountValue"
                                    type="number"
                                    class="w-full text-xs rounded-lg border-gray-300 p-1.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                                    placeholder="0">
                            </div>
                        </div>

                        {{-- Total --}}
                        <div class="flex justify-between text-xl font-black text-gray-900 dark:text-white border-t border-dashed border-gray-200 dark:border-gray-700 pt-3">
                            <span>Total Akhir</span>
                            <span class="text-amber-600 dark:text-amber-400">Rp {{ number_format($this->finalTotal(), 0, ',', '.') }}</span>
                        </div>

                        {{-- Payment Input --}}
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Uang Bayar (Rp)</label>
                            <input
                                wire:model.live="paid"
                                type="number"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white font-extrabold text-gray-900"
                                placeholder="Masukkan jumlah pembayaran...">
                        </div>

                        {{-- Change Amount --}}
                        @if($paid > 0)
                            <div class="flex justify-between text-sm font-semibold border-t border-gray-100 dark:border-gray-800 pt-2">
                                <span class="text-gray-500">Kembalian</span>
                                <span class="{{ ($paid - $this->finalTotal() < 0) ? 'text-rose-600' : 'text-emerald-600' }} font-bold">
                                    Rp {{ number_format($paid - $this->finalTotal(), 0, ',', '.') }}
                                </span>
                            </div>
                        @endif

                        {{-- Submit Button --}}
                        <x-filament::button
                            wire:click="checkout"
                            color="warning"
                            class="w-full justify-center py-2.5 font-bold"
                            :disabled="(float)$paid < $this->finalTotal()">
                            💳 Bayar & Cetak Struk
                        </x-filament::button>
                    </div>
                @endif
            </x-filament::section>
        </div>

    </div>
</div>