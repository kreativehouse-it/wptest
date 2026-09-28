<?php

/**
 * @file plugins/generic/medgemmaParser/MedgemmaParserSettingsForm.php
 *
 * Distributed under the GNU GPL v3.
 *
 * @class MedgemmaParserSettingsForm
 *
 * @brief SageMaker connection and analysis settings, per journal.
 */

namespace APP\plugins\generic\medgemmaParser;

use APP\plugins\generic\medgemmaParser\classes\SageMakerClient;
use APP\template\TemplateManager;
use PKP\form\Form;
use PKP\form\validation\FormValidator;
use PKP\form\validation\FormValidatorCSRF;
use PKP\form\validation\FormValidatorCustom;
use PKP\form\validation\FormValidatorInSet;
use PKP\form\validation\FormValidatorPost;
use PKP\form\validation\FormValidatorRegExp;

class MedgemmaParserSettingsForm extends Form
{
    private const FIELDS = [
        'awsRegion' => 'string',
        'awsAccessKeyId' => 'string',
        'endpointName' => 'string',
        'payloadFormat' => 'string',
        'maxInputChars' => 'int',
        'maxOutputTokens' => 'int',
        'outputLanguage' => 'string',
        'autoAnalyze' => 'bool',
    ];

    public function __construct(protected MedgemmaParserPlugin $plugin, protected int $contextId)
    {
        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

        $prefix = 'plugins.generic.medgemmaParser.settings.';
        $this->addCheck(new FormValidatorRegExp($this, 'awsRegion', 'required', $prefix . 'awsRegion.invalid', '/^[a-z]{2}(-[a-z]+)+-\d$/'));
        $this->addCheck(new FormValidator($this, 'awsAccessKeyId', 'required', $prefix . 'awsAccessKeyId.required'));
        $this->addCheck(new FormValidatorRegExp($this, 'endpointName', 'required', $prefix . 'endpointName.invalid', '/^[a-zA-Z0-9](-*[a-zA-Z0-9]){0,62}$/'));
        $this->addCheck(new FormValidatorInSet($this, 'payloadFormat', 'required', $prefix . 'payloadFormat.invalid', [SageMakerClient::FORMAT_MESSAGES, SageMakerClient::FORMAT_INPUTS]));
        $this->addCheck(new FormValidatorCustom($this, 'maxInputChars', 'required', $prefix . 'number.invalid', fn ($value) => ctype_digit((string) $value) && $value >= 1000));
        $this->addCheck(new FormValidatorCustom($this, 'maxOutputTokens', 'required', $prefix . 'number.invalid', fn ($value) => ctype_digit((string) $value) && $value >= 256));
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    public function initData()
    {
        foreach (array_keys(self::FIELDS) as $name) {
            $this->setData($name, $this->plugin->getSetting($this->contextId, $name));
        }
        $this->setData('payloadFormat', $this->getData('payloadFormat') ?: SageMakerClient::FORMAT_MESSAGES);
        $this->setData('maxInputChars', $this->getData('maxInputChars') ?: 60000);
        $this->setData('maxOutputTokens', $this->getData('maxOutputTokens') ?: 4096);
        $this->setData('outputLanguage', $this->getData('outputLanguage') ?: 'Italian');
    }

    /**
     * The stored secret is never sent back to the browser, so an empty field
     * keeps it. Checked here rather than with a "required" validator, which
     * would also make the browser reject the empty field.
     */
    public function validate($callHooks = true)
    {
        if (!$this->getData('awsSecretAccessKey') && !$this->plugin->getSetting($this->contextId, 'awsSecretAccessKey')) {
            $this->addError('awsSecretAccessKey', __('plugins.generic.medgemmaParser.settings.awsSecretAccessKey.required'));
            $this->addErrorField('awsSecretAccessKey');
        }
        return parent::validate($callHooks);
    }

    public function readInputData()
    {
        $this->readUserVars(array_merge(array_keys(self::FIELDS), ['awsSecretAccessKey']));
    }

    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'pluginName' => $this->plugin->getName(),
            'hasSecret' => (bool) $this->plugin->getSetting($this->contextId, 'awsSecretAccessKey'),
            'payloadFormats' => [
                SageMakerClient::FORMAT_MESSAGES => 'plugins.generic.medgemmaParser.settings.payloadFormat.messages',
                SageMakerClient::FORMAT_INPUTS => 'plugins.generic.medgemmaParser.settings.payloadFormat.inputs',
            ],
        ]);
        return parent::fetch($request, $template, $display);
    }

    public function execute(...$functionArgs)
    {
        foreach (self::FIELDS as $name => $type) {
            $value = $this->getData($name);
            $value = match ($type) {
                'int' => (int) $value,
                'bool' => (bool) $value,
                default => trim((string) $value),
            };
            $this->plugin->updateSetting($this->contextId, $name, $value, $type);
        }
        if ($secret = trim((string) $this->getData('awsSecretAccessKey'))) {
            $this->plugin->updateSetting($this->contextId, 'awsSecretAccessKey', $secret, 'string');
        }
        parent::execute(...$functionArgs);
    }
}
