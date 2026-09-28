<?php

/**
 * @file plugins/generic/medgemmaParser/jobs/AnalyzeSubmissionJob.php
 *
 * Distributed under the GNU GPL v3.
 *
 * @class AnalyzeSubmissionJob
 *
 * @brief Background job that sends a submission's manuscript to MedGemma.
 *  Runs outside the author's request so submitting stays fast.
 */

namespace APP\plugins\generic\medgemmaParser\jobs;

use APP\plugins\generic\medgemmaParser\classes\AnalysisRepository;
use APP\plugins\generic\medgemmaParser\classes\PaperAnalyzer;
use APP\plugins\generic\medgemmaParser\classes\SageMakerClient;
use APP\plugins\generic\medgemmaParser\classes\TextExtractor;
use APP\plugins\generic\medgemmaParser\MedgemmaParserPlugin;
use PKP\jobs\BaseJob;
use PKP\plugins\PluginRegistry;
use Throwable;

class AnalyzeSubmissionJob extends BaseJob
{
    /** Model calls are slow and cost money: do not retry automatically. */
    public $tries = 1;

    public int $timeout = 600;

    public function __construct(protected int $submissionId, protected int $contextId)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $repository = new AnalysisRepository();

        try {
            PluginRegistry::loadCategory('generic', true, $this->contextId);
            /** @var ?MedgemmaParserPlugin $plugin */
            $plugin = PluginRegistry::getPlugin('generic', 'medgemmaparserplugin');
            if (!$plugin || !$plugin->isConfigured($this->contextId)) {
                $repository->markError($this->submissionId, 'The MedGemma plugin is disabled or not configured.');
                return;
            }

            $setting = fn (string $name) => $plugin->getSetting($this->contextId, $name);
            $analyzer = new PaperAnalyzer(
                new SageMakerClient(
                    $setting('awsRegion'),
                    $setting('awsAccessKeyId'),
                    $setting('awsSecretAccessKey'),
                    $setting('endpointName'),
                    $setting('payloadFormat') ?: SageMakerClient::FORMAT_MESSAGES,
                ),
                new TextExtractor(),
                (int) ($setting('maxInputChars') ?: 60000),
                (int) ($setting('maxOutputTokens') ?: 4096),
                $setting('outputLanguage') ?: 'Italian',
            );

            $file = $analyzer->findManuscript($this->submissionId, $this->contextId);
            if (!$file) {
                $repository->markError($this->submissionId, 'No manuscript file in a supported format (PDF, DOCX, ODT, TXT, HTML) was found.');
                return;
            }

            $repository->markRunning($this->submissionId, $file->getId(), $setting('endpointName'));
            $analysis = $analyzer->analyze($file);
            $repository->markDone($this->submissionId, $analysis['result'], $analysis['truncated']);
        } catch (Throwable $e) {
            $repository->markError($this->submissionId, $e->getMessage());
            error_log('medgemmaParser: submission ' . $this->submissionId . ': ' . $e->getMessage());
        }
    }
}
