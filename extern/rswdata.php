<?php
/* External (to CiviCRM) portion of the RSW ID Card extension. This provides a CMS-independent web page that is accessed
 * using the ID card's QR code.
 * 
 +--------------------------------------------------------------------+
 | Copyright Andrew Thompson. All rights reserved.                    |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty.                     |
 +--------------------------------------------------------------------+
 *
 */

// Load config file. This includes the path to the CiviCRM config file and the CMS type.
$rsw_config = require __DIR__ . '/config.local.php';

// Perform bootstrap of CiviCRM
// Load the CiviCRM config file (path is specified in this extension's own config file)
require_once $rsw_config['civicrm_config_file'];

// The following 3 require_once lines are a temporary workaround. While the Joomla 5 version of CiviCRM does not include
// PSR Log (due to it conflicting with Joomla's), manually load the PSR Log files from Joomla's libraries/vendor directory
if ($rsw_config['cms'] == "joomla") {
  require_once $rsw_config['cms_root'] . '/libraries/vendor/psr/log/src/LoggerTrait.php';
  require_once $rsw_config['cms_root'] . '/libraries/vendor/psr/log/src/LoggerInterface.php';
  require_once $rsw_config['cms_root'] . '/libraries/vendor/psr/log/src/AbstractLogger.php';
}

require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();
$res = CRM_Core_Resources::singleton();

// Get CiviCRM's short date format
$dateFormat = getShortDateFormat();

// Load Google ReCAPTCHA library
$recaptchaBase = dirname(__FILE__).'/../packages/ReCaptcha';
require_once $recaptchaBase . '/ReCaptcha.php';
require_once $recaptchaBase . '/RequestMethod.php';
require_once $recaptchaBase . '/RequestParameters.php';
require_once $recaptchaBase . '/Response.php';
require_once $recaptchaBase . '/RequestMethod/Post.php';
require_once $recaptchaBase . '/RequestMethod/Socket.php';
require_once $recaptchaBase . '/RequestMethod/SocketPost.php';

// Instantiate a Smarty template
$template = CRM_Core_Smarty::singleton();

// Get this extension's base URL. Used for loading resources in template.
$template->assign('extBaseUrl', $res->getUrl('au.org.prr.rswidcard'));

// Start a session and save url arguments in the session
session_start();
$_SESSION['cid'] = $cid = CRM_Utils_Request::retrieve('c', 'Positive');
$_SESSION['key'] = $key = CRM_Utils_Request::retrieve('k', 'String');
$recaptchaResponse = CRM_Utils_Request::retrieve('g-recaptcha-response', 'String');

$orgName = getOrganisationName();

$pageTitle = "$orgName &ndash; Member data";
$mainHeading = $orgName;
// PRRPS-specific: use "Railway worker data" in title
if (str_contains($orgName, "Pichi Richi Railway Preservation Society")) {
  $pageTitle = "Pichi Richi Railway Preservation Society &ndash; Railway worker data";
}

