@extends('arsip.templates._layout')

@php
    $letterDate = \Carbon\Carbon::parse($letter->letter_date)->locale('id')->translatedFormat('d F Y');
    $creatorPosition = is_array($creator['position'] ?? null) ? ($creator['position']['name'] ?? 'Kepala Departemen SDM') : ($creator['position'] ?? 'Kepala Departemen SDM');
    $signerName = $content['signer_name'] ?? ($creator['name'] ?? 'Dani Jatnika');
    $signerTitle = $content['signer_title'] ?? $creatorPosition;
    $petugas = $content['yang_ditugaskan'] ?? [];
    if (empty($petugas) && !empty($content['petugas'])) {
        $petugas = $content['petugas'];
    }

@endphp

@section('content')
    <div class="doc-title-block" style="margin-top: 10px; margin-bottom: 24px;">
        <div class="doc-title">SURAT TUGAS</div>
        <div class="doc-number">No : {{ $letter->document_number ?: '        /ST/PTKP/' . \Carbon\Carbon::parse($letter->letter_date)->format('m/Y') }}</div>
    </div>

    <div class="body-content" style="line-height: 1.5; text-align: justify;">
        <p>
            {{ $content['dasar_penugasan'] ?? 'Berdasarkan kebutuhan operasional dan penugasan kedinasan di lingkungan perusahaan, PT. Kertas Padalarang menugaskan kepada :' }}
        </p>

        <table class="ptkp-table">
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Nama</th>
                    <th style="width: 120px;">No. Register</th>
                    <th>Jabatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($petugas as $idx => $p)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}.</td>
                        <td><strong>{{ $p['nama'] ?? '-' }}</strong></td>
                        <td style="text-align: center;">{{ $p['nip'] ?? ($p['no_reg'] ?? '-') }}</td>
                        <td>{{ $p['jabatan'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td style="text-align: center;">1.</td>
                        <td><strong>{{ $content['nama_petugas'] ?? ($creator['name'] ?? '-') }}</strong></td>
                        <td style="text-align: center;">{{ $content['nip_petugas'] ?? '-' }}</td>
                        <td>{{ $content['jabatan_petugas'] ?? '-' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p style="margin-top: 14pt;">
            @if(!empty($content['body_html']))
                {!! $content['body_html'] !!}
            @else
                Untuk melakukan {{ $content['keperluan'] ?? ($content['tujuan'] ?? 'tugas kedinasan sebagaimana tercantum dalam instruksi kerja') }}
                @if(!empty($content['tanggal_mulai']))
                    pada tanggal {{ \Carbon\Carbon::parse($content['tanggal_mulai'])->locale('id')->translatedFormat('d F Y') }}
                    @if(!empty($content['tanggal_selesai']) && $content['tanggal_selesai'] !== $content['tanggal_mulai'])
                        s.d. {{ \Carbon\Carbon::parse($content['tanggal_selesai'])->locale('id')->translatedFormat('d F Y') }}
                    @endif
                @endif
                @if(!empty($content['lokasi']))
                    bertempat di {{ $content['lokasi'] }}.
                @else
                    .
                @endif
            @endif
        </p>

        <p style="margin-top: 16pt;">
            Demikian Surat Tugas ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.
        </p>
    </div>

    <div class="signature-section" style="margin-top: 30px;">
        <div class="signature-box">
            <table class="meta-table" style="margin: 0; font-size: 11pt;">
                <tr>
                    <td style="width: 105px;">Dikeluarkan di</td>
                    <td style="width: 10px;">:</td>
                    <td>Padalarang</td>
                </tr>
                <tr>
                    <td>Pada Tanggal</td>
                    <td>:</td>
                    <td>{{ $letterDate }}</td>
                </tr>
            </table>
            <div style="font-weight: bold; margin-top: 8px;">PT. Kertas Padalarang</div>
            @include('arsip.templates._signature_block')
            <div class="signature-title">{{ $signerTitle }}</div>
        </div>
    </div>
@endsection
