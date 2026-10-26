@extends('arsip.templates._layout')

@php
    $letterDate = \Carbon\Carbon::parse($letter->letter_date)->locale('id')->translatedFormat('d F Y');
    $creatorPosition = is_array($creator['position'] ?? null) ? ($creator['position']['name'] ?? 'Staff') : ($creator['position'] ?? 'Staff');
    $signerName = $content['signer_name'] ?? ($creator['name'] ?? 'Puteri Zulieni');
    $signerTitle = $content['signer_title'] ?? ($creatorPosition ?? 'Plt. Direktur Utama');
    $pemberiPerintah = $content['pemberi_perintah'] ?? ($creator['name'] ?? 'Dani Jatnika');

    $pegawaiList = $content['pegawai'] ?? [];
    if (empty($pegawaiList) && !empty($content['yang_ditugaskan'])) {
        $pegawaiList = $content['yang_ditugaskan'];
    }
    if (empty($pegawaiList)) {
        $pegawaiList = [
            [
                'nama' => $creator['name'] ?? 'Staff',
                'nip' => $creator['nip'] ?? '-',
                'unit' => $originUnit['name'] ?? 'SDM',
                'jabatan' => $creatorPosition,
            ]
        ];
    }


    $departureDate = !empty($content['departure_date']) ? \Carbon\Carbon::parse($content['departure_date'])->locale('id')->translatedFormat('d F Y') : $letterDate;
    $returnDate = !empty($content['return_date']) ? \Carbon\Carbon::parse($content['return_date'])->locale('id')->translatedFormat('d F Y') : $departureDate;

    $rincianBiaya = $content['rincian_biaya'] ?? [];
    $totalBiaya = 0;
    foreach ($rincianBiaya as $rb) {
        $totalBiaya += ($rb['total'] ?? (($rb['uang_harian'] ?? 0) + ($rb['uang_perlengkapan'] ?? 0) + ($rb['lain_lain'] ?? 0)));
    }
    if ($totalBiaya === 0 && !empty($content['estimated_cost'])) {
        $totalBiaya = (int) $content['estimated_cost'];
    }
@endphp

