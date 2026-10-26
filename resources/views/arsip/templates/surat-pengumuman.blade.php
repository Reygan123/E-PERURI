@extends('arsip.templates._layout')

@php
    $letterDate = \Carbon\Carbon::parse($letter->letter_date)->locale('id')->translatedFormat('d F Y');
    $creatorPosition = is_array($creator['position'] ?? null) ? ($creator['position']['name'] ?? 'Kepala Departemen SDM') : ($creator['position'] ?? 'Kepala Departemen SDM');
    $signerName = $content['signer_name'] ?? ($creator['name'] ?? 'Dani Jatnika');
    $signerTitle = $content['signer_title'] ?? $creatorPosition;
@endphp


@section('kop')
    <div class="kop-header">
        @if(!empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="PERURI Security Paper Mill" class="kop-logo">
        @endif
        <div class="kop-company">PT. KERTAS PADALARANG</div>
        <hr class="kop-divider">
    </div>
@endsection

@section('content')
    <div class="doc-title-block" style="margin-bottom: 22px;">
        <div class="doc-title" style="font-size: 13pt;">PENGUMUMAN</div>
        <div class="doc-number">Nomor : {{ $letter->document_number ?: '        /PENG/' . ($originUnit['code'] ?? 'PTKP') . '/' . \Carbon\Carbon::parse($letter->letter_date)->format('m/Y') }}</div>
        <div style="font-weight: bold; margin-top: 8px; text-transform: uppercase;">
            TENTANG<br>
            {{ $letter->subject }}
        </div>
    </div>

    <div class="body-content" style="line-height: 1.5; text-align: justify;">
        @if(!empty($content['body_html']))
            {!! $content['body_html'] !!}
        @else
            <p>{{ $content['body_text'] ?? 'Diberitahukan kepada seluruh karyawan/karyawati PT Kertas Padalarang sehubungan dengan agenda operasional perusahaan.' }}</p>
        @endif

        <p style="margin-top: 18pt;">
            Demikian pengumuman ini disampaikan untuk diketahui dan dilaksanakan sebagaimana mestinya. Atas perhatian seluruh insan perusahaan diucapkan terima kasih.
        </p>
    </div>

    <div class="signature-section" style="margin-top: 30px;">
        <div class="signature-box">
            <div>Dikeluarkan di : Padalarang</div>
            <div>Pada Tanggal : {{ $letterDate }}</div>
            <div style="font-weight: bold; margin-top: 6px;">PT. Kertas Padalarang</div>
            @include('arsip.templates._signature_block')
            <div class="signature-title">{{ $signerTitle }}</div>
        </div>
    </div>
@endsection
