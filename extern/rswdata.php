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
    catch (\API_Exception $e) {
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
  // Query to get the current PRRPS approvals
  $query = "  
    SELECT ov1.label AS app_approval_name, cf.prrapprov_date AS app_date, cf.prrapprov_date_expiry AS app_exp_date, cf.prrapprov_other_detail AS app_other_detail  
    FROM civicrm_value_prrps_approvals cf
    INNER JOIN civicrm_option_value ov1
    ON cf.prrapprov_category = ov1.value
    AND ov1.option_group_id = 98
    WHERE cf.prrapprov_is_latest_record = 1
    AND cf.prrapprov_is_withdrawn = 0
    AND (cf.prrapprov_date_expiry IS NULL OR cf.prrapprov_date_expiry = '0000-00-00 00:00:00' OR cf.prrapprov_date_expiry > now())
    AND cf.entity_id = " . $cid . "
    ORDER BY app_date DESC;
  ";
  $dao = CRM_Core_DAO::executeQuery($query);
  $result = $dao->fetchAll();

  if (is_array($result)) {
    $template->assign('approvals', $result);
  } else {
    $template->assign('approvals', array('is_error' => 1));
  }  
}

function getTrainingAssessData($cid, &$template) {
  // Query to get the current PRRPS training and assessments
  $query = "  
    SELECT ov1.label AS ass_trg_assess_name, ov2.label AS ass_record_type, cf.prrasstrg_date AS ass_date, 
      cf.prrasstrg_date_expiry AS ass_exp_date, cf.prrasstrg_other_detail AS ass_other_detail  
    FROM civicrm_value_prrps_assessments_training AS cf
    INNER JOIN civicrm_option_value AS ov1
    ON cf.prrasstrg_category = ov1.value
    AND ov1.option_group_id = 100
    INNER JOIN civicrm_option_value ov2
    ON cf.prrasstrg_record_type = ov2.value
    AND ov2.option_group_id = 99
    INNER JOIN civicrm_option_value ov3
    ON cf.prrasstrg_assessment_result = ov3.value
    AND ov3.option_group_id = 101
    WHERE cf.prrasstrg_is_latest_record = 1
    AND (cf.prrasstrg_date_expiry IS NULL OR cf.prrasstrg_date_expiry = '0000-00-00 00:00:00' OR cf.prrasstrg_date_expiry > now())
    AND cf.prrasstrg_assessment_result <> '2' -- 2 = Not yet competent
    AND cf.entity_id = " . $cid . "
    ORDER BY ass_date DESC;
  ";
  $dao = CRM_Core_DAO::executeQuery($query);
  $result = $dao->fetchAll();

  if (is_array($result)) {
    $template->assign('trgassessments', $result);
  } else {
    $template->assign('trgassessments', array('is_error' => 1));
  }  
}

function getExternalQualData($cid, &$template) {
  // Query to get the current external qualifications and training data
  $query = "  
    SELECT ov1.label AS extqu_qualtrg_name, cf.extqualtrg_date AS extqu_date, cf.extqualtrg_date_expiry AS extqu_exp_date, cf.extqualtrg_other_detail AS extqu_other_detail  
    FROM civicrm_value_external_training_quals cf
    INNER JOIN civicrm_option_value ov1
    ON cf.extqualtrg_category = ov1.value
    AND ov1.option_group_id = 131
    WHERE cf.extqualtrg_is_latest_record = 1
    AND (cf.extqualtrg_date_expiry IS NULL OR cf.extqualtrg_date_expiry = '0000-00-00 00:00:00' OR cf.extqualtrg_date_expiry > now())
    AND cf.entity_id = " . $cid . "
    ORDER BY extqu_date DESC;
  ";
  $dao = CRM_Core_DAO::executeQuery($query);
  $result = $dao->fetchAll();

  if (is_array($result)) {
    $template->assign('extQuals', $result);
  } else {
    $template->assign('extQuals', array('is_error' => 1));
  }  
}
  
function getRSHealthData($cid, &$template) {
  // Query to get the most recent health assessment 
  $query = "
    SELECT ov1.label AS ha_category, ov2.label AS ha_result, cf.rshealth_date AS ha_date, cf.rshealth_date_expiry AS ha_exp_date, 
      cf.rshealth_conditions AS ha_conditions, cf.rshealth_other_detail as ha_other_detail 
    FROM civicrm_value_rail_safety_health cf
    INNER JOIN civicrm_option_value ov1
    ON cf.rshealth_category = ov1.value
    AND ov1.option_group_id = 92
    INNER JOIN civicrm_option_value ov2
    ON cf.rshealth_fitness = ov2.value
    AND ov2.option_group_id = 93
    WHERE cf.rshealth_is_latest_record = 1
    AND cf.entity_id = " . $cid . "
    ORDER BY ha_date DESC
    LIMIT 1
  ";
  $dao = CRM_Core_DAO::executeQuery($query);
  $result = $dao->fetchAll();


  // Assign the most recent health assessment details to template variables
  if (is_array($result) && array_key_exists(0, $result)) {
    $template->assign('health', $result[0]);
    
    // "Decode" the Conditions field checkbox values
    if ($result[0]['ha_conditions']) {
      $conditionsFieldId = CRM_Core_BAO_CustomField::getCustomFieldID('Conditions', 'Rail_Safety_Health');
      $template->assign('ha_conditions', getCustomOptionLabels($result[0]['ha_conditions'], $conditionsFieldId));
    }
  } else {
    $template->assign('health', array('is_error' => 1));
  }
}

function getFullName(int $cid) {
  if ($cid > 0) {
    // Get the contact's name using api4
    try {
      $contact = \Civi\Api4\Contact::get(FALSE)
        ->addSelect('display_name')
        ->addWhere('id', '=', $cid)
        ->setLimit(1)
        ->execute()
        ->first();
      
      if (!$contact) {
        echo ts("Contact does not exist.");
        exit;
      }
      else {
        return $contact['display_name'];
      }
    
    }
    catch (\API_Exception $e) {
      echo ts("Error retrieving contact's details.");
      exit;
    }
  }
}

function getCustomOptionLabels($value, $customFieldId) {
  $customOptions = CRM_Core_BAO_CustomOption::getCustomOption($customFieldId);
  $returnArray = array();
  
  if ($value) {
    $checkedData = explode(CRM_Core_DAO::VALUE_SEPARATOR, substr($value, 1, -1));
    foreach ($customOptions as $option) {
      if (in_array($option['value'], $checkedData)) {
        $returnArray[] = $option['label'] . "\n";
      }
    }
  }
  return $returnArray;
}

function getShortDateFormat() {
  try {
    $setting = \Civi\Api4\Setting::get(FALSE)
    ->addSelect('dateformatshortdate')
    ->execute()
    ->first();
  }
  catch (\API_Exception $e) {
    CRM_Core_Error::fatal(ts('Could not retrieve CiviCRM short date format setting.'));
  }

  if (is_array($setting) && array_key_exists('value', $setting)) {
    return $setting['value'];
  }
  else {
    CRM_Core_Error::fatal(ts('Could not retrieve CiviCRM short date format setting.'));
  }
}

?>