@section('content')
    <!-- HALAMAN 1: SURAT PERINTAH PERJALANAN DINAS -->
    <div class="doc-title-block" style="margin-bottom: 16px;">
        <div class="doc-title" style="font-size: 13pt;">SURAT PERINTAH PERJALANAN DINAS (SPPD)</div>
        <div class="doc-number" style="font-weight: bold;">
            NO SPPD : {{ $letter->document_number ?: '        /SPD/PTKP/' . \Carbon\Carbon::parse($letter->letter_date)->format('m/Y') }}
        </div>
    </div>

    <table class="ptkp-table" style="font-size: 10pt; margin-bottom: 16px;">
        <tr>
            <td style="width: 35px; text-align: center; font-weight: bold;">1</td>
            <td style="width: 250px;">Nama Pejabat Pemberi Perintah Dinas</td>
            <td><strong>{{ $pemberiPerintah }}</strong></td>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold; vertical-align: top;">2</td>
            <td style="vertical-align: top;">
                Nama Pegawai Yang Diperintah
            </td>
            <td style="padding: 4px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 9.5pt;">
                    <thead>
                        <tr style="border-bottom: 1px solid #333;">
                            <th style="width: 25px; text-align: center; padding: 2px;">No</th>
                            <th style="text-align: left; padding: 2px;">Nama</th>
                            <th style="text-align: center; width: 60px; padding: 2px;">No.Reg</th>
                            <th style="text-align: left; padding: 2px;">Departemen/Seksi</th>
                            <th style="text-align: left; padding: 2px;">Jabatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pegawaiList as $idx => $peg)
                            <tr>
                                <td style="text-align: center; padding: 2px;">{{ $idx + 1 }}.</td>
                                <td style="padding: 2px;"><strong>{{ $peg['nama'] ?? '-' }}</strong></td>
                                <td style="text-align: center; padding: 2px;">{{ $peg['nip'] ?? ($peg['no_reg'] ?? '-') }}</td>
                                <td style="padding: 2px;">{{ $peg['unit'] ?? ($peg['departemen'] ?? ($originUnit['name'] ?? '-')) }}</td>
                                <td style="padding: 2px;">{{ is_array($peg['jabatan'] ?? null) ? ($peg['jabatan']['name'] ?? '-') : ($peg['jabatan'] ?? '-') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold;">3</td>
            <td>
                a. Kode Anggaran*<br>
                b. Kode Unit Kerja*
            </td>
            <td>
                {{ $content['kode_anggaran'] ?? '-' }}<br>
                {{ $content['kode_unit_kerja'] ?? ($originUnit['code'] ?? '-') }}
            </td>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold;">4</td>
            <td>Maksud Perjalanan Dinas</td>
            <td>{{ $content['purpose'] ?? ($content['maksud'] ?? $letter->subject) }}</td>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold;">5</td>
            <td>Alat Transportasi Yang Dipergunakan</td>
            <td>{{ $content['transport_mode'] ?? 'Kendaraan Dinas' }}</td>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold;">6</td>
            <td>Instansi Yang Dituju</td>
            <td><strong>{{ $content['destination_city'] ?? ($content['instansi_tujuan'] ?? 'Kantor Perum Peruri') }}</strong></td>
        </tr>
        <tr>
            <td style="text-align: center; font-weight: bold;">7</td>
            <td>
                a. Tanggal Berangkat<br>
                b. Tanggal Kembali
            </td>
            <td>
                {{ $departureDate }}<br>
                {{ $returnDate }}
            </td>
        </tr>
    </table>

    <!-- Pos Sekuriti Checkbox Area -->
    <div style="border: 1px solid #111111; padding: 8px 12px; margin-bottom: 16px; font-size: 9pt;">
        <div style="text-align: center; font-weight: bold; margin-bottom: 6px; letter-spacing: 0.5px;">
            DI ISI DAN DI CAP OLEH PETUGAS SEKURITI PTKP
        </div>
        <table style="width: 100%; font-size: 9pt;">
            <tr>
                <td style="width: 120px;">Tanggal Keluar</td>
                <td style="width: 10px;">:</td>
                <td style="width: 35%;">....................................</td>
                <td style="width: 120px;">Tanggal Masuk</td>
                <td style="width: 10px;">:</td>
                <td>....................................</td>
            </tr>
            <tr>
                <td>Jam Keluar</td>
                <td>:</td>
                <td>....................................</td>
                <td>Jam Masuk</td>
                <td>:</td>
                <td>....................................</td>
            </tr>
        </table>
        <div style="font-size: 8pt; font-style: italic; margin-top: 4px; color: #444;">*) Harus Diisi</div>
    </div>

    <div class="signature-section" style="margin-top: 10px;">
        <div class="signature-box">
            <table class="meta-table" style="margin: 0; font-size: 10.5pt;">
                <tr>
                    <td style="width: 110px;">Dikeluarkan di</td>
                    <td style="width: 10px;">:</td>
                    <td>Padalarang</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td>{{ $letterDate }}</td>
                </tr>
            </table>
            <div style="font-weight: bold; margin-top: 6px;">Direksi PT. Kertas Padalarang</div>
            <div class="signature-space" style="height: 55px;"></div>
            <div class="signature-name">{{ $signerName }}</div>
            <div class="signature-title">{{ $signerTitle }}</div>
        </div>
    </div>

    <!-- HALAMAN 2: PERHITUNGAN BIAYA PERJALANAN DINAS -->
    <div style="page-break-before: always; padding-top: 10px;"></div>

    <div class="kop-header">
        @if(!empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="PERURI Security Paper Mill" class="kop-logo" style="max-width: 140px;">
        @endif
        <div class="kop-company">PT. KERTAS PADALARANG</div>
        <div style="font-size: 12pt; font-weight: bold; text-decoration: underline; margin-top: 6px;">
            PERHITUNGAN BIAYA PERJALANAN DINAS
        </div>
    </div>

    <table style="width: 100%; margin-bottom: 14px; font-size: 10pt;">
        <tr>
            <td style="width: 170px;">Surat Perintah Dinas No.</td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $letter->document_number ?: '        /SPD/PTKP/' . \Carbon\Carbon::parse($letter->letter_date)->format('m/Y') }}</strong></td>
            <td style="width: 130px;">Berangkat Tanggal</td>
            <td style="width: 10px;">:</td>
            <td>{{ $departureDate }}</td>
        </tr>
        <tr>
            <td>Tanggal Dokumen</td>
            <td>:</td>
            <td>{{ $letterDate }}</td>
            <td>Kembali Tanggal</td>
            <td>:</td>
            <td>{{ $returnDate }}</td>
        </tr>
        <tr>
            <td>Tujuan</td>
            <td>:</td>
            <td colspan="4"><strong>{{ $content['destination_city'] ?? '-' }}</strong></td>
        </tr>
    </table>

    <table class="ptkp-table" style="font-size: 9pt;">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th>NAMA</th>
                <th style="width: 55px;">NO.REG</th>
                <th>JABATAN</th>
                <th style="width: 55px;">DEPT</th>
                <th style="width: 65px;">LAMA DINAS</th>
                <th style="width: 85px;">UANG HARIAN</th>
                <th style="width: 95px;">UANG PERLENGKAPAN</th>
                <th style="width: 80px;">LAIN-LAIN</th>
                <th style="width: 95px;">JUMLAH</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rincianBiaya as $idx => $rb)
                @php
                    $harian = (int) ($rb['uang_harian'] ?? 0);
                    $perlengkapan = (int) ($rb['uang_perlengkapan'] ?? 0);
                    $lain = (int) ($rb['lain_lain'] ?? 0);
                    $subtotal = $rb['total'] ?? ($harian + $perlengkapan + $lain);
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}.</td>
                    <td><strong>{{ $rb['nama'] ?? '-' }}</strong></td>
                    <td style="text-align: center;">{{ $rb['nip'] ?? '-' }}</td>
                    <td>{{ $rb['jabatan'] ?? '-' }}</td>
                    <td style="text-align: center;">{{ $rb['dept'] ?? ($originUnit['code'] ?? 'SDM') }}</td>
                    <td style="text-align: center;">{{ $rb['lama_dinas'] ?? '1 Hari' }}</td>
                    <td style="text-align: right;">Rp {{ number_format($harian, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($perlengkapan, 0, ',', '.') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($lain, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                @foreach($pegawaiList as $idx => $peg)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}.</td>
                        <td><strong>{{ $peg['nama'] ?? '-' }}</strong></td>
                        <td style="text-align: center;">{{ $peg['nip'] ?? '-' }}</td>
                        <td>{{ is_array($peg['jabatan'] ?? null) ? ($peg['jabatan']['name'] ?? '-') : ($peg['jabatan'] ?? '-') }}</td>
                        <td style="text-align: center;">{{ $originUnit['code'] ?? 'SDM' }}</td>
                        <td style="text-align: center;">1 Hari</td>
                        <td style="text-align: right;">-</td>
                        <td style="text-align: right;">-</td>
                        <td style="text-align: right;">-</td>
                        <td style="text-align: right; font-weight: bold;">-</td>
                    </tr>
                @endforeach
            @endforelse
            <tr style="background-color: #f5f5f5; font-weight: bold;">
                <td colspan="9" style="text-align: right;">JUMLAH TOTAL</td>
                <td style="text-align: right;">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    @if(!empty($content['terbilang']))
        <div style="font-size: 9.5pt; font-style: italic; margin-top: -6px; margin-bottom: 20px;">
            Terbilang : {{ $content['terbilang'] }}
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; margin-top: 25px; font-size: 10pt;">
        <div style="width: 250px; text-align: left;">
            <div>Yang mengesahkan,</div>
            <div style="font-weight: bold; margin-bottom: 4px;">Departemen SDM</div>
            <div class="signature-space" style="height: 55px;"></div>
            <div class="signature-name">{{ $content['pejabat_mengesahkan'] ?? 'Benny Saputra' }}</div>
            <div class="signature-title">Kadep SDM</div>
        </div>
        <div style="width: 250px; text-align: left;">
            <div>Padalarang, {{ $letterDate }}</div>
            <div>Yang diperintahkan,</div>
            <div class="signature-space" style="height: 55px;"></div>
            <div class="signature-name">{{ $pegawaiList[0]['nama'] ?? ($creator['name'] ?? 'Pegawai') }}</div>
            <div class="signature-title">{{ is_array($pegawaiList[0]['jabatan'] ?? null) ? ($pegawaiList[0]['jabatan']['name'] ?? 'Staff') : ($pegawaiList[0]['jabatan'] ?? 'Staff') }}</div>
        </div>
    </div>

@endsection
