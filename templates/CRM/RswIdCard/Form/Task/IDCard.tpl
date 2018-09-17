{* HEADER *}

<div class="crm-block crm-form-block crm-contact-task-mailing-label-form-block">
<div class="messages status no-popup">{include file="CRM/Contact/Form/Task.tpl"}</div>

{* FIELDS *}

<div class="crm-section">
  <div class="label">{$form.label_name.label}</div>
  <div class="content">{$form.label_name.html} {help id="id-rswidcard-label-name-select" title=$form.label_name.label file="CRM/RswIdCard/Form/Task/IDCard.hlp"}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.print_bg_image.label}</div>
  <div class="content">{$form.print_bg_image.html} {help id="id-rswidcard-print-bg-image-checkbox" title=$form.print_bg_image.label file="CRM/RswIdCard/Form/Task/IDCard.hlp"}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.print_border.label}</div>
  <div class="content">{$form.print_border.html} {help id="id-rswidcard-print-border-checkbox" title=$form.print_border.label file="CRM/RswIdCard/Form/Task/IDCard.hlp"}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.round_corners.label}</div>
  <div class="content">{$form.round_corners.html} {help id="id-rswidcard-round-corners-checkbox" title=$form.round_corners.label file="CRM/RswIdCard/Form/Task/IDCard.hlp"}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.bg_image_bleed.label}</div>
  <div class="content">{$form.bg_image_bleed.html} {help id="id-rswidcard-bg-image-bleed-value" title=$form.bg_image_bleed.label file="CRM/RswIdCard/Form/Task/IDCard.hlp"}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.excl_existing.label}</div>
  <div class="content">{$form.excl_existing.html} {help id="id-rswidcard-excl-existing-checkbox" title=$form.excl_existing.label file="CRM/RswIdCard/Form/Task/IDCard.hlp"}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.is_test.label}</div>
  <div class="content">{$form.is_test.html} {help id="id-rswidcard-is-test-checkbox" title=$form.is_test.label file="CRM/RswIdCard/Form/Task/IDCard.hlp"}</div>
  <div class="clear"></div>
</div>
  
{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

</div>
