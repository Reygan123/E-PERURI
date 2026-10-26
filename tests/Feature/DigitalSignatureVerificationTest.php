<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Arsip\Database\Seeders\LetterTypeSeeder;
use App\Modules\Arsip\Models\Letter;
use App\Modules\Arsip\Models\LetterType;
use App\Modules\Arsip\Services\DigitalSignatureService;
use App\Modules\Arsip\Services\LetterPdfService;
use App\Modules\Platform\database\seeders\DevelopmentUserSeeder;
use App\Modules\Platform\database\seeders\NumberingFormatSeeder;
use App\Modules\Platform\database\seeders\OrgUnitSeeder;
use App\Modules\Platform\database\seeders\RoleAndPermissionSeeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DigitalSignatureVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => OrgUnitSeeder::class]);
        $this->artisan('db:seed', ['--class' => RoleAndPermissionSeeder::class]);
        $this->artisan('db:seed', ['--class' => DevelopmentUserSeeder::class]);
        $this->artisan('db:seed', ['--class' => NumberingFormatSeeder::class]);
        $this->artisan('db:seed', ['--class' => LetterTypeSeeder::class]);
    }

    public function test_digital_signature_service_can_sign_letter_and_generate_bsre_metadata(): void
    {
        $superadmin = User::where('username', 'superadmin')->firstOrFail();
        $type = LetterType::where('code', 'ND')->firstOrFail();
        $unit = DB::table('platform.org_units')->first();

        $letter = Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $type->id,
            'document_number' => '001/ND/IV/2026',
            'origin_unit_id' => $unit->id,
            'creator_employee_id' => $superadmin->employee_id,
            'destination_type' => 'internal_units',
            'subject' => 'Pengujian Tanda Tangan Elektronik BSrE',
            'letter_date' => Carbon::now()->toDateString(),
            'status' => 'approved',
            'confidentiality' => 'biasa',
            'urgency' => 'biasa',
            'content_body' => [
                'perihal' => 'Pengujian TTE BSrE',
                'body_text' => 'Ini adalah isi naskah dinas untuk pengujian TTE BSrE.',
            ],
        ]);

        $service = app(DigitalSignatureService::class);
        $signedLetter = $service->signDocument($letter, (string) $superadmin->employee_id);

        $this->assertNotNull($signedLetter->signature_hash);
        $this->assertNotNull($signedLetter->signed_at);
        $this->assertEquals($superadmin->employee_id, $signedLetter->signer_employee_id);
        $this->assertEquals('published', $signedLetter->status);

        $meta = $signedLetter->signature_metadata;
        $this->assertIsArray($meta);
        $this->assertStringContainsString('Balai Sertifikasi Elektronik', $meta['issuer']);
        $this->assertStringStartsWith('BSRE-PTKP-', $meta['certificate_serial']);
        $this->assertEquals('VALID', $meta['status']);
        $this->assertEquals('SHA256withRSA / PAdES-B-LT', $meta['algorithm']);
        $this->assertNotNull($meta['document_digest']);
    }

    public function test_public_verification_endpoint_returns_valid_data_for_signed_letter(): void
    {
        $superadmin = User::where('username', 'superadmin')->firstOrFail();
        $type = LetterType::where('code', 'ND')->firstOrFail();
        $unit = DB::table('platform.org_units')->first();

        $letter = Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $type->id,
            'document_number' => '002/ND/IV/2026',
            'origin_unit_id' => $unit->id,
            'creator_employee_id' => $superadmin->employee_id,
            'destination_type' => 'internal_units',
            'subject' => 'Surat Pengujian Verifikasi Publik',
            'letter_date' => Carbon::now()->toDateString(),
            'status' => 'approved',
            'confidentiality' => 'biasa',
            'urgency' => 'biasa',
            'content_body' => ['body_text' => 'Pengujian verifikasi publik.'],
        ]);

        $service = app(DigitalSignatureService::class);
        $signed = $service->signDocument($letter, (string) $superadmin->employee_id);

        $response = $this->get(route('document.verify', ['hash' => $signed->signature_hash]));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/VerifyDocument')
            ->where('verification.is_valid', true)
            ->where('verification.document_number', '002/ND/IV/2026')
            ->where('verification.subject', 'Surat Pengujian Verifikasi Publik')
            ->where('verification.signer_name', $superadmin->name)
        );
    }

    public function test_public_verification_endpoint_handles_invalid_hash(): void
    {
        $response = $this->get(route('document.verify', ['hash' => 'hash-palsu-tidak-ditemukan']));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Public/VerifyDocument')
            ->where('verification', null)
            ->where('hash', 'hash-palsu-tidak-ditemukan')
        );
    }

    public function test_letter_pdf_service_renders_tte_qr_code_block_when_signed(): void
    {
        $superadmin = User::where('username', 'superadmin')->firstOrFail();
        $type = LetterType::where('code', 'A')->firstOrFail();
        $unit = DB::table('platform.org_units')->first();

        $letter = Letter::create([
            'id' => (string) Str::uuid(),
            'type_id' => $type->id,
            'document_number' => '003/DIR/IV/2026',
            'origin_unit_id' => $unit->id,
            'creator_employee_id' => $superadmin->employee_id,
            'destination_type' => 'external_text',
            'destination_text' => 'Direktur Bank Indonesia',
            'subject' => 'Penyampaian Kertas Uang Spesimen',
            'letter_date' => Carbon::now()->toDateString(),
            'status' => 'approved',
            'confidentiality' => 'biasa',
            'urgency' => 'biasa',
            'content_body' => ['body_text' => 'Pengujian cetak PDF ber-QR Code.'],
        ]);

        $sigService = app(DigitalSignatureService::class);
        $signed = $sigService->signDocument($letter, (string) $superadmin->employee_id);

        $pdfService = app(LetterPdfService::class);
        $html = $pdfService->renderHtml($signed);

        $this->assertStringContainsString('tte-box', $html);
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('Balai Sertifikasi Elektronik (BSrE) BSSN', $html);
        $this->assertStringContainsString('Ditandatangani secara elektronik oleh', $html);
    }
}
