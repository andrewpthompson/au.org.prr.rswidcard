<?php

/*
 * Settings metadata file
 * 
 * @see https://docs.civicrm.org/dev/en/latest/framework/setting/#settings-definition
 * But it was found to be necessary to also include addional metadata, otherwise
 * the settings form class, which was taken from nz.co.fuzion.civixero, would not
 * work.
 *  - html_type
 *  - quick_form_type
 *  - html_attributes['size']
 */
return array(
  'rswidcard_logo_url' => array(
    'group_name' => 'ID Cards',
    'group' => 'rswidcard',
    'name' => 'rswidcard_logo_url',
    'type' => 'String',
    'default' => 'https://mywebsite.org/images/logo.png',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => 'ID card logo PNG image URL',
    'description' => 'URL of the logo image to be used on the cards (PNG file only). Leave blank if not required.',
    'help_text' => NULL,
    'html_type' => 'Text',
    'quick_form_type' => 'Element',
    'html_attributes' => array(
      'size' => 75,
    ),
  ),
  'rswidcard_bg_image_url' => array(
    'group_name' => 'ID Cards',
    'group' => 'rswidcard',
    'name' => 'rswidcard_bg_image_url',
    'type' => 'String',
    'default' => 'https://mywebsite.org/images/background.jpg',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => 'Card background JPEG image URL',
    'description' => 'URL of the background image to be used on the cards (JPEG file only). Leave blank if not required.',
    'help_text' => NULL,
    'html_type' => 'Text',
    'quick_form_type' => 'Element',
    'html_attributes' => array(
      'size' => 75,
    ),
  ),
  'rswidcard_card_text' => array(
    'group_name' => 'ID Cards',
    'group' => 'rswidcard',
    'name' => 'rswidcard_card_text',
    'type' => 'String',
    'default' => 'RAIL SAFETY WORKER',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => 'Text to be printed on card',
    'description' => 'Text that will be printed below the logo on the cards. Leave blank if not required.',
    'help_text' => NULL,
    'html_type' => 'Text',
    'quick_form_type' => 'Element',
    'html_attributes' => array(
      'size' => 40,
    ),
  ),
  'rswidcard_barcode_url' => array(
    'group_name' => 'ID Cards',
    'group' => 'rswidcard',
    'name' => 'rswidcard_barcode_url',
    'type' => 'String',
    'default' => 'https://mywebsite.org/idcard/',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => 'URL to be encoded in barcode',
    'description' => 'Base URL that will be encoded in the barcodes. (Unique parameters will be appended to this when cards are created.) Keep as short as possible.',
    'help_text' => NULL,
    'html_type' => 'Text',
    'quick_form_type' => 'Element',
    'html_attributes' => array(
      'size' => 40,
    ),
  ),
  'rswidcard_form_title' => array(
    'group_name' => 'ID Cards',
    'group' => 'rswidcard',
    'name' => 'rswidcard_form_title',
    'type' => 'String',
    'default' => 'ID Cards',
    'add' => '5.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => 'Form and menu title',
    'description' => 'What do you call the cards? e.g. "ID Cards" or "Membership Cards". This will be displayed as the form title and in the search task menu.',
    'help_text' => NULL,
    'html_type' => 'Text',
    'quick_form_type' => 'Element',
    'html_attributes' => array(
      'size' => 40,
    ),
  ),
);