// Controller for the reCAPTCHA form
// Ensure that reCAPTCHA public and private keys are set in CiviCRM settings
if (empty($config->recaptchaPublicKey) || empty($config->recaptchaPrivateKey)) {
  $template->assign('errMsg', "ReCAPTCHA is not configured.");
}
// If there is a reCAPTCHA response, verify it
elseif (isset($recaptchaResponse)) {
  $recaptcha = new \ReCaptcha\ReCaptcha($config->recaptchaPrivateKey);
  $resp = $recaptcha->verify($recaptchaResponse, $_SERVER['REMOTE_ADDR']);
  
  if ($resp->isSuccess()) {
    // Successful reCAPTCHA response
    // This section is the main code that queries for and then outputs (via a template) the records and data to be shown.
    $mainHeading = "Member data";
    $contact = getContactData($cid);
    $keyHash = $contact['rsw_id_card.rswid_card_hash'] ?? NULL;
    $fullName = $contact['display_name'] ?? NULL;

    if (validateKey($key, $keyHash) === true) {
      // Get contact's full name and assign to a template variable
      $template->assign('fullName', $fullName);

      // Get membership data and assign to template
      $template->assign('membership', getMembershipData($cid));
      
      // Specific to PRRPS
      // Get approvals, rail safety health assessments, training and assessments, and external qualifications records and assign to template.
      if (str_contains($orgName, "Pichi Richi Railway Preservation Society")) {
        $template->assign('approvals', getApprovalsData($cid));
        $template->assign('health', getRSHealthData($cid));
        $template->assign('trgassessments', getTrainingAssessData($cid));
        $template->assign('extQuals', getExternalQualData($cid));
        // Specific heading for PRRPS
        $mainHeading = "Railway worker data";
      }

      // Specific to Phoenix Aero Club
      // Get licence and medical data and assign to template.
      if (str_contains($orgName, "Phoenix Aero")) {
        $template->assign('licenceAndMedical', getLicenceMedicalData($cid));
      }
    }
    else {
      $template->assign('errMsg', "The QR code was read incorrectly, the identity card has been de-activated, or no record was found for the worker.");
    }
  }
  else {
    $template->assign('errMsg', "Invalid reCAPTCHA response.");
  }
}
else {
  // Display reCAPTCHA challenge form
  $template->assign('isCaptcha', TRUE);
  $template->assign('recaptchaPublicKey', $config->recaptchaPublicKey);
}

// Output the template
$extTplPath = dirname(__FILE__)."/../templates/";
$template->assign('extTplPath', $extTplPath);
$template->assign('dateFormat', $dateFormat);
$template->display($extTplPath. "/extern/rswdata.tpl");

// Finished.

// Get CiviCRM's short date format from settings using APIv4
function getShortDateFormat() {
  try {
    $setting = \Civi\Api4\Setting::get(FALSE)
    ->addSelect('dateformatshortdate')
    ->execute()
    ->first();

    return $setting['value'];
  }
  catch (\API_Exception $e) {
    CRM_Core_Error::fatal(ts('Could not retrieve CiviCRM short date format setting.'));
  }
}

function getOrganisationName() {
  try {
    $domain = \Civi\Api4\Domain::get(FALSE)
      ->addSelect('name')
      ->execute()
      ->first();
    
    return $domain["name"];
  }
  catch (\API_Exception $e) {
    return NULL;
  }
}

// Verifies the supplied key (from QR code) against the hash (previously retrieved from CiviCRM custom field), using PHP's password_verify() function
function validateKey(string $key, string $hash) { 
  if (strlen($hash) > 20 && password_verify($key, $hash)) {
    return TRUE;
  }
  return FALSE;
}

// Get basic data for the contact: display name and ID card hash
function getContactData(int $cid) {
  try {
    return \Civi\Api4\Contact::get(FALSE)
      ->addSelect('display_name', 'rsw_id_card.rswid_card_hash')
      ->addWhere('id', '=', $cid)
      ->execute()
      ->first();
  }
  catch (\API_Exception $e) {
    return NULL;
  }
}

// Get current PRRPS approval records for a contact using APIv4
function getApprovalsData(int $cid) {
  try {
    return \Civi\Api4\CustomValue::get('PRRPS_Approvals', FALSE)
      ->addSelect('Approval:label', 'Date', 'Expiry_date', 'Other_detail')
      ->addWhere('entity_id', '=', $cid)
      ->addWhere('Is_latest_record', '=', TRUE)
      ->addWhere('Approval_withdrawn', '=', FALSE)
      ->addClause('OR', ['Expiry_date', 'IS NULL'], ['Expiry_date', '>=', 'now'])
      ->addOrderBy('Approval:label', 'ASC')
      ->addOrderBy('Date', 'DESC')
      ->execute();
  }
  catch (\API_Exception $e) {
    return NULL;
  }
}

