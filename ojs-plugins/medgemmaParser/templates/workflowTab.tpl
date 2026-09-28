{**
 * plugins/generic/medgemmaParser/templates/workflowTab.tpl
 *
 * Distributed under the GNU GPL v3.
 *
 * "AI analysis" tab on the editorial workflow page.
 *
 * v-pre stops Vue from compiling model output, which may contain "{{ }}".
 *}
<tab id="medgemmaAnalysis" label="{translate key="plugins.generic.medgemmaParser.tab"}">
<div class="medgemmaAnalysis" v-pre>
	<p class="medgemmaAnalysis__note">{translate key="plugins.generic.medgemmaParser.disclaimer"}</p>

	{if !$medgemmaAnalysis}
		<p>{translate key="plugins.generic.medgemmaParser.status.none"}</p>
	{else}
		<p>
			<span class="medgemmaAnalysis__status medgemmaAnalysis__status--{$medgemmaAnalysis->status|escape}">
				{translate key="plugins.generic.medgemmaParser.status.`$medgemmaAnalysis->status`"}
			</span>
			{translate key="plugins.generic.medgemmaParser.updated" date=$medgemmaAnalysis->date_modified|date_format:$datetimeFormatShort}
		</p>
		{if $medgemmaAnalysis->status === 'error'}
			<p class="medgemmaAnalysis__missing">{$medgemmaAnalysis->error|escape}</p>
		{/if}
		{if $medgemmaAnalysis->truncated}
			<p class="medgemmaAnalysis__note">{translate key="plugins.generic.medgemmaParser.truncated"}</p>
		{/if}
	{/if}

	{if $medgemmaConfigured}
		<form method="post" action="{$medgemmaAnalyzeUrl|escape}">
			{csrf}
			<button type="submit" class="pkpButton">
				{if $medgemmaAnalysis}
					{translate key="plugins.generic.medgemmaParser.reanalyze"}
				{else}
					{translate key="plugins.generic.medgemmaParser.analyze"}
				{/if}
			</button>
		</form>
	{else}
		<p class="medgemmaAnalysis__note">{translate key="plugins.generic.medgemmaParser.notConfigured"}</p>
	{/if}

	{if $medgemmaResult}
		{assign var="meta" value=$medgemmaResult.metadata|default:[]}
		{assign var="clinical" value=$medgemmaResult.clinical|default:[]}
		{assign var="prereview" value=$medgemmaResult.prereview|default:[]}

		<h3>{translate key="plugins.generic.medgemmaParser.section.metadata"}</h3>
		<table>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.title"}</th><td>{$meta.title|escape}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.authors"}</th><td>
				{foreach from=$meta.authors|default:[] item=author}
					{$author.name|escape}{if $author.affiliation} <span class="medgemmaAnalysis__note">({$author.affiliation|escape})</span>{/if}<br>
				{/foreach}
			</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.correspondingAuthor"}</th><td>{$meta.corresponding_author|escape}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.articleType"}</th><td>{$meta.article_type|escape}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.keywords"}</th><td>{foreach from=$meta.keywords|default:[] item=value name=kw}{$value|escape}{if !$smarty.foreach.kw.last}; {/if}{/foreach}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.abstract"}</th><td>{$meta.abstract|escape|nl2br}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.sections"}</th><td>{foreach from=$meta.sections|default:[] item=value name=sec}{$value|escape}{if !$smarty.foreach.sec.last} · {/if}{/foreach}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.referencesCount"}</th><td>{$meta.references_count|escape}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.funding"}</th><td>{$meta.funding|escape}</td></tr>
		</table>

		<h3>{translate key="plugins.generic.medgemmaParser.section.clinical"}</h3>
		<table>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.studyDesign"}</th><td>{$clinical.study_design|escape}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.population"}</th><td>{$clinical.population|escape}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.setting"}</th><td>{$clinical.setting|escape}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.sampleSize"}</th><td>{$clinical.sample_size|escape}</td></tr>
			{foreach from=['interventions', 'comparators', 'primary_outcomes', 'secondary_outcomes', 'drugs', 'mesh_terms'] item=listField}
				<tr><th>{translate key="plugins.generic.medgemmaParser.field.`$listField`"}</th><td>
					{foreach from=$clinical.$listField|default:[] item=value}{$value|escape}<br>{/foreach}
				</td></tr>
			{/foreach}
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.conditions"}</th><td>
				{foreach from=$clinical.conditions|default:[] item=condition}
					{$condition.name|escape}{if $condition.icd10} <code>{$condition.icd10|escape}</code>{/if}<br>
				{/foreach}
			</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.followUp"}</th><td>{$clinical.follow_up|escape}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.trialRegistration"}</th><td>{$clinical.trial_registration|escape}</td></tr>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.mainFindings"}</th><td>{$clinical.main_findings|escape|nl2br}</td></tr>
		</table>

		<h3>{translate key="plugins.generic.medgemmaParser.section.prereview"}</h3>
		<table>
			<tr><th>{translate key="plugins.generic.medgemmaParser.field.reportingGuideline"}</th><td>{$prereview.reporting_guideline|escape}</td></tr>
			{foreach from=['ethics_approval', 'informed_consent', 'conflicts_of_interest', 'data_availability'] item=statement}
				{assign var="item" value=$prereview.$statement|default:[]}
				<tr><th>{translate key="plugins.generic.medgemmaParser.field.`$statement`"}</th><td>
					<span class="medgemmaAnalysis__{$item.status|default:'missing'|escape}">{$item.status|escape}</span>
					{if $item.details}<br>{$item.details|escape}{/if}
				</td></tr>
			{/foreach}
		</table>

		{if $prereview.checklist}
			<table>
				<tr>
					<th>{translate key="plugins.generic.medgemmaParser.field.checklistItem"}</th>
					<th>{translate key="plugins.generic.medgemmaParser.field.checklistStatus"}</th>
					<th>{translate key="plugins.generic.medgemmaParser.field.checklistEvidence"}</th>
				</tr>
				{foreach from=$prereview.checklist item=check}
					<tr>
						<td>{$check.item|escape}</td>
						<td class="medgemmaAnalysis__{$check.status|escape}">{$check.status|escape}</td>
						<td>{$check.evidence|escape}</td>
					</tr>
				{/foreach}
			</table>
		{/if}

		{if $prereview.concerns}
			<h3>{translate key="plugins.generic.medgemmaParser.field.concerns"}</h3>
			<ul>
				{foreach from=$prereview.concerns item=concern}<li>{$concern|escape}</li>{/foreach}
			</ul>
		{/if}

		<details>
			<summary>{translate key="plugins.generic.medgemmaParser.rawJson"}</summary>
			<pre style="white-space: pre-wrap;">{$medgemmaAnalysis->result|escape}</pre>
		</details>
	{/if}
</div>
</tab>
