<?php

/**
 * @file plugins/generic/medgemmaParser/MedgemmaParserHandler.php
 *
 * Distributed under the GNU GPL v3.
 *
 * @class MedgemmaParserHandler
 *
 * @brief Lets editors queue a new analysis from the workflow tab.
 */

namespace APP\plugins\generic\medgemmaParser;

use APP\core\Application;
use APP\core\Request;
use APP\handler\Handler;
use PKP\security\authorization\SubmissionAccessPolicy;
use PKP\security\Role;

class MedgemmaParserHandler extends Handler
{
    public function __construct(protected MedgemmaParserPlugin $plugin)
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR],
            ['analyze']
        );
    }

    /**
     * @copydoc PKPHandler::authorize()
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Queue a new analysis and go back to the workflow page.
     *
     * @param array $args
     * @param Request $request
     */
    public function analyze($args, $request)
    {
        if (!$request->isPost() || !$request->checkCSRF()) {
            $request->getDispatcher()->handle404();
        }

        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);
        $contextId = $submission->getData('contextId');
        if ($this->plugin->isConfigured($contextId)) {
            $this->plugin->queueAnalysis($submission->getId(), $contextId);
        }

        $request->redirect(null, 'workflow', 'access', [$submission->getId()]);
    }
}
