<?php

/**
 * @file plugins/generic/medgemmaParser/MedgemmaParserPlugin.php
 *
 * Distributed under the GNU GPL v3.
 *
 * @class MedgemmaParserPlugin
 *
 * @brief Analyses the manuscripts uploaded by authors with a MedGemma model
 *  deployed on an AWS SageMaker endpoint and shows the result to editors.
 */

namespace APP\plugins\generic\medgemmaParser;

use APP\core\Application;
use APP\plugins\generic\medgemmaParser\classes\AnalysisRepository;
use APP\plugins\generic\medgemmaParser\classes\MedgemmaSchemaMigration;
use APP\plugins\generic\medgemmaParser\jobs\AnalyzeSubmissionJob;
use APP\submission\Submission;
use APP\template\TemplateManager;
use Illuminate\Support\Facades\Event;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\observers\events\SubmissionSubmitted;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\security\Role;
use PKP\user\User;

class MedgemmaParserPlugin extends GenericPlugin
{
    /** Page name used by the "analyse again" handler */
    public const HANDLER_PAGE = 'medgemmaParser';

    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if (Application::isUnderMaintenance()) {
            return $success;
        }
        if ($success && $this->getEnabled($mainContextId)) {
            Event::listen(SubmissionSubmitted::class, [$this, 'onSubmissionSubmitted']);
            Hook::add('TemplateManager::display', [$this, 'addWorkflowStyles']);
            Hook::add('Template::Workflow', [$this, 'addWorkflowTab']);
            Hook::add('LoadHandler', [$this, 'setupHandler']);
        }
        return $success;
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName()
    {
        return __('plugins.generic.medgemmaParser.displayName');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription()
    {
        return __('plugins.generic.medgemmaParser.description');
    }

    /**
     * @copydoc Plugin::getInstallMigration()
     */
    public function getInstallMigration()
    {
        return new MedgemmaSchemaMigration();
    }

    /**
     * Queue the analysis as soon as the author completes the submission.
     */
    public function onSubmissionSubmitted(SubmissionSubmitted $event): void
    {
        $contextId = $event->context->getId();
        if (!$this->getSetting($contextId, 'autoAnalyze') || !$this->isConfigured($contextId)) {
            return;
        }
        $this->queueAnalysis($event->submission->getId(), $contextId);
    }

    /**
     * Mark the submission as pending and dispatch the background job.
     */
    public function queueAnalysis(int $submissionId, int $contextId): void
    {
        (new AnalysisRepository())->markPending($submissionId);
        dispatch(new AnalyzeSubmissionJob($submissionId, $contextId));
    }

    /**
     * Whether the SageMaker connection settings are filled in.
     */
    public function isConfigured(int $contextId): bool
    {
        foreach (['awsRegion', 'awsAccessKeyId', 'awsSecretAccessKey', 'endpointName'] as $name) {
            if (!$this->getSetting($contextId, $name)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Load the tab's stylesheet on the workflow page only.
     *
     * @param string $hookName
     * @param array $args [$templateMgr, $template]
     */
    public function addWorkflowStyles($hookName, $args)
    {
        [$templateMgr, $template] = $args;
        if ($template !== 'workflow/workflow.tpl') {
            return false;
        }
        $request = Application::get()->getRequest();
        $templateMgr->addStyleSheet(
            'medgemmaParserWorkflow',
            $request->getBaseUrl() . '/' . $this->getPluginPath() . '/css/workflowTab.css',
            ['contexts' => 'backend']
        );
        return false;
    }

    /**
     * Add an "AI analysis" tab to the editorial workflow page.
     *
     * @param string $hookName
     * @param array $args [&$params, $smarty, &$output]
     */
    public function addWorkflowTab($hookName, $args)
    {
        $smarty = $args[1];
        $output = & $args[2];

        $submission = $smarty->getTemplateVars('submission');
        if (!$submission instanceof Submission) {
            return false;
        }

        $request = Application::get()->getRequest();
        if (!$this->canUseAnalysis($request->getUser(), $submission->getData('contextId'))) {
            return false;
        }
        $analysis = (new AnalysisRepository())->getBySubmissionId($submission->getId());

        $smarty->assign([
            'medgemmaAnalysis' => $analysis,
            'medgemmaResult' => $analysis && $analysis->result ? json_decode($analysis->result, true) : null,
            'medgemmaConfigured' => $this->isConfigured($submission->getData('contextId')),
            'medgemmaAnalyzeUrl' => $request->getDispatcher()->url(
                $request,
                Application::ROUTE_PAGE,
                null,
                self::HANDLER_PAGE,
                'analyze',
                null,
                ['submissionId' => $submission->getId()]
            ),
        ]);
        $output .= $smarty->fetch($this->getTemplateResource('workflowTab.tpl'));
        return false;
    }

    /**
     * Route requests for the "analyse again" action to the plugin handler.
     *
     * @param string $hookName
     * @param array $args [&$page, &$op, &$sourceFile, &$handler]
     */
    public function setupHandler($hookName, $args)
    {
        $page = $args[0];
        $handler = & $args[3];
        if ($page !== self::HANDLER_PAGE) {
            return false;
        }
        $handler = new MedgemmaParserHandler($this);
        return true;
    }

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $verb)
    {
        $router = $request->getRouter();
        return array_merge(
            $this->getEnabled() ? [
                new LinkAction(
                    'settings',
                    new AjaxModal(
                        $router->url($request, null, null, 'manage', null, ['verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic']),
                        $this->getDisplayName()
                    ),
                    __('manager.plugins.settings'),
                    null
                ),
            ] : [],
            parent::getActions($request, $verb)
        );
    }

    /**
     * @copydoc Plugin::manage()
     */
    public function manage($args, $request)
    {
        if ($request->getUserVar('verb') !== 'settings') {
            return parent::manage($args, $request);
        }

        $context = $request->getContext();
        TemplateManager::getManager($request)->registerPlugin('function', 'plugin_url', [$this, 'smartyPluginUrl']);
        $form = new MedgemmaParserSettingsForm($this, $context->getId());

        if ($request->getUserVar('save')) {
            $form->readInputData();
            if ($form->validate()) {
                $form->execute();
                return new JSONMessage(true);
            }
        } else {
            $form->initData();
        }
        return new JSONMessage(true, $form->fetch($request));
    }

    /**
     * Only journal managers, section editors and site admins see the analysis.
     */
    public function canUseAnalysis(?User $user, int $contextId): bool
    {
        return $user && (
            $user->hasRole([Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR], $contextId)
            || $user->hasRole([Role::ROLE_ID_SITE_ADMIN], Application::CONTEXT_SITE)
        );
    }
}
