<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $transaction->transaction_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .receipt-wrapper {
            background: white;
            max-width: 320px;
            width: 100%;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .tagline {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-bottom: 8px;
        }

        .divider {
            border-top: 1px dashed #999;
            margin: 8px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 11px;
        }

        .info-row .label { color: #555; }

        .items-header {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .item-row {
            margin: 4px 0;
        }

        .item-name {
            font-size: 11px;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            color: #555;
            padding-left: 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin: 2px 0;
        }

        .summary-row.total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #333;
            padding-top: 4px;
            margin-top: 4px;
        }

        .summary-row.change {
            font-size: 13px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-top: 8px;
        }

        .print-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #1a56db;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-family: sans-serif;
            margin-top: 16px;
            border-radius: 4px;
        }

        .pdf-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #e02424;
            color: white;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
            font-family: sans-serif;
            margin-top: 8px;
            border-radius: 4px;
        }

        .back-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #e5e7eb;
            color: #374151;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
            font-family: sans-serif;
            margin-top: 8px;
            border-radius: 4px;
        }

        @media print {
            body { background: white; padding: 0; }
            .receipt-wrapper { box-shadow: none; padding: 0; }
            .print-btn, .pdf-btn, .back-btn { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="receipt-wrapper">
        <!-- Store Header -->
        <div class="store-name">SIS TOKO</div>
        <div class="tagline">Sistem Informasi Penjualan & Stok</div>

        <div class="divider"></div>

        <!-- Transaction Info -->
        <div class="info-row">
            <span class="label">No. Transaksi</span>
            <span>{{ $transaction->transaction_code }}</span>
        </div>
        <div class="info-row">
            <span class="label">Kasir</span>
            <span>{{ $transaction->user->name }}</span>
        </div>
        <div class="info-row">
            <span class="label">Tanggal</span>
            <span>{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
        </div>

        <div class="divider"></div>

        <!-- Items -->
        <div class="items-header">
            <span>ITEM</span>
            <span>SUBTOTAL</span>
        </div>

        @foreach($transaction->details as $detail)
            <div class="item-row">
                <div class="item-name">{{ $detail->product->name }}</div>
                <div class="item-detail">
                    <span>{{ $detail->quantity }} x Rp {{ number_format($detail->selling_price, 0, ',', '.') }}</span>
                    <span>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach

        <div class="divider"></div>

        <!-- Summary -->
        <div class="summary-row">
            <span>Subtotal</span>
            <span>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
        </div>

        @if($transaction->discount_amount > 0)
        <div class="summary-row">
            <span>Diskon</span>
            <span>- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
        </div>
        @endif

        <div class="summary-row total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($transaction->final_price, 0, ',', '.') }}</span>
        </div>

        <div class="summary-row">
            <span>Bayar</span>
            <span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
        </div>

        <div class="summary-row change">
            <span>Kembali</span>
            <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
        </div>

        <div class="divider"></div>

        <!-- Footer -->
        <div class="footer">
            <p>Terima kasih telah berbelanja!</p>
            <p>Barang yang sudah dibeli</p>
            <p>tidak dapat dikembalikan.</p>
        </div>

        <!-- Action Buttons -->
        <button class="print-btn" onclick="window.print()">🖨️ Print Struk</button>
        <a class="pdf-btn" href="{{ route('receipt.pdf', $transaction) }}" target="_blank">📄 Download PDF</a>
        <a class="back-btn" href="/cashier">← Kembali ke POS</a>
    </div>
</body>
</html>
