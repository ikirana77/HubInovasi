<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

const SUBMISSION_IMPACT_ROW_LIMIT = 20;
const SUBMISSION_IMPACT_EVIDENCE_TYPE = 'impact_evidence';

function submission_impact_text(mixed $value, int $maxlength): string
{
    $value = trim((string) $value);
    return function_exists('mb_substr') ? mb_substr($value, 0, $maxlength) : substr($value, 0, $maxlength);
}

function submission_impact_date(mixed $value): ?string
{
    $value = trim((string) $value);
    if ($value === '') return null;
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value ? $value : null;
}

function submission_impact_rows(mixed $rows): array
{
    return is_array($rows) ? array_slice(array_values($rows), 0, SUBMISSION_IMPACT_ROW_LIMIT) : [];
}

function submission_impact_payload(array $source): array
{
    $metrics = [];
    foreach (submission_impact_rows($source['impact_metrics'] ?? []) as $row) {
        if (!is_array($row)) continue;
        $metric = [
            'label' => submission_impact_text($row['label'] ?? '', 180),
            'value' => submission_impact_text($row['value'] ?? '', 120),
            'unit' => submission_impact_text($row['unit'] ?? '', 80),
            'baseline' => submission_impact_text($row['baseline'] ?? '', 120),
            'target' => submission_impact_text($row['target'] ?? '', 120),
            'measured_at' => submission_impact_date($row['measured_at'] ?? ''),
            'evidence_notes' => submission_impact_text($row['evidence_notes'] ?? '', 2000),
        ];
        if ($metric['label'] !== '') $metrics[] = $metric;
    }

    $evidence = [];
    foreach (submission_impact_rows($source['impact_evidence'] ?? []) as $row) {
        if (!is_array($row)) continue;
        $item = [
            'title' => submission_impact_text($row['title'] ?? '', 255),
            'url' => submission_impact_text($row['url'] ?? '', 1000),
            'description' => submission_impact_text($row['description'] ?? '', 2000),
        ];
        if ($item['title'] !== '') $evidence[] = $item;
    }

    $recognitions = [];
    foreach (submission_impact_rows($source['recognitions'] ?? []) as $row) {
        if (!is_array($row)) continue;
        $recognition = [
            'title' => submission_impact_text($row['title'] ?? '', 255),
            'organiser' => submission_impact_text($row['organiser'] ?? '', 255),
            'level' => submission_impact_text($row['level'] ?? '', 120),
            'award_date' => submission_impact_date($row['award_date'] ?? ''),
            'description' => submission_impact_text($row['description'] ?? '', 2000),
            'evidence_url' => submission_impact_text($row['evidence_url'] ?? '', 500),
        ];
        if ($recognition['title'] !== '') $recognitions[] = $recognition;
    }

    return ['metrics' => $metrics, 'evidence' => $evidence, 'recognitions' => $recognitions];
}

function submission_impact_validation_errors(array $payload, bool $requiredForReview = false): array
{
    $errors = [];
    $metrics = $payload['metrics'] ?? [];
    $evidence = $payload['evidence'] ?? [];
    $recognitions = $payload['recognitions'] ?? [];

    if ($requiredForReview && !$metrics) $errors[] = 'Sekurang-kurangnya satu metrik impak diperlukan.';
    if ($requiredForReview && !$evidence) $errors[] = 'Sekurang-kurangnya satu bukti impak diperlukan.';
    foreach ($metrics as $metric) {
        if (($metric['value'] ?? '') === '' && ($metric['target'] ?? '') === '') $errors[] = 'Setiap metrik memerlukan nilai semasa atau sasaran.';
    }
    foreach ($evidence as $item) {
        if (!valid_external_url($item['url'] ?? null)) $errors[] = 'Setiap bukti memerlukan pautan HTTP atau HTTPS yang sah.';
    }
    foreach ($recognitions as $recognition) {
        if (($recognition['evidence_url'] ?? '') !== '' && !valid_external_url($recognition['evidence_url'])) {
            $errors[] = 'Pautan bukti anugerah mesti menggunakan HTTP atau HTTPS.';
        }
    }
    return array_values(array_unique($errors));
}

function submission_impact_details_for_submission(int $submissionId): array
{
    if ($submissionId < 1) return ['metrics' => [], 'evidence' => [], 'recognitions' => []];
    $pdo = db();

    $metricStatement = $pdo->prepare('SELECT metric_label label, metric_value value, metric_unit unit, baseline_value baseline, target_value target, measured_at, evidence_notes FROM submission_metrics WHERE submission_id = ? ORDER BY sort_order, id');
    $metricStatement->execute([$submissionId]);

    $evidenceStatement = $pdo->prepare('SELECT alt_text title, external_url url, caption description FROM submission_media WHERE submission_id = ? AND media_type = ? ORDER BY sort_order, id');
    $evidenceStatement->execute([$submissionId, SUBMISSION_IMPACT_EVIDENCE_TYPE]);

    $awardStatement = $pdo->prepare('SELECT award_title title, organiser, award_level level, award_date, description, evidence_path evidence_url FROM submission_awards WHERE submission_id = ? ORDER BY sort_order, id');
    $awardStatement->execute([$submissionId]);

    return [
        'metrics' => $metricStatement->fetchAll(),
        'evidence' => $evidenceStatement->fetchAll(),
        'recognitions' => $awardStatement->fetchAll(),
    ];
}

function save_submission_impact_details(int $submissionId, array $payload): void
{
    if ($submissionId < 1) throw new InvalidArgumentException('Submission tidak sah.');
    $pdo = db();
    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM submission_metrics WHERE submission_id = ?')->execute([$submissionId]);
        $metricInsert = $pdo->prepare('INSERT INTO submission_metrics (submission_id, metric_label, metric_value, metric_unit, baseline_value, target_value, measured_at, evidence_notes, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        foreach (($payload['metrics'] ?? []) as $sortOrder => $metric) {
            $metricInsert->execute([$submissionId, $metric['label'], $metric['value'] ?: null, $metric['unit'] ?: null, $metric['baseline'] ?: null, $metric['target'] ?: null, $metric['measured_at'], $metric['evidence_notes'] ?: null, $sortOrder]);
        }

        $pdo->prepare('DELETE FROM submission_media WHERE submission_id = ? AND media_type = ?')->execute([$submissionId, SUBMISSION_IMPACT_EVIDENCE_TYPE]);
        $evidenceInsert = $pdo->prepare('INSERT INTO submission_media (submission_id, media_type, external_url, alt_text, caption, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
        foreach (($payload['evidence'] ?? []) as $sortOrder => $item) {
            $evidenceInsert->execute([$submissionId, SUBMISSION_IMPACT_EVIDENCE_TYPE, $item['url'] ?: null, $item['title'], $item['description'] ?: null, $sortOrder]);
        }

        $pdo->prepare('DELETE FROM submission_awards WHERE submission_id = ?')->execute([$submissionId]);
        $awardInsert = $pdo->prepare('INSERT INTO submission_awards (submission_id, award_title, organiser, award_level, award_date, description, evidence_path, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach (($payload['recognitions'] ?? []) as $sortOrder => $recognition) {
            $awardInsert->execute([$submissionId, $recognition['title'], $recognition['organiser'] ?: null, $recognition['level'] ?: null, $recognition['award_date'], $recognition['description'] ?: null, $recognition['evidence_url'] ?: null, $sortOrder]);
        }
        if ($ownsTransaction) $pdo->commit();
    } catch (Throwable $exception) {
        if ($ownsTransaction && $pdo->inTransaction()) $pdo->rollBack();
        throw $exception;
    }
}
