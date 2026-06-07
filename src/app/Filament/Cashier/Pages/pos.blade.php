<x-filament-panels::page>
    <div class="grid grid-cols-12 gap-4">

        <div class="col-span-8">
            <x-filament::section>
                <x-slot name="heading">
                    Cari Barang
                </x-slot>

                <input
                    type="text"
                    class="w-full rounded-lg border-gray-300"
                    placeholder="Scan Barcode atau Nama Barang">
            </x-filament::section>
        </div>

        <div class="col-span-4">
            <x-filament::section>
                <x-slot name="heading">
                    Keranjang
                </x-slot>

                <div class="space-y-2">
                    <div>Total: Rp 0</div>

                    <x-filament::button>
                        Bayar
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>

    </div>
</x-filament-panels::page>