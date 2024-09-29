<?php

require_once 'rswidcard.civix.php';
use CRM_RswIdCard_ExtensionUtil as E;

// This extension uses random_int() which is a PHP 7 function. Load a library that implements random_int for PHP 5.
require_once 'packages/random_compat/lib/random.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function rswidcard_civicrm_config(&$config) {
  _rswidcard_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function rswidcard_civicrm_install() {
  _rswidcard_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function rswidcard_civicrm_enable() {
  _rswidcard_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_navigationMenu().
 * 
 * Adds to the main navigation menu the following
 *  - top-level "PRRPS" if it doesn't exist already
 *  - child item 'Make blank temporary RSW ID cards'
 * 
 * The child link includes '/blank' in its URL. This is recognised by the form 
 * and causes it to work without a list of contact IDs as it normally would to 
 * enable blank cards to be produced.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function rswidcard_civicrm_navigationMenu(&$menu) {
  // Insert a menu item into the Administer menu for this extension's settings 
  // form
  _rswidcard_civix_insert_navigation_menu($menu, 'Administer', array(
    'label' => E::ts('ID Card settings', array('domain' => 'au.org.prr.rswidcard')),
    'name' => 'rswidcard_settings',
    'url' => 'civicrm/rswidcard/settings',
    'permission' => 'administer CiviCRM',
    'operator' => NULL,
    'separator' => 2,
  ));

  // This menu item has been made specific to PRRPS and is not expected to be
  // required by other organisations. It is for printing a sheet of blank
  // cards to have details hand-written on for temporary use
  // Check if the domain's default email contains "@prr.org.au" and if so add
  // the menu item.
  $domain = civicrm_api3('Domain', 'get', array('sequential' => 1));
  if (strpos($domain['values'][0]['domain_email'], '@prr.org.au') !== FALSE) {
    // Get the "PRRPS" top-level menu item to check if it exists
    $parentNavId = CRM_Core_DAO::getFieldValue('CRM_Core_BAO_Navigation', 'PRRPS', 'id', 'name');
    // If it was not found then create it
    if (!$parentNavId) {
      _rswidcard_civix_insert_navigation_menu($menu, NULL, array(
        'label' => ts('PRRPS', array('domain' => 'au.org.prr.rswidcard')),
        'name' => 'PRRPS',
        'permission' => 'view all contacts',
        'operator' => NULL,
        'separator' => 0,
      ));
    }

    // Insert a child menu item for making blank temporary ID cards
    _rswidcard_civix_insert_navigation_menu($menu, 'PRRPS', array(
      'label' => E::ts('Make blank temporary RSW ID cards', array('domain' => 'au.org.prr.rswidcard')),
      'name' => 'rswidcardblank',
      'url' => 'civicrm/rswidcard/blank',
      'permission' => 'view all contacts',
      'operator' => NULL,
      'separator' => 0,
      'active' => 1,
    ));
  _rswidcard_civix_navigationMenu($menu);
  }
}

/**
 * Adds a Contact search task 'Rail safety worker ID cards - Print'
 */
function rswidcard_civicrm_searchTasks($objectType, &$tasks ) {
  $menuTitle = Civi::settings()->get('rswidcard_form_title');
  if (empty($menuTitle)) {
    $menuTitle = CRM_RswIdCard_Form_Settings::DEFAULT_MENU_FORM_TITLE;
  }
  $menuTitle .= ' - ' . ts('Print');
  if ( $objectType == 'contact' ) {
    $tasks[] = array(
      //'title' => ts('Rail safety worker ID cards - Print'),
      'title' => $menuTitle,
      'class' => 'CRM_RswIdCard_Form_Task_IDCard',
    );
  }
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * Load a JavaScript file to hide the Card Key Hash field on the Edit 
 * Contact form and the inline Edit form on the Contact Summary page.
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function rswidcard_civicrm_buildForm($formName, &$form) {
  // Load script that hides the Card Key Hash custom field using jQuery
  if ($formName == 'CRM_Contact_Form_Inline_CustomData' || $formName == 'CRM_Contact_Form_Contact') {
    CRM_Core_Resources::singleton()->addScriptFile('au.org.prr.rswidcard', 'js/hideKeyField.js');
  }
}

/**
 * Implements hook_civicrm_pageRun().
 * 
 * Hides Card Key Hash custom field on the Contact Summary View page (i.e. when viewing not editing).
 * 
 * @param CRM_Core_Page $page the page being rendered
 */
function rswidcard_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');

  // Load script that hides the Card Key Hash custom field using jQuery
  if ($pageName == 'CRM_Contact_Page_View_Summary') {
    CRM_Core_Resources::singleton()->addScriptFile('au.org.prr.rswidcard', 'js/hideKeyField.js');
  }
}
