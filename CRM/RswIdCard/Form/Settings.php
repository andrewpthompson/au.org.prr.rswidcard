<?php
class CRM_RswIdCard_Form_Settings extends CRM_Admin_Form_Setting {
  const DEFAULT_MENU_FORM_TITLE = 'ID Cards';
  
  protected $_settings = array(
//   'max_attachments' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,  
//   'contact_undelete' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,  
//   'versionAlert' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,   
//   'versionCheck' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,  
//   'empoweredBy' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 
//   'maxFileSize' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
//   'doNotAttachPDFReceipt' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
//   'secondDegRelPermissions' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
//   'checksumTimeout' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'rswidcard_logo_url' => 'ID Card Settings',
    'rswidcard_bg_image_url' => 'ID Card Settings',
    'rswidcard_card_text' => 'ID Card Settings',
    'rswidcard_barcode_url' =>  'ID Card Settings',
    'rswidcard_form_title' => 'ID Card Settings',
  );
}

//
///**
// * Form controller class
// *
// * @see https://github.com/eileenmcnaughton/nz.co.fuzion.civixero/blob/master/CRM/Civixero/Form/XeroSettings.php
// * This class was copied almost verbatim from CiviXero. Modified getFormSettings()
// * to remove $extraSettings, which was specific tp CiviXero.
// */
//class CRM_RswIdCard_Form_Settings extends CRM_Core_Form {
//  private $_settingFilter = array('group' => 'rswidcard');
//  //everything from this line down is generic & can be re-used for a setting form in another extension
//  private $_submittedValues = array();
//  private $_settings = array();
//
//  function buildQuickForm() {
//    $settings = $this->getFormSettings();
//    $elementDescriptions = array();
//    foreach ($settings as $name => $setting) {
//      if (isset($setting['quick_form_type'])) {
//        $add = 'add' . $setting['quick_form_type'];
//        if ($add == 'addElement') {
//          $this->$add($setting['html_type'], $name, ts($setting['title']), CRM_Utils_Array::value('html_attributes', $setting, array ()));
//        }
//        elseif ($setting['html_type'] == 'Select') {
//          $optionValues = array();
//          if (!empty($setting['pseudoconstant'])) {
//            if(!empty($setting['pseudoconstant']['optionGroupName'])) {
//              $optionValues = CRM_Core_OptionGroup::values($setting['pseudoconstant']['optionGroupName'], FALSE, FALSE, FALSE, NULL, 'name');
//            }
//            elseif (!empty($setting['pseudoconstant']['callback'])) {
//              $cb = Civi\Core\Resolver::singleton()->get($setting['pseudoconstant']['callback']);
//              $optionValues = call_user_func_array($cb, $optionValues);
//            }
//          }
//          $this->add('select', $setting['name'], $setting['title'], $optionValues, FALSE, $setting['html_attributes']);
//        }
//        else {
//          $this->$add($name, ts($setting['title']));
//        }
//        //$this->assign("{$setting['name']}_description", ts($setting['description'])); // PROBLEM
//        $elementDescriptions[$setting['name']] = ts($setting['description']);
//      }
//    }
//    $this->addButtons(array(
//      array (
//        'type' => 'submit',
//        'name' => ts('Submit'),
//        'isDefault' => TRUE,
//      )
//    ));
//    // Export element descriptions array
//    $this->assign('elementDescriptions', $elementDescriptions);
//    // export form elements
//    $this->assign('elementNames', $this->getRenderableElementNames());
//    parent::buildQuickForm();
//  }
//
//  function postProcess() {
//    $this->_submittedValues = $this->exportValues();
//    $this->saveSettings();
//    parent::postProcess();
//  }
//
//  /**
//   * Get the fields/elements defined in this form.
//   *
//   * @return array (string)
//   */
//  function getRenderableElementNames() {
//    // The _elements list includes some items which should not be
//    // auto-rendered in the loop -- such as "qfKey" and "buttons". These
//    // items don't have labels. We'll identify renderable by filtering on
//    // the 'label'.
//    $elementNames = array();
//    foreach ($this->_elements as $element) {
//      $label = $element->getLabel();
//      if (!empty($label)) {
//        $elementNames[] = $element->getName();
//      }
//    }
//    return $elementNames;
//  }
//
//  /**
//   * Get the settings we are going to allow to be set on this form.
//   *
//   * @return array
//   */
//  function getFormSettings() {
//    if (empty($this->_settings)) {
//      $settings = civicrm_api3('setting', 'getfields', array('filters' => $this->_settingFilter));
//    }
//    $settings = $settings['values'];
//    return $settings;
//  }
//
//  /**
//   * Get the settings we are going to allow to be set on this form.
//   *
//   * @return array
//   */
//  function saveSettings() {
//    $settings = $this->getFormSettings();
//    $values = array_intersect_key($this->_submittedValues, $settings);
//    civicrm_api3('setting', 'create', $values);
//  }
//
//  /**
//   * Set defaults for form.
//   *
//   * @see CRM_Core_Form::setDefaultValues()
//   */
//  function setDefaultValues() {
//    $existing = civicrm_api3('setting', 'get', array('return' => array_keys($this->getFormSettings())));
//    $defaults = array();
//    $domainID = CRM_Core_Config::domainID();
//    foreach ($existing['values'][$domainID] as $name => $value) {
//      $defaults[$name] = $value;
//    }
//    return $defaults;
//  }
//}