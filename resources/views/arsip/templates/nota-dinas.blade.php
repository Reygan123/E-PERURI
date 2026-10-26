@extends('arsip.templates._layout')

@php
    $deptName = $originUnit['name'] ?? 'DEPARTEMEN SUMBER DAYA MANUSIA';
    $letterDate = \Carbon\Carbon::parse($letter->letter_date)->locale('id')->translatedFormat('d F Y');
    $creatorPosition = is_array($creator['position'] ?? null) ? ($creator['position']['name'] ?? 'Kepala Departemen') : ($creator['position'] ?? 'Kepala Departemen');
    $signerName = $content['signer_name'] ?? ($creator['name'] ?? 'Dani Jatnika');
    $signerTitle = $content['signer_title'] ?? $creatorPosition;
@endphp


@section('kop')
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
    <div class="doc-title-block" style="margin-bottom: 8px;">
        <div class="doc-title">NOTA DINAS</div>
        <div class="doc-number">Nomor : {{ $letter->document_number ?: '        /' . ($originUnit['code'] ?? 'UNIT') . '/' . \Carbon\Carbon::parse($letter->letter_date)->format('m/Y') }}</div>
    </div>

    <div style="text-align: right; margin-bottom: 14px; font-size: 11pt;">
        Padalarang, {{ $letterDate }}
    </div>

    <table class="meta-table" style="margin-bottom: 16px;">
        <tr>
            <td style="width: 90px;">Kepada</td>
            <td style="width: 15px;">:</td>
            <td><strong>{{ $content['kepada'] ?? 'Direktur Utama' }}</strong></td>
        </tr>
        <tr>
            <td>Dari</td>
            <td>:</td>
            <td>{{ $content['dari_jabatan'] ?? ($originUnit['name'] ?? 'Kepala Departemen') }}</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td>{{ $content['lampiran'] ?? '-' }}</td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>:</td>
            <td><strong>{{ $letter->subject }}</strong></td>
        </tr>
    </table>

    <div style="border-top: 1px solid #111111; margin-bottom: 16px;"></div>

    <div class="body-content" style="line-height: 1.5; text-align: justify;">
        <p>Dengan Hormat,</p>

        @if(!empty($content['body_html']))
            {!! $content['body_html'] !!}
        @else
            <p>{{ $content['body_text'] ?? 'Sehubungan dengan operasional perusahaan, bersama ini kami sampaikan permohonan sebagaimana terlampir.' }}</p>
        @endif

        <p style="margin-top: 14pt;">Demikian nota dinas ini kami sampaikan, atas perhatian dan persetujuannya kami ucapkan terima kasih.</p>
    </div>

    <div class="signature-section">
        <div class="signature-box">
            <div>Hormat Kami,</div>
            <div style="font-weight: bold; margin-bottom: 4px;">{{ $deptName }}</div>
            @include('arsip.templates._signature_block')
            <div class="signature-title">{{ $signerTitle }}</div>
        </div>
    </div>
@endsection
