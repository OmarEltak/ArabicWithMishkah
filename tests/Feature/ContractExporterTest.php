<?php

declare(strict_types=1);

use App\Models\Contract;
use App\Models\User;
use App\Services\Contracts\ContractExporter;

it('produces a valid DOCX file from a contract', function () {
    $user = User::factory()->create();
    $contract = Contract::create([
        'user_id' => $user->id,
        'title' => 'Test NDA',
        'body' => "MUTUAL NON-DISCLOSURE AGREEMENT\n\n1. PARTIES\n\nThis agreement is between Foo and Bar.\n\n2. TERM\n\n* one year\n* renewable\n\nThe parties agree to the above.",
        'status' => 'draft',
        'version' => 1,
    ]);

    $exporter = new ContractExporter;
    $bytes = $exporter->toDocx($contract);

    // DOCX is a ZIP starting with PK signature.
    expect(substr($bytes, 0, 2))->toBe('PK');
    expect(strlen($bytes))->toBeGreaterThan(2000);

    $name = $exporter->suggestedFilename($contract);
    expect($name)->toEndWith('.docx');
    expect($name)->toContain('test-nda');
});
