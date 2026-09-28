<?php

/**
 * @file plugins/generic/medgemmaParser/classes/AnalysisRepository.php
 *
 * Distributed under the GNU GPL v3.
 *
 * @class AnalysisRepository
 *
 * @brief Reads and writes rows of the medgemma_analyses table.
 */

namespace APP\plugins\generic\medgemmaParser\classes;

use Illuminate\Support\Facades\DB;
use PKP\core\Core;

class AnalysisRepository
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_DONE = 'done';
    public const STATUS_ERROR = 'error';

    private const TABLE = 'medgemma_analyses';

    public function getBySubmissionId(int $submissionId): ?object
    {
        return DB::table(self::TABLE)->where('submission_id', $submissionId)->first();
    }

    public function markPending(int $submissionId): void
    {
        $this->save($submissionId, [
            'status' => self::STATUS_PENDING,
            'error' => null,
        ]);
    }

    public function markRunning(int $submissionId, int $submissionFileId, string $endpointName): void
    {
        $this->save($submissionId, [
            'status' => self::STATUS_RUNNING,
            'submission_file_id' => $submissionFileId,
            'endpoint_name' => $endpointName,
            'error' => null,
        ]);
    }

    public function markDone(int $submissionId, array $result, bool $truncated): void
    {
        $this->save($submissionId, [
            'status' => self::STATUS_DONE,
            'result' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'truncated' => $truncated,
            'error' => null,
        ]);
    }

    public function markError(int $submissionId, string $error): void
    {
        $this->save($submissionId, [
            'status' => self::STATUS_ERROR,
            'error' => mb_substr($error, 0, 5000),
        ]);
    }

    private function save(int $submissionId, array $values): void
    {
        $now = Core::getCurrentDate();
        $values['date_modified'] = $now;

        $exists = DB::table(self::TABLE)->where('submission_id', $submissionId)->exists();
        if ($exists) {
            DB::table(self::TABLE)->where('submission_id', $submissionId)->update($values);
            return;
        }
        DB::table(self::TABLE)->insert($values + [
            'submission_id' => $submissionId,
            'date_created' => $now,
        ]);
    }
}
