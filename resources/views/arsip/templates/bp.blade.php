@extends('arsip.templates._layout')

@php
    $letterDate = \Carbon\Carbon::parse($letter->letter_date)->locale('id')->translatedFormat('d F Y');
    $creatorPosition = is_array($creator['position'] ?? null) ? ($creator['position']['name'] ?? 'Kepala Departemen SDM') : ($creator['position'] ?? 'Kepala Departemen SDM');
    $signerName = $content['signer_name'] ?? ($creator['name'] ?? 'Dani Jatnika');
    $signerTitle = $content['signer_title'] ?? $creatorPosition;
    $totalCost = (int) ($content['total_cost'] ?? 0);
@endphp


@section('kop')
    <div class="kop-header">
        @if(!empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="PERURI Security Paper Mill" class="kop-logo">
        @endif
        <div class="kop-company">PT. KERTAS PADALARANG</div>
        <div class="kop-sub" style="font-weight: bold;">DEPARTEMEN SUMBER DAYA MANUSIA</div>
        <hr class="kop-divider">
    </div>
@endsection

@section('content')
    <div class="doc-title-block" style="margin-bottom: 20px;">
        <div class="doc-title">SURAT PENGANTAR BIAYA PENGOBATAN</div>
        <div class="doc-number">Nomor : {{ $letter->document_number ?: '        /BP/SDM/' . \Carbon\Carbon::parse($letter->letter_date)->format('m/Y') }}</div>
    </div>

    <div class="body-content" style="line-height: 1.5; font-size: 10.5pt;">
        <p>Dengan ini menerangkan bahwa permohonan klaim biaya pengobatan karyawan sebagai berikut :</p>

        <table class="ptkp-table" style="margin-bottom: 16px;">
            <tr>
                <td style="width: 220px; font-weight: bold;">Nama Pegawai / Pasien</td>
                <td><strong>{{ $content['patient_name'] ?? ($creator['name'] ?? '-') }}</strong></td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Nomor Registrasi / NIP</td>
                <td>{{ $content['patient_nip'] ?? ($creator['nip'] ?? '-') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Unit Kerja / Departemen</td>
                <td>{{ $originUnit['name'] ?? 'SDM' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Klinik / Rumah Sakit Rujukan</td>
                <td>{{ $content['clinic_name'] ?? 'Klinik Pratama PTKP' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tanggal Pemeriksaan / Berobat</td>
                <td>{{ !empty($content['treatment_date']) ? \Carbon\Carbon::parse($content['treatment_date'])->locale('id')->translatedFormat('d F Y') : $letterDate }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Keterangan / Diagnosa</td>
                <td>{{ $content['diagnosis'] ?? ($content['description'] ?? 'Rawat jalan dan penebusan obat resep') }}</td>
            </tr>
            <tr style="background-color: #f9f9f9;">
                <td style="font-weight: bold;">Total Biaya Penggantian (Rp)</td>
                <td><strong style="font-size: 11pt;">Rp {{ number_format($totalCost, 0, ',', '.') }}</strong></td>
            </tr>
        </table>

        @if(!empty($content['terbilang']))
            <div style="font-style: italic; margin-top: -8px; margin-bottom: 16px; font-size: 10pt;">
                Terbilang : {{ $content['terbilang'] }}
            </div>
        @endif

        <p style="margin-top: 14pt;">
            Demikian surat permohonan penggantian biaya pengobatan ini dibuat dengan sesungguhnya untuk diproses sesuai peraturan perusahaan yang berlaku.
        </p>
    </div>

    <div class="signature-section" style="margin-top: 25px;">
        <div class="signature-box">
            <div>Padalarang, {{ $letterDate }}</div>
            <div style="font-weight: bold; margin-top: 4px;">Departemen SDM</div>
            @include('arsip.templates._signature_block')
            <div class="signature-title">{{ $signerTitle }}</div>
        </div>
    </div>
@endsection
