<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>e-TBPKP {{ $pembayaran->no_etbpkp }}</title>
    <style>
        @page {
            margin: 24px 28px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
            color: #0F172A;
            margin: 0;
        }
        .header-bar {
            background-color: #0F2942;
            color: #FFFFFF;
            padding: 16px 18px;
            border-bottom: 4px solid #047857;
            margin-bottom: 18px;
        }
        .header-brand {
            font-size: 10px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #CBD5E1;
            margin: 0 0 4px 0;
        }
        .header-title {
            font-size: 15px;
            font-weight: bold;
            margin: 0;
        }
        .header-meta {
            font-size: 11px;
            margin: 6px 0 0 0;
            color: #E2E8F0;
        }
        .status-box {
            border: 1.5px solid #065F46;
            background-color: #D1FAE5;
            color: #065F46;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 16px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0F2942;
            margin: 0 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #CBD5E1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        th, td {
            border: 1px solid #CBD5E1;
            padding: 7px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #F4F6F9;
            color: #0F2942;
            font-weight: bold;
            width: 36%;
        }
        .amount {
            text-align: right;
            font-weight: bold;
        }
        .total-row th, .total-row td {
            background-color: #0F2942;
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #CBD5E1;
            font-size: 10px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="header-bar">
        <p class="header-brand">Pajak.In | Portal Pajak Kendaraan Bermotor</p>
        <p class="header-title">TANDA BUKTI PELUNASAN KEWAJIBAN PEMBAYARAN (e-TBPKP)</p>
        <p class="header-meta">Nomor Dokumen: {{ $pembayaran->no_etbpkp }} | Kode Transaksi: {{ $pembayaran->kode_transaksi }}</p>
    </div>

    <div class="status-box">
        STATUS PEMBAYARAN: LUNAS | TANGGAL BAYAR: {{ $pembayaran->tanggal_bayar->format('d/m/Y H:i') }} WIB
    </div>

    <p class="section-title">Data Wajib Pajak dan Kendaraan Bermotor</p>
    <table>
        <tr>
            <th>Nama Wajib Pajak</th>
            <td>{{ $wajibPajak->name }}</td>
        </tr>
        <tr>
            <th>Jenis Entitas</th>
            <td>{{ ($profile?->jenis_entitas ?? 'pribadi') === 'usaha' ? 'Badan Usaha' : 'Wajib Pajak Pribadi' }}</td>
        </tr>
        <tr>
            <th>NIK / NPWP</th>
            <td>{{ $profile?->nik_npwp ?? '-' }}</td>
        </tr>
        <tr>
            <th>Provinsi Terdaftar</th>
            <td>{{ $kendaraan->provinsi }}</td>
        </tr>
        <tr>
            <th>Nomor Polisi (Plat Nomor)</th>
            <td><strong>{{ $kendaraan->plat_nomor }}</strong></td>
        </tr>
        <tr>
            <th>Kendaraan</th>
            <td>{{ $kendaraan->merek }} {{ $kendaraan->model_kendaraan }} (Tahun {{ $kendaraan->tahun_pembuatan }}, {{ $kendaraan->kapasitas_cc }} cc)</td>
        </tr>
        <tr>
            <th>Tahun Pajak / Jatuh Tempo</th>
            <td>{{ $tagihan->tahun_pajak }} (Jatuh Tempo: {{ $tagihan->jatuh_tempo->format('d/m/Y') }})</td>
        </tr>
        <tr>
            <th>Metode Pembayaran</th>
            <td>{{ $pembayaran->metode_pembayaran }}</td>
        </tr>
    </table>

    <p class="section-title">Rincian Pelunasan Pajak Kendaraan Bermotor</p>
    <table>
        <tr>
            <th>PKB Pokok</th>
            <td class="amount">Rp {{ number_format((float) $tagihan->pkb_pokok, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Pajak Progresif (Kepemilikan ke-{{ $kendaraan->urutan_kepemilikan }})</th>
            <td class="amount">Rp {{ number_format((float) $tagihan->pajak_progresif, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Opsen PKB Kabupaten/Kota</th>
            <td class="amount">Rp {{ number_format((float) $tagihan->opsen_pkb, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>SWDKLLJ</th>
            <td class="amount">Rp {{ number_format((float) $tagihan->swdkllj, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Denda Keterlambatan</th>
            <td class="amount">Rp {{ number_format((float) $tagihan->denda_keterlambatan, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <th>TOTAL DIBAYAR (LUNAS)</th>
            <td class="amount">Rp {{ number_format((float) $pembayaran->nominal_dibayar, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        Dokumen ini diterbitkan secara elektronik oleh Pajak.In berdasarkan transaksi {{ $pembayaran->kode_transaksi }} dan sah sebagai bukti pelunasan PKB.
    </div>
</body>
</html>
