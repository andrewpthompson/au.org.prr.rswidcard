<?php
// Perform bootstrap of CiviCRM
// Edit the below line with the correct path to CiviCRM
require_once '../membership/administrator/components/com_civicrm/civicrm/civicrm.config.php';

// The following 3 require_once lines are a temporary workaround. While the Joomla 5 version of CiviCRM does not include
// PSR Log (due to it conflicting with Joomla's), manually load the PSR Log files from Joomla's libraries/vendor directory
require_once '../membership/libraries/vendor/psr/log/src/LoggerTrait.php';
require_once '../membership/libraries/vendor/psr/log/src/LoggerInterface.php';
require_once '../membership/libraries/vendor/psr/log/src/AbstractLogger.php';

require_once 'CRM/Core/Config.php';
$config = CRM_Core_Config::singleton();
$res = CRM_Core_Resources::singleton();

if (defined('PANTHEON_ENVIRONMENT')) {
  ini_set('session.save_handler', 'files');
}

// Get CiviCRM's short date format
$dateFormat = getShortDateFormat();

// Load Google ReCAPTCHA library
$recaptchaBase = dirname(__FILE__).'/../packages/recaptcha';
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
    if (validateKey($cid, $key) === true) {
      getRSWData($cid, $template);
    }
    else {
      $template->assign('errMsg', "The QR code was read incorrectly, the identity card has been de-activated, or no record was found for the worker. Try re-scanning the QR code.");
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

function validateKey($cid, $key) {
  // Name of card hash custom field
  $cfCardHash = 'rsw_id_card.rswid_card_hash';
    
  if ($cid && $key) {
    // Get contact's key hash from database and validate it
    try {
      // API 4 query to retrieve the contact's card hash
      $contact = \Civi\Api4\Contact::get(FALSE)
        ->addSelect($cfCardHash)
        ->addWhere('id', '=', $cid)
        ->setLimit(1)
        ->execute()
        ->first();

      $keyHash = $contact[$cfCardHash];
      
      // Verify the supplied key (from QR code) against the hash using PHP's password_verify() function
      if (strlen($keyHash) > 20 && password_verify($key, $keyHash)) {
        return true;
      }
    }
    catch (\CRM_Core_Exception $e) {
      // do nothing
    }
  }
  return false;
}

function getRSWData($cid, &$template) {
  // Get contact's full name and assign to a template variable
  $template->assign('fullName', getFullName($cid));

  getRSHealthData($cid, $template);
  getApprovalsData($cid, $template);
  getTrainingAssessData($cid, $template);
  getExternalQualData($cid, $template);
}

function getApprovalsData($cid, &$template) {
  // APIv4 query to get the current PRRPS approvals
  try {
    $approvals = \Civi\Api4\CustomValue::get('PRRPS_Approvals', FALSE)
      ->addSelect('Approval:label', 'Date', 'Expiry_date', 'Other_detail')
      ->addWhere('entity_id', '=', $cid)
      ->addWhere('Is_latest_record', '=', TRUE)
      ->addWhere('Approval_withdrawn', '=', FALSE)
      ->addClause('OR', ['Expiry_date', 'IS NULL'], ['Expiry_date', '>=', 'now'])
      ->addOrderBy('Approval:label', 'ASC')
      ->addOrderBy('Date', 'DESC')
      ->execute();
    
    $template->assign('approvals', $approvals);
  }
  catch (\CRM_Core_Exception $e) {
    $template->assign('approvals', array('is_error' => 1));
  }
}

function getTrainingAssessData($cid, &$template) {
  // APIv4 query to get the current PRRPS training and assessments
  try {
    $trgassessments = \Civi\Api4\CustomValue::get('PRRPS_Assessments_Training', FALSE)
      ->addSelect('Assessment_or_training_name:label', 'Record_type:label', 'Date', 'Expiry_date', 'Other_detail')
      ->addWhere('entity_id', '=', $cid)
      ->addWhere('Is_latest_record', '=', TRUE)
      ->addWhere('Assessment_result', 'NOT IN', [2, 5]) // 2 == 'Not yet competent', 5 == 'Insufficient evidence of competence'. (WTF is the difference?)
      ->addClause('OR', ['Expiry_date', 'IS NULL'], ['Expiry_date', '>=', 'now'])
      ->addOrderBy('Assessment_or_training_name:label', 'ASC')
      ->addOrderBy('Date', 'DESC')
      ->execute();
    
    $template->assign('trgassessments', $trgassessments);
  }
  catch (\CRM_Core_Exception $e) {
    $template->assign('trgassessments', array('is_error' => 1));
  }
}

function getExternalQualData($cid, &$template) {
  // APIv4 query to get the current external qualifications and training data
  try {
    $extQuals = \Civi\Api4\CustomValue::get('External_Training_Qualifications', FALSE)
      ->addSelect('Name_of_training_qualification:label', 'Date', 'Expiry_date', 'Other_detail')
      ->addWhere('entity_id', '=', $cid)
      ->addWhere('Is_latest_record', '=', TRUE)
      ->addClause('OR', ['Expiry_date', 'IS NULL'], ['Expiry_date', '>=', 'now'])
      ->addOrderBy('Name_of_training_qualification:label', 'ASC')
      ->addOrderBy('Date', 'DESC')
      ->execute();
    
    $template->assign('extQuals', $extQuals);
  }
  catch (\CRM_Core_Exception $e) {
    $template->assign('extQuals', array('is_error' => 1));
  }
}
  
function getRSHealthData($cid, &$template) {
  // APIv4 query to get the most recent health assessment
  try {
    $health = \Civi\Api4\CustomValue::get('Rail_Safety_Health', FALSE)
      ->addSelect('Category:label', 'Health_assessment_result:label', 'Date', 'Expiry_date', 'Conditions:label', 'Other_detail')
      ->addWhere('entity_id', '=', $cid)
      ->addWhere('Is_latest_record', '=', TRUE)
      ->execute()
      ->first();
    
    $template->assign('health', $health);
  }
  catch (\CRM_Core_Exception $e) {
    $template->assign('health', array('is_error' => 1));
  }
}

function getFullName(int $cid) {
  if ($cid > 0) {
    // Get the contact's name using APIv4
    try {
      $contact = \Civi\Api4\Contact::get(FALSE)
        ->addSelect('display_name')
        ->addWhere('id', '=', $cid)
        ->setLimit(1)
        ->execute()
        ->first();
      
      return $contact['display_name'];
    }
    catch (\CRM_Core_Exception $e) {
      echo ts("Error retrieving contact's details.");
      exit;
    }
  }
}

function getShortDateFormat() {
  try {
    $setting = \Civi\Api4\Setting::get(FALSE)
    ->addSelect('dateformatshortdate')
    ->execute()
    ->first();

    return $setting['value'];
  }
  catch (\CRM_Core_Exception $e) {
    CRM_Core_Error::fatal(ts('Could not retrieve CiviCRM short date format setting.'));
  }
}

?>
