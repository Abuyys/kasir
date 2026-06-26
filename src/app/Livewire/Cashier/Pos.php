<?php

namespace App\Livewire\Cashier;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\StockMovement;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class Pos extends Component
{
    public $barcode;
    public $cart = [];
    public $paid = 0;
    public $lastTransactionId = null;

    // Search and filters
    public $search = '';
    public $selectedCategory = null;

    // Discounts
    public $discountType = 'nominal'; // 'percent' or 'nominal'
    public $discountValue = 0;

    protected $listeners = ['refreshPos' => '$refresh'];

    public function searchProduct()
    {
        $product = Product::where('barcode', $this->barcode)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            $this->addError('barcode', 'Produk tidak ditemukan');
            return;
        }

        if ($product->stock <= 0) {
            $this->addError('barcode', 'Stok habis');
            return;
        }

        $this->addToCart($product);
        $this->barcode = '';
    }

    public function addToCart($product)
    {
        $currentQty = isset($this->cart[$product->id]) ? $this->cart[$product->id]['qty'] : 0;
        
        if ($currentQty + 1 > $product->stock) {
            $this->addError('barcode', 'Stok tidak mencukupi untuk ' . $product->name);
            return;
        }

        if (isset($this->cart[$product->id])) {
            $this->cart[$product->id]['qty']++;
        } else {
            $this->cart[$product->id] = [
                'id'    => $product->id,
                'name'  => $product->name,
                'price' => (float)$product->selling_price,
                'qty'   => 1,
            ];
        }
        $this->resetErrorBag();
    }

    public function addToCartById($productId)
    {
        $product = Product::where('is_active', true)->find($productId);
        if ($product) {
            $this->addToCart($product);
        }
    }

    public function increment($id)
    {
        if (isset($this->cart[$id])) {
            $product = Product::find($id);
            if ($product && $this->cart[$id]['qty'] + 1 > $product->stock) {
                $this->addError('barcode', 'Stok tidak mencukupi untuk ' . $product->name);
                return;
            }
            $this->cart[$id]['qty']++;
            $this->resetErrorBag();
        }
    }

    public function decrement($id)
    {
        if (isset($this->cart[$id])) {
            if ($this->cart[$id]['qty'] <= 1) {
                $this->remove($id);
            } else {
                $this->cart[$id]['qty']--;
            }
            $this->resetErrorBag();
        }
    }

    public function updateQty($id, $qty)
    {
        $qty = (int)$qty;
        if ($qty <= 0) {
            $this->remove($id);
            return;
        }

        $product = Product::find($id);
        if ($product && $qty > $product->stock) {
            $this->addError('barcode', 'Stok tidak mencukupi untuk ' . $product->name . ' (Sisa: ' . $product->stock . ')');
            $this->cart[$id]['qty'] = $product->stock;
            return;
        }

        if (isset($this->cart[$id])) {
            $this->cart[$id]['qty'] = $qty;
        }
        $this->resetErrorBag();
    }

    public function remove($id)
    {
        unset($this->cart[$id]);
        $this->resetErrorBag();
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
    }

    public function total()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
    }

    public function discountAmount()
    {
        $totalPrice = $this->total();
        if ($this->discountType === 'percent') {
            return $totalPrice * ((float)$this->discountValue / 100);
        }
        return (float)$this->discountValue;
    }

    public function finalTotal()
    {
        return max(0, $this->total() - $this->discountAmount());
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            $this->addError('barcode', 'Keranjang masih kosong');
            return;
        }

        $finalPrice = $this->finalTotal();
        if ($this->paid < $finalPrice) {
            $this->addError('barcode', 'Uang bayar kurang dari total belanja');
            return;
        }

        $transactionId = null;

        DB::transaction(function () use (&$transactionId, $finalPrice) {
            $transaction = Transaction::create([
                'transaction_code' => 'TRX-' . strtoupper(uniqid()),
                'user_id'          => auth()->id(),
                'total_price'      => $this->total(),
                'discount_type'    => $this->discountType,
                'discount_value'   => $this->discountValue,
                'discount_amount'  => $this->discountAmount(),
                'final_price'      => $finalPrice,
                'paid_amount'      => $this->paid,
                'change_amount'    => $this->paid - $finalPrice,
                'status'           => 'success',
            ]);

            foreach ($this->cart as $item) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $item['id'],
                    'quantity'       => $item['qty'],
                    'selling_price'  => $item['price'],
                    'subtotal'       => $item['price'] * $item['qty'],
                ]);

                $product = Product::find($item['id']);
                $before  = $product->stock;
                $product->decrement('stock', $item['qty']);

                StockMovement::create([
                    'product_id'   => $product->id,
                    'user_id'      => auth()->id(),
                    'type'         => 'sale',
                    'qty'          => $item['qty'],
                    'before_stock' => $before,
                    'after_stock'  => $before - $item['qty'],
                    'notes'        => 'Penjualan ' . $transaction->transaction_code,
                ]);
            }

            ActivityLog::create([
                'user_id'     => auth()->id(),
                'activity'    => 'TRANSACTION',
                'description' => 'Melakukan transaksi ' . $transaction->transaction_code,
                'ip_address'  => request()->ip(),
            ]);

            $transactionId = $transaction->id;
        });

        $this->cart  = [];
        $this->paid  = 0;
        $this->discountValue = 0;
        $this->lastTransactionId = $transactionId;
    }

    public function render()
    {
        $categories = Category::all();
        
        $productsQuery = Product::where('is_active', true);

        if ($this->search) {
            $productsQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->selectedCategory) {
            $productsQuery->where('category_id', $this->selectedCategory);
        }

        $products = $productsQuery->get();

        return view('livewire.cashier.pos', [
            'products' => $products,
            'categories' => $categories
        ]);
    }
}
