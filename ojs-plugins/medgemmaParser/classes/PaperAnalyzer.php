<?php

/**
 * @file plugins/generic/medgemmaParser/classes/PaperAnalyzer.php
 *
 * Distributed under the GNU GPL v3.
 *
 * @class PaperAnalyzer
 *
 * @brief Picks the manuscript file of a submission, extracts its text and
 *  asks MedGemma for structured metadata, clinical data and a pre-review
 *  checklist.
 */

namespace APP\plugins\generic\medgemmaParser\classes;

use APP\core\Services;
use APP\facades\Repo;
use Exception;
use PKP\db\DAORegistry;
use PKP\submission\Genre;
use PKP\submission\GenreDAO;
use PKP\submissionFile\SubmissionFile;

class PaperAnalyzer
{
    /** Preferred formats first: word processor files extract more cleanly than PDF. */
    private const MIMETYPE_PRIORITY = [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.oasis.opendocument.text',
        'application/pdf',
        'text/html',
        'text/plain',
    ];

    public function __construct(
        private SageMakerClient $client,
        private TextExtractor $extractor,
        private int $maxInputChars = 60000,
        private int $maxOutputTokens = 4096,
        private string $outputLanguage = 'Italian',
    ) {
    }

    /**
     * Find the main manuscript among the files uploaded at submission.
     */
    public function findManuscript(int $submissionId, int $contextId): ?SubmissionFile
    {
        /** @var GenreDAO $genreDao */
        $genreDao = DAORegistry::getDAO('GenreDAO');

        $candidates = Repo::submissionFile()->getCollector()
            ->filterBySubmissionIds([$submissionId])
            ->filterByFileStages([SubmissionFile::SUBMISSION_FILE_SUBMISSION])
            ->getMany()
            ->filter(function (SubmissionFile $file) use ($genreDao, $contextId) {
                if (!in_array($file->getData('mimetype'), TextExtractor::SUPPORTED_MIMETYPES)) {
                    return false;
                }
                /** @var ?Genre $genre */
                $genre = $genreDao->getById($file->getData('genreId'), $contextId);
                return $genre
                    && $genre->getCategory() == Genre::GENRE_CATEGORY_DOCUMENT
                    && !$genre->getSupplementary()
                    && !$genre->getDependent();
            })
            ->sort(function (SubmissionFile $a, SubmissionFile $b) {
                $priority = array_search($a->getData('mimetype'), self::MIMETYPE_PRIORITY)
                    <=> array_search($b->getData('mimetype'), self::MIMETYPE_PRIORITY);
                return $priority ?: $b->getId() <=> $a->getId();
            });

        return $candidates->first();
    }

    /**
     * @return array{result: array, truncated: bool}
     */
    public function analyze(SubmissionFile $file): array
    {
        $contents = Services::get('file')->fs->read($file->getData('path'));
        $text = $this->extractor->extract($contents, $file->getData('mimetype'));

        $truncated = mb_strlen($text) > $this->maxInputChars;
        if ($truncated) {
            // Keep the start (title, abstract, methods) and the end, where
            // ethics, funding and conflict of interest statements usually sit.
            $head = (int) floor($this->maxInputChars * 0.75);
            $tail = $this->maxInputChars - $head;
            $text = mb_substr($text, 0, $head) . "\n\n[...]\n\n" . mb_substr($text, -$tail);
        }

        $output = $this->client->generate(
            $this->systemPrompt(),
            "MANUSCRIPT:\n\"\"\"\n{$text}\n\"\"\"",
            $this->maxOutputTokens
        );

        return ['result' => $this->decodeJson($output), 'truncated' => $truncated];
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
You are an assistant for the editorial office of a peer-reviewed medical journal.
Read the manuscript and return ONLY a JSON object, with no text before or after it, matching this structure:

{
  "metadata": {
    "title": string|null,
    "authors": [{"name": string, "affiliation": string|null}],
    "corresponding_author": string|null,
    "abstract": string|null,
    "keywords": [string],
    "article_type": string|null,
    "sections": [string],
    "references_count": integer|null,
    "funding": string|null
  },
  "clinical": {
    "study_design": string|null,
    "population": string|null,
    "setting": string|null,
    "sample_size": integer|null,
    "interventions": [string],
    "comparators": [string],
    "primary_outcomes": [string],
    "secondary_outcomes": [string],
    "follow_up": string|null,
    "conditions": [{"name": string, "icd10": string|null}],
    "drugs": [string],
    "mesh_terms": [string],
    "trial_registration": string|null,
    "main_findings": string|null
  },
  "prereview": {
    "reporting_guideline": "CONSORT"|"PRISMA"|"STROBE"|"STARD"|"CARE"|"SPIRIT"|"ARRIVE"|"SRQR"|"other"|"none",
    "checklist": [{"item": string, "status": "present"|"partial"|"missing", "evidence": string|null}],
    "ethics_approval": {"status": "present"|"missing"|"not_applicable", "details": string|null},
    "informed_consent": {"status": "present"|"missing"|"not_applicable", "details": string|null},
    "conflicts_of_interest": {"status": "present"|"missing", "details": string|null},
    "data_availability": {"status": "present"|"missing", "details": string|null},
    "concerns": [string]
  }
}

Rules:
- Use only information stated in the manuscript. Use null or [] when something is not reported; never invent values.
- ICD-10 codes and MeSH terms are suggestions: include them only when the match is clear.
- "checklist" lists the key items of the reporting guideline that fits the study design.
- "evidence" is a short quote (max 25 words) from the manuscript.
- "concerns" lists methodological or reporting issues an editor should check.
- Copy metadata values in the manuscript's language. Write "main_findings", "details" and "concerns" in {$this->outputLanguage}.
PROMPT;
    }

    /**
     * Models sometimes wrap JSON in code fences or add a sentence around it.
     */
    private function decodeJson(string $output): array
    {
        $start = strpos($output, '{');
        $end = strrpos($output, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $decoded = json_decode(substr($output, $start, $end - $start + 1), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        throw new Exception('MedGemma did not return valid JSON. Output starts with: ' . mb_substr($output, 0, 500));
    }
}
