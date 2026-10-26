<?php

declare(strict_types=1);

namespace App\Modules\Arsip\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Arsip\Services\DigitalSignatureService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicVerificationController extends Controller
{
    public function __construct(
        protected DigitalSignatureService $digitalSignatureService
    ) {}

    /**
     * Public document verification endpoint.
     */
    public function verify(Request $request, string $hash): Response
    {
        $verificationData = $this->digitalSignatureService->verifySignature($hash);

        return Inertia::render('Public/VerifyDocument', [
            'verification' => $verificationData,
            'hash' => $hash,
        ]);
    }
}
