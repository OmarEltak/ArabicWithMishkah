<?php
declare(strict_types=1);

use App\Services\Verification\CorpusCitationGate;

it('does not flag bare contract-clause headings (المادة N:) as statute citations', function () {
    $gate = new CorpusCitationGate();
    $contractBody = <<<AR
المادة 1: موضوع العقد
بموجب هذا العقد، تلتزم الشركة بتعيين الطرف الثاني في وظيفة "مبرمج مواقع".

المادة 2: مدة العقد
مدة هذا العقد سنة واحدة.

المادة 3: الراتب والمزايا
يتقاضى الطرف الثاني راتباً أساسياً.
AR;
    $report = $gate->verify($contractBody, 'EG');
    expect($report->totalCitations())->toBe(0);
});

it('still flags real external statute citations', function () {
    $gate = new CorpusCitationGate();
    $contractBody = 'وفقاً للمادة 148 من القانون المدني المصري، يلتزم الطرفان بحسن النية.';
    $report = $gate->verify($contractBody, 'EG');
    expect($report->totalCitations())->toBeGreaterThan(0);
});

it('flags article references prefixed with linking prepositions (للمادة N من)', function () {
    $gate = new CorpusCitationGate();
    $body = 'يلتزم البائع بنقل الملكية للمادة 418 من القانون المدني المصري.';
    $report = $gate->verify($body, 'EG');
    expect($report->totalCitations())->toBeGreaterThan(0);
});
