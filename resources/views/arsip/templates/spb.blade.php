@extends('arsip.templates._layout')

@php
    $deptName = $originUnit['name'] ?? 'DEPARTEMEN SUMBER DAYA MANUSIA';
    $letterDate = \Carbon\Carbon::parse($letter->letter_date)->locale('id')->translatedFormat('d F Y');
    $creatorPosition = is_array($creator['position'] ?? null) ? ($creator['position']['name'] ?? 'Kepala Departemen') : ($creator['position'] ?? 'Kepala Departemen');
    $signerName = $content['signer_name'] ?? ($creator['name'] ?? 'Dani Jatnika');
    $signerTitle = $content['signer_title'] ?? $creatorPosition;
    $items = $content['items'] ?? [];
@endphp


@section('kop')
    <div style="font-size: 8pt; color: #666666; margin-bottom: 4px; text-align: left;">
        F.06.1.1.2 Rev.3 (18-3-2025)
    </div>
    <div class="kop-header">
        @if(!empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="PERURI Security Paper Mill" class="kop-logo">
        @endif
        <div class="kop-company">PT. KERTAS PADALARANG</div>
        <div class="kop-sub" style="font-weight: bold; text-transform: uppercase;">{{ $deptName }}</div>
        <hr class="kop-divider">
    </div>
@endsection

@section('content')
    <div style="text-align: right; margin-bottom: 12px; font-size: 11pt;">
        Padalarang, {{ $letterDate }}
    </div>

    <table style="width: 100%; margin-bottom: 16px; font-size: 10.5pt; border-collapse: collapse;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <table class="meta-table" style="font-size: 10.5pt;">
                    <tr>
                        <td style="width: 70px;">No.</td>
                        <td style="width: 10px;">:</td>
                        <td>{{ $letter->document_number ?: '        /SPB/' . ($originUnit['code'] ?? 'UNIT') . '/' . \Carbon\Carbon::parse($letter->letter_date)->format('m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Lamp.</td>
                        <td>:</td>
                        <td>{{ $content['lampiran'] ?? '1 Berkas' }}</td>
                    </tr>
                    <tr>
                        <td>Hal</td>
                        <td>:</td>
                        <td><strong>Permintaan Barang/Jasa (SPB/SPJ)</strong></td>
                    </tr>
                    <tr>
                        <td>Kategori</td>
                        <td>:</td>
                        <td>{{ $content['kategori'] ?? 'Investasi / Non-Investasi / Jasa' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%; vertical-align: top; padding-left: 20px;">
                <div style="font-size: 10.5pt; line-height: 1.4;">
                    Kepada Yth, :<br>
                    <strong>{{ $content['tujuan_departemen'] ?? 'Departemen Dafasum' }}</strong><br>
                    {{ $content['tujuan_seksi'] ?? 'Up. Seksi Gudang' }}<br>
                    Di Tempat
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 10px;">
        <p style="margin-bottom: 4px;">Dengan Hormat,</p>
        <p>Mohon dapat disiapkan permintaan barang-barang/jasa sebagai berikut:</p>
    </div>

    <table class="ptkp-table" style="font-size: 9.5pt;">
        <thead>
            <tr>
                <th style="width: 30px;">NO.</th>
                <th style="width: 100px;">ITEM CODE *)</th>
                <th>NAMA BARANG</th>
                <th>TYPE / UKURAN / MERK / SPESIFIKASI</th>
                <th style="width: 60px;">JUMLAH</th>
                <th style="width: 60px;">SATUAN</th>
                <th>KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $it)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="text-align: center;">{{ $it['item_code'] ?? '-' }}</td>
                    <td><strong>{{ $it['nama_barang'] ?? ($it['item_name'] ?? '-') }}</strong></td>
                    <td style="white-space: pre-line;">{{ $it['spesifikasi'] ?? ($it['spec'] ?? '-') }}</td>
                    <td style="text-align: center;">{{ $it['jumlah'] ?? ($it['qty'] ?? 1) }}</td>
                    <td style="text-align: center;">{{ $it['satuan'] ?? ($it['unit'] ?? 'Pcs') }}</td>
                    <td>{{ $it['keterangan'] ?? ($it['purpose'] ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: center;">-</td>
                    <td>-</td>
                    <td>-</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: center;">Unit</td>
                    <td>-</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="font-size: 8pt; font-style: italic; color: #555555; margin-top: -8px; margin-bottom: 14px;">
        *) dilihat Dari SAFT ERP, Jika Item baru dapat dikosongkan
    </div>

    <p>Demikian Kami sampaikan permintaan ini, atas perhatiannya kami ucapkan terimakasih.</p>

    <div class="signature-section" style="margin-top: 20px;">
        <div class="signature-box">
            <div>Hormat kami,</div>
            <div style="font-weight: bold; margin-bottom: 4px;">{{ $deptName }}</div>
            @include('arsip.templates._signature_block')
            <div class="signature-title">{{ $signerTitle }}</div>
        </div>
    </div>
@endsection
