<?php

use App\Modules\Arsip\Models\LetterApproval;
use App\Modules\Platform\Models\Employee;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$approvals = LetterApproval::where('status', 'pending')->get();
foreach ($approvals as $app) {
    $emp = Employee::with('user')->find($app->approver_employee_id);
    echo "ID: {$app->id} | Role: {$app->approver_role_title} | Emp: ".($emp?->name ?? 'None').' | Username: '.($emp?->user?->username ?? 'None')."\n";
}