// Get current PRRPS training and assessments records for a contact using APIv4
function getTrainingAssessData(int $cid) {
  try {
    return \Civi\Api4\CustomValue::get('PRRPS_Assessments_Training', FALSE)
      ->addSelect('Assessment_or_training_name:label', 'Record_type:label', 'Date', 'Expiry_date', 'Other_detail')
      ->addWhere('entity_id', '=', $cid)
      ->addWhere('Is_latest_record', '=', TRUE)
      ->addWhere('Assessment_result', 'NOT IN', [2, 5]) // 2 == 'Not yet competent', 5 == 'Insufficient evidence of competence'. (I don't know what is the difference but anyway we check for both.)
      ->addClause('OR', ['Expiry_date', 'IS NULL'], ['Expiry_date', '>=', 'now'])
      ->addOrderBy('Assessment_or_training_name:label', 'ASC')
      ->addOrderBy('Date', 'DESC')
      ->execute();
  }
  catch (\API_Exception $e) {
    return NULL;
  }
}

// Get current external qualifications and training records for a contact using APIv4
function getExternalQualData(int $cid) {
  try {
    return \Civi\Api4\CustomValue::get('External_Training_Qualifications', FALSE)
      ->addSelect('Name_of_training_qualification:label', 'Date', 'Expiry_date', 'Other_detail')
      ->addWhere('entity_id', '=', $cid)
      ->addWhere('Is_latest_record', '=', TRUE)
      ->addClause('OR', ['Expiry_date', 'IS NULL'], ['Expiry_date', '>=', 'now'])
      ->addOrderBy('Name_of_training_qualification:label', 'ASC')
      ->addOrderBy('Date', 'DESC')
      ->execute();
  }
  catch (\API_Exception $e) {
    return NULL;
  }
}

// Get the current rail safety health assessment record for a contact using APIv4
function getRSHealthData(int $cid) {
  try {
    return \Civi\Api4\CustomValue::get('Rail_Safety_Health', FALSE)
      ->addSelect('Category:label', 'Health_assessment_result:label', 'Date', 'Expiry_date', 'Conditions:label', 'Other_detail')
      ->addWhere('entity_id', '=', $cid)
      ->addWhere('Is_latest_record', '=', TRUE)
      ->execute()
      ->first();
  }
  catch (\API_Exception $e) {
    return NULL;
  }
}

// Get the current membership data for a contact using APIv4 (if more than one membership, the one with the latest end date)
function getMembershipData(int $cid) {
  try {
    return \Civi\Api4\Membership::get(FALSE)
      ->addSelect('membership_type_id:label', 'join_date', 'start_date', 'end_date', 'status_id:label')
      ->addWhere('contact_id', '=', 2)
      ->addWhere('status_id.is_current_member', '=', TRUE)
      ->addOrderBy('end_date', 'DESC')
      ->execute()
      ->first();
  }
  catch (\API_Exception $e) {
    return NULL;
  }
}

// Gets custom field data from the single-record Licence and Medical Certification
// custom field group and makes it available to the template. Specific to Phoenix Aero Club.
function getLicenceMedicalData(int $cid) {
  try {
    return \Civi\Api4\Contact::get(FALSE)
      ->addSelect('Licence_and_Medical_Certificate.Aviation_Reference_Number',
        'Licence_and_Medical_Certificate.Licence_Type',
        'Licence_and_Medical_Certificate.Ratings',
        'Licence_and_Medical_Certificate.Aeroplane_Flight_Review_Expires',
        'Licence_and_Medical_Certificate.Medical_Class',
        'Licence_and_Medical_Certificate.Medical_Expiry_Date',
        'Licence_and_Medical_Certificate.Last_Flight')
      ->addWhere('id', '=', $cid)
      ->execute()
      ->first();
  }
  catch (\API_Exception $e) {
    return NULL;
  }
}

?>