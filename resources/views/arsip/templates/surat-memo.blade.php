@extends('arsip.templates._layout')

@php
    $letterDate = \Carbon\Carbon::parse($letter->letter_date)->locale('id')->translatedFormat('d F Y');
    $creatorPosition = is_array($creator['position'] ?? null) ? ($creator['position']['name'] ?? 'Plt. Direktur Utama') : ($creator['position'] ?? 'Plt. Direktur Utama');
    $signerName = $content['signer_name'] ?? ($creator['name'] ?? 'Puteri Zulieni');
    $signerTitle = $content['signer_title'] ?? $creatorPosition;
@endphp


@section('kop')
    <div class="kop-header">
        @if(!empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="PERURI Security Paper Mill" class="kop-logo">
        @endif
        <div class="kop-company">PT. KERTAS PADALARANG</div>
        <div class="kop-sub" style="font-weight: bold; letter-spacing: 1px;">DIREKSI</div>
        <hr class="kop-divider">
    </div>
@endsection

@section('content')
    <div class="doc-title-block" style="margin-bottom: 16px;">
        <div class="doc-title">MEMORANDUM DIREKSI</div>
        <div class="doc-number">Nomor : {{ $letter->document_number ?: '        /E/DIR/' . \Carbon\Carbon::parse($letter->letter_date)->format('m/Y') }}</div>
    </div>

    <table class="meta-table" style="margin-bottom: 16px;">
        <tr>
            <td style="width: 80px;">Kepada</td>
            <td style="width: 15px;">:</td>
            <td><strong>{{ $content['kepada'] ?? 'Seluruh Kepala Departemen / Karyawan' }}</strong></td>
        </tr>
        <tr>
            <td>Dari</td>
            <td>:</td>
            <td>{{ $content['dari'] ?? $signerTitle }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $letterDate }}</td>
        </tr>
        <tr>
            <td>Perihal</td>
            <td>:</td>
            <td><strong>{{ $letter->subject }}</strong></td>
        </tr>
    </table>

    <div style="border-top: 1px solid #111111; margin-bottom: 16px;"></div>

    <div class="body-content" style="line-height: 1.5; text-align: justify;">
        @if(!empty($content['body_html']))
            {!! $content['body_html'] !!}
        @else
            <p>{{ $content['body_text'] ?? 'Dalam rangka meningkatkan kedisiplinan dan optimalisasi kinerja operasional di seluruh unit PT Kertas Padalarang, dengan ini disampaikan arahan direksi sebagai berikut.' }}</p>
        @endif

        <p style="margin-top: 16pt;">Demikian memorandum ini disampaikan untuk menjadi perhatian dan dilaksanakan dengan penuh tanggung jawab.</p>
    </div>

    <div class="signature-section" style="margin-top: 30px;">
        <div class="signature-box">
            <div style="font-weight: bold;">Direksi PT. Kertas Padalarang</div>
            @include('arsip.templates._signature_block')
            <div class="signature-title">{{ $signerTitle }}</div>
        </div>
    </div>
@endsection
