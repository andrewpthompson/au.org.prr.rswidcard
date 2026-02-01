<?php

/* Configuration file for the external (to CiviCRM) portion of the RSW ID Card extension.
 * Copy config.dist.php to config.local.php and customise config.local.php
 */

return [
  // CMS. This will be one of: joomla | wordpress | drupal
  'cms' => '',

  // Path to the CiviCRM civicrm.config.php (Joomla/Wordpress) or civicrm.settings.php (Drupal) file
  // e.g. /var/www/joomla/administrator/components/com_civicrm/civicrm/civicrm.config.php
  // e.g. ../wp-content/plugins/civicrm/civicrm/civicrm.config.php
  'civicrm_config_file' => '',

  // CMS root (used for additional bootstrapping if required). No trailing slash.
  // e.g. /var/www/joomla
  'cms_root' => '',
];
