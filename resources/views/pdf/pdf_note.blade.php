<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota Pengambilan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { font-size: 18px; font-weight: bold; text-align: center; }
        p { font-size: 12px; }
        .italic { font-style: italic; }
        .text-right { text-align: right; }
        .font-medium { font-weight: 500; }
        .font-semibold { font-weight: 600; }
        .mt-4 { margin-top: 1rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .text-xs { font-size: 10px; }
        .text-sm { font-size: 12px; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .w-full { width: 100%; }
        .border { border: 1px solid black; }
        .border-collapse { border-collapse: collapse; }
        .px-1 { padding-left: 4px; padding-right: 4px; }
        .py-1 { padding-top: 4px; padding-bottom: 4px; }
        .bg-gray-100 { background-color: #f3f4f6;}
        .flex {display: flex;}
        .justify-between {justify-content: space-between;}
        .max-w-7xl {max-width: 80rem;}
        .mx-auto { margin-left: auto; margin-right: auto;}
        .px-4 {padding-left: 1rem;padding-right: 1rem;}
        .py-6 {padding-top: 1.5rem;padding-bottom: 1.5rem;}
        .text-xs {font-size: 0.75rem;}

    </style>
</head>
<body>
    <div class="max-w-7xl mx-auto px-4 py-6 text-xs">
    <div class="text-center mb-6">
        <h2 style="font-size: 18px; font-weight: bold;">Nota Pengambilan</h2>
        <p class="font-medium">Semester 1 2024/2025</p>
    </div>

    <div class="mb-4">
        <div class="mt-4 text-sm text-gray-600 italic">
            <p class="text-right"><em>Tandai Stabilo yang sudah terkirim</em></p>
        </div>
        <div class="flex justify-between">            
            <p><strong>Sekolah Dasar:</strong> {{ $school }}</p>
            <p><strong>Telp/Wa:</strong> 0821 333 759 59</p>
        </div>
        <p><strong>Kota / Kabupaten:</strong> {{ $city }}</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-collapse text-center">
            <thead class="bg-gray-100 text-[11px]">
                <tr>
                    <th class="border px-1 py-1">NO.</th>
                    <th class="border px-1 py-1">HARGA<br>SATUAN</th>
                    <th class="border px-1 py-1">JUDUL BUKU</th>
                    @for ($i = 1; $i <= 6; $i++)
                        <th class="border px-1 py-1">{{ $i }}</th>
                    @endfor
                    <th class="border px-1 py-1">TOTAL<br>AMBIL</th>
                    <th class="border px-1 py-1">TOTAL BAYAR</th>
                </tr>
            </thead>
            <tbody class="text-[11px]">
                @foreach ($items as $book)
                    <tr>
                        <td class="border px-1 py-1">{{ $book['no'] }}</td>
                        <td class="border px-1 py-1">Rp {{ number_format($book['price'], 0, ',', '.') }}</td>
                        <td class="border px-1 py-1 text-left">{{ $book['title'] }}</td>
                        @foreach ($book['quantities'] as $qty)
                            <td class="border px-1 py-1">{{ $qty }}</td>
                        @endforeach
                        <td class="border px-1 py-1">{{ $book['total_ambil'] }}</td>
                        <td class="border px-1 py-1">
                            @if ($book['total_bayar'] > 0)
                                Rp {{ number_format($book['total_bayar'], 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 text-right font-semibold text-sm">
        TOTAL PEMESANAN: Rp {{ number_format($total_pesanan, 0, ',', '.') }}
    </div>
</div>
</body>
</html>
