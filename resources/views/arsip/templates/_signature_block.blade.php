@php
    $finalSignerName = $signatureSigner['name'] ?? ($signerName ?? ($creator['name'] ?? 'Pejabat PTKP'));
    $finalSignerTitle = is_array($signatureSigner['position'] ?? null)
        ? ($signatureSigner['position']['name'] ?? ($signerTitle ?? 'Pejabat Struktural'))
        : ($signatureSigner['position'] ?? ($signerTitle ?? 'Pejabat Struktural'));
    $finalSignerNip = $signatureSigner['nip'] ?? ($signatureMeta['signer_nip'] ?? null);
@endphp

@if(!empty($isDigitallySigned) && !empty($qrCodeSvg))
    <div class="tte-box">
        <div class="tte-qr">
            {!! $qrCodeSvg !!}
        </div>
        <div class="tte-info">
            <strong>Ditandatangani secara elektronik oleh:</strong>
            <span style="font-size: 8.5pt; font-weight: bold; color: #0f172a;">{{ $finalSignerName }}</span><br>
            @if(!empty($finalSignerNip) && $finalSignerNip !== '-')
                <span style="font-size: 7.5pt; color: #64748b;">NIP. {{ $finalSignerNip }}</span><br>
            @endif
            <span class="tte-bsre">Balai Sertifikasi Elektronik (BSrE) BSSN</span>
            <span style="font-size: 6.5pt; color: #94A3B8; word-break: break-all;">ID: {{ substr($letter->signature_hash ?? '', 0, 16) }}</span>
        </div>
    </div>
    <div class="signature-name">{{ $finalSignerName }}</div>
@else
    <div class="signature-space"></div>
    <div class="signature-name">{{ $finalSignerName }}</div>
@endif
