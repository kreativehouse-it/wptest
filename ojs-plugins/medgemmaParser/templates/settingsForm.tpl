{**
 * plugins/generic/medgemmaParser/templates/settingsForm.tpl
 *
 * Distributed under the GNU GPL v3.
 *
 * MedGemma parser plugin settings
 *}
<script>
	$(function() {ldelim}
		$('#medgemmaParserSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="medgemmaParserSettingsForm" method="post" action="{url router=\PKP\core\PKPApplication::ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="medgemmaParserSettingsFormNotification"}

	<p>{translate key="plugins.generic.medgemmaParser.settings.description"}</p>

	{fbvFormArea id="medgemmaParserAwsArea" title="plugins.generic.medgemmaParser.settings.aws"}
		{fbvFormSection}
			{fbvElement type="text" id="awsRegion" value=$awsRegion required=true label="plugins.generic.medgemmaParser.settings.awsRegion" size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="endpointName" value=$endpointName required=true label="plugins.generic.medgemmaParser.settings.endpointName" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="awsAccessKeyId" value=$awsAccessKeyId required=true label="plugins.generic.medgemmaParser.settings.awsAccessKeyId" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		{fbvFormSection}
			{if $hasSecret}
				{fbvElement type="text" password=true id="awsSecretAccessKey" value="" label="plugins.generic.medgemmaParser.settings.awsSecretAccessKey.keep" size=$fbvStyles.size.MEDIUM}
			{else}
				{fbvElement type="text" password=true id="awsSecretAccessKey" value="" required=true label="plugins.generic.medgemmaParser.settings.awsSecretAccessKey" size=$fbvStyles.size.MEDIUM}
			{/if}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="select" id="payloadFormat" from=$payloadFormats selected=$payloadFormat label="plugins.generic.medgemmaParser.settings.payloadFormat" size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="medgemmaParserAnalysisArea" title="plugins.generic.medgemmaParser.settings.analysis"}
		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="autoAnalyze" checked=$autoAnalyze label="plugins.generic.medgemmaParser.settings.autoAnalyze"}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="maxInputChars" value=$maxInputChars required=true label="plugins.generic.medgemmaParser.settings.maxInputChars" size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="maxOutputTokens" value=$maxOutputTokens required=true label="plugins.generic.medgemmaParser.settings.maxOutputTokens" size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvElement type="text" id="outputLanguage" value=$outputLanguage label="plugins.generic.medgemmaParser.settings.outputLanguage" size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
