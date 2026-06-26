<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barcode - {{ $product->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f7f7f7;
        }
        .label-card {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 300px;
        }
        .product-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 4px;
            color: #333;
        }
        .product-price {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        svg {
            width: 100%;
            max-height: 80px;
        }
        .print-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-btn:hover {
            background-color: #0056b3;
        }
        @media print {
            body {
                background-color: #fff;
            }
            .label-card {
                border: none;
                box-shadow: none;
                padding: 0;
                width: auto;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="label-card">
        <div class="product-name">{{ $product->name }}</div>
        <div class="product-price">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</div>
        
        <svg id="barcode"></svg>
        
        <button class="print-btn" onclick="window.print()">Cetak Label</button>
    </div>

    <!-- JsBarcode Script via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        JsBarcode("#barcode", "{{ $product->barcode }}", {
            format: "CODE128",
            lineColor: "#000",
            width: 2,
            height: 50,
            displayValue: true
        });

        // Trigger print dialog automatically after a brief delay for rendering
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
