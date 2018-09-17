CRM.$(function($) {
  // Firstly try hiding the field assuming that its label hasn't been modified. This is quicker and is usually unnoticeable.
    
  // Hide the "RSW ID Card Key Hash" custom field on the contact summary page's inline fields (view)
  $('.crm-label:contains("RSW ID Card Key Hash")').parent().hide();
  
  // Hide the "RSW ID Card Key Hash" custom field in the contact summary inline fields edit form, and also in the Edit Contact form
  $('.custom_field-row .label label:contains("RSW ID Card Key Hash")').parent().parent().hide();

  
  // Secondly get the custom field's label from the database via API and then hide the field. This is a bit slower and if used on its
  // own would be noticeable particularly on the inline Edit form.
  
  // Get the label of the custom field that is to be hidden
  CRM.api3('CustomField', 'getvalue', {
    "return": "label",
    "name": "rswid_card_hash",
    "custom_group_id": "rsw_id_card"
  }).done(function(result) {
    var cfLabel = result.result;

    // Hide the "RSW ID Card Key Hash" custom field on the contact summary page's inline fields (view)
    $('.crm-label:contains(' + cfLabel + ')').parent().hide();
    
    // Hide the "RSW ID Card Key Hash" custom field in the contact summary inline fields edit form, and also in the Edit Contact form
    $('.custom_field-row .label label:contains(' + cfLabel + ')').parent().parent().hide();
  });

} );  
