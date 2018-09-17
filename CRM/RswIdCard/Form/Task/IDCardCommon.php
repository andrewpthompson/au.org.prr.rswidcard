<?php

/**
 * This class provides the common functionality for ID cards
 */
class CRM_RswIdCard_Form_Task_IDCardCommon {

  /**
   * @param CRM_Core_Form $form
   * @param array $contactIds
   * @param string $subject
   * @param string $activityType
   *
   * @return array
   *   List of activity IDs.
   *   There may be 1 or more, depending on the use-case.
   *
   * @throws CRM_Core_Exception
   * 
   * Copied from CRM_Contact_Form_Task_PDFLetterCommon::CreateActivities() but simplified and added activityType parameter
   */
  public static function createActivities($form, $contactIds, $subject, $activityType) {
    $activityParams = array(
      'subject' => $subject,
      'source_contact_id' => CRM_Core_Session::singleton()->getLoggedInContactID(),
      'activity_type_id' => CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', $activityType),
      'activity_date_time' => date('YmdHis'),
    );
    if (!empty($form->_activityId)) {
      $activityParams += array('id' => $form->_activityId);
    }
    $activityIds = array();
    // Create one activity with all contacts (i.e. we're not using the setting for letters that selects one activity for each contact or one that covers all)

    $fullParams = array(
      'target_contact_id' => $contactIds,
    ) + $activityParams;
    if (!empty($form->_caseId)) {
      $fullParams['case_id'] = $form->_caseId;
    }
    elseif (!empty($form->_caseIds)) {
      $fullParams['case_id'] = $form->_caseIds;
    }
    $activity = civicrm_api3('Activity', 'create', $fullParams);
    $activityIds[] = $activity['id'];

    return $activityIds;
  }
  
  /**
   * This is a replacement for CRM_Core_BAO_LabelFormat::getList()
   * It returns only "label" formats with grouping containing the word "Card"
   * 
   * @return array
   */
  public static function getCardFormatList() {
    $result = civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'return' => array("name", "label"),
      'option_group_id' => "label_format",
      'is_active' => 1,
      'grouping' => array('LIKE' => "%Card%"),
    ));
    
    if (!empty($result) && array_key_exists('values', $result)) {
      $cards = array();
      foreach ($result['values'] as $card_format) {
        $cards[$card_format['name']] = $card_format['label'];
      }
      return $cards;
    }
  }
  
  /**
   * Returns the aspect ratio of an image file
   * 
   */
  public static function imageAspectRatio($img) { 
    if ($img) {
      $size = getimagesize($img);
      return $size[0] / $size[1];
    }
    else {
      return NULL;
    }
  }
  
  /**
   * Returns the absolute path to a contact photo from its URL.
   * Adapted from CRM_Utils_File::getImageURL()
   * The use case for this is that TCPDF doesn't seem to be robust with URLs
   * containing parameters
   *
   * @param string $imageURL
   *   Contact's image url
   *
   * @return string $url
   */
  public static function getImageURL($imageURL) {
    // retrieve image name from $imageURL
    $imageURL = CRM_Utils_String::unstupifyUrl($imageURL);
    parse_str(parse_url($imageURL, PHP_URL_QUERY), $query);
    $path = NULL;
    if (!empty($query['photo'])) {
      $path = CRM_Core_Config::singleton()->customFileUploadDir . $query['photo'];
    }
    return $path;
  }
}

?>
