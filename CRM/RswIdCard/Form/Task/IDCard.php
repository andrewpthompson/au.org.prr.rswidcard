<?php
/* This class was based on CiviCRM core's PDF mailing labels. Therefore 
 * some of the names in this class are "label" where it really refers to 
 * ID cards. */

use CRM_RswIdCard_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_RswIdCard_Form_Task_IDCard extends CRM_Contact_Form_Task {

  private $print_bg_image = true;
  private $print_border = false;
  private $round_corners = false;
  private $bg_image_bleed = false;
  
  // Names of the custom fields that are used for storing card details
  private $cfCardDate = null;
  private $cfCardHash = null;
  
  // Base URL of this extension
  private $baseUrl = null;
  
  // Page to be rotated for vertical cards
  private $rotate = false;
  
  /**
   * Build all the data structures needed to build the form.
   */
  public function preProcess() {
    // Only call parent::preProcess() if this form has been called the normal way via a search task.
    // If it has been called for the purpose of creating blank cards (and has no contacts) then skip it.
    $blank = in_array('blank', $this->urlPath); 
    if ($blank == false) {
      parent::preProcess();
    }
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    self::buildLabelForm($this);
  }

  /**
   * Common Function to build Mailing Label Form.
   *
   * @param CRM_Core_Form $form
   * 
   */
  public function buildLabelForm($form) {
    // Set the form's title (user configurable)
    $formTitle = Civi::settings()->get('rswidcard_form_title');
    if (empty($formTitle)) {
      $formTitle = CRM_RswIdCard_Form_Settings::DEFAULT_MENU_FORM_TITLE;
    }
    $formTitle = E::ts("Make", array('domain' => 'au.org.prr.rswidcard')) . " " . $formTitle;
    CRM_Utils_System::setTitle($formTitle);

    //add select for label
    $label = CRM_RswIdCard_Form_Task_IDCardCommon::getCardFormatList();

    $form->add('select', 'label_name', E::ts("Select card stock", array('domain' => 'au.org.prr.rswidcard')), array('' => ts("- select card stock -")) + $label, TRUE);
    $form->add('advcheckbox', 'print_bg_image', E::ts("Print background image", array('domain' => 'au.org.prr.rswidcard')));
    $form->add('advcheckbox', 'print_border', E::ts("Print border", array('domain' => 'au.org.prr.rswidcard')));
    $form->add('advcheckbox', 'round_corners', E::ts("Round corners", array('domain' => 'au.org.prr.rswidcard')));
    $form->add('text', 'bg_image_bleed', E::ts("Background image bleed (mm)", array('domain' => 'au.org.prr.rswidcard')));
    $form->add('advcheckbox', 'excl_existing', E::ts("Exclude contacts who have already been issued a card", array('domain' => 'au.org.prr.rswidcard')));
    $form->add('advcheckbox', 'is_test', E::ts("Test mode", array('domain' => 'au.org.prr.rswidcard')));

    $form->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Make Cards', array('domain' => 'au.org.prr.rswidcard')),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => E::ts('Done'),
      ),
    ));

    // export form elements
    $form->assign('elementNames', $form->getRenderableElementNames());
    
    // If this form has been called for the purpose of creating blank temporary ID cards then display a message
    $blank = in_array('blank', $this->urlPath); 
    if ($blank == true) {
      $message = "One page of blank identity cards will be created. Issuing a temporary identity card in no way relieves the organisation of its obligation to meet the legislated requirements for rail safety worker competence and health assessment.";
      CRM_Core_Session::setStatus($message, '', 'no-popup');
    }
    
    parent::buildQuickForm();
  }

  /**
   * Set default values for the form.
   *
   * @return array
   *   array of default values
   */
  public function setDefaultValues() {
    $defaults = array();
    $defaults['label_name'] = 'CR80_PVC_card';
    $defaults['print_bg_image'] = true;
    $defaults['print_border'] = false;
    $defaults['round_corners'] = false;
    $defaults['bg_image_bleed'] = 0.0;
    $defaults['excl_existing'] = true;
    $defaults['is_test'] = false;
    
    return $defaults;
  }
  
  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() { 
    $fv = $this->controller->exportValues($this->_name);
    
    // Get form options
    $this->print_bg_image = $fv['print_bg_image'];
    $this->print_border = $fv['print_border'];
    $this->round_corners = $fv['round_corners'];
    $this->bg_image_bleed = $fv['bg_image_bleed'];
    
    // Get the custom field IDs 
    $this->cfCardDate = "custom_" . CRM_Core_BAO_CustomField::getCustomFieldID('rswid_card_date', 'rsw_id_card');
    $this->cfCardHash = "custom_" . CRM_Core_BAO_CustomField::getCustomFieldID('rswid_card_hash', 'rsw_id_card');
    
    // Get this extension's URL
    $res = CRM_Core_Resources::singleton();
    $this->baseUrl = $res->getUrl('au.org.prr.rswidcard');
    
    //build the returnProperties
    $returnProperties = array('display_name' => 1, 'contact_type' => 1, 'prefix_id' => 1, 'first_name' => 1, 'middle_name' => 1, 'last_name' => 1, 
      'image_URL' => 1, "$this->cfCardDate" => 1, "$this->cfCardHash" => 1,
    );

    $rows = array();
    $contactIds = array();
    
    if (!empty($this->_contactIds)) { // Don't fetch contacts if form called standalone for the purpose of creating blank ID cards
      foreach ($this->_contactIds as $key => $contactID) {
        $params[] = array(CRM_Core_Form::CB_PREFIX . $contactID, '=', 1, 0, 0);
      }
      $params[] = array('is_deceased', '=', 0, 0, 0);

      $custom = array();
      foreach ($returnProperties as $name => $dontCare) {
        $cfID = CRM_Core_BAO_CustomField::getKeyID($name);
        if ($cfID) {
          $custom[] = $cfID;
        }
      }

      // Get the total number of contacts to fetch from database.
      $numberofContacts = count($this->_contactIds);
      $query = new CRM_Contact_BAO_Query($params, $returnProperties);
      $details = $query->apiQuery($params, $returnProperties, NULL, NULL, 0, $numberofContacts);

      foreach ($this->_contactIds as $value) {
        foreach ($custom as $cfID) {
          if (isset($details[0][$value]["custom_{$cfID}"])) {
            $details[0][$value]["custom_{$cfID}"] = CRM_Core_BAO_CustomField::displayValue($details[0][$value]["custom_{$cfID}"], $cfID);
          }
        }
        $contact = CRM_Utils_Array::value($value, $details['0']);

        if (is_a($contact, 'CRM_Core_Error')) {
          return NULL;
        }
        
        // Exclude (don't add to array) contacts with cards already if excl_existing form option is selected
        if (!empty($contact) && (!$fv['excl_existing'] || empty($contact[$this->cfCardDate]))) {
          foreach ($contact as $field => $fieldValue) {
            $rows[$value][$field] = $fieldValue;
            // This array is used in creating an Activity after the cards have been created and excludes those contacts from it
            $contactIds[] = $value;
          }
        }
      }
    }
    else {
      // Set up dummy contacts for making blank cards (more than enough for one page)
      self::setUpBlankCards($rows, 20);
    }

    foreach ($rows as $id => $row) {
      $ts = time();
      $rows[$id]['card_issue_date'] = date("YmdHis", $ts);
      $rows[$id]['card_issue_date_formatted'] = date('d/m/Y', $ts);
      
      // Generate a random string to be stored in the database (as a contact custom field) and in the QR code 
      // on the card for access to a simple report (implemented outside of CiviCRM) that will display the contact's
      // approvals, health assessment status, training etc.
      $rows[$id]['QR_key'] = CRM_RswIdCard_Utils_Random::random_str(18);
    }

    // Call function to create labels
    self::createLabel($rows, $fv['label_name']);
    
    // Write new card details to database
    foreach ($rows as $id => $row) {
      if (!$fv['is_test']) {
        $params = array(
          'entity_id' => $id,
          "$this->cfCardDate" => $row['card_issue_date'],
          "$this->cfCardHash" => password_hash($row['QR_key'], PASSWORD_DEFAULT),
        );      
        $result = civicrm_api3('CustomValue', 'create', $params);
      }
    }
    
    // Create activity if this is not a test and at least one card was actually created
    if (!$fv['is_test'] && !empty($contactIds)) {
      $activityIds = CRM_RswIdCard_Form_Task_IDCardCommon::createActivities($this, $contactIds, 'Rail safety worker identity card created', 'rsw_id_card_created');
    }
    
    CRM_Utils_System::civiExit(1);
  }

  /**
   * Create labels (pdf).
   *
   * @param array $contactRows
   *   Associated array of contact data.
   * @param string $format
   *   Format in which labels needs to be printed.
   * @param string $fileName
   *   The name of the file to save the label in.
   */
  public function createLabel($contactRows, &$format, $fileName = 'ID_cards.pdf') {
    $this->pdf = new CRM_Utils_PDF_Label($format, 'mm');
    $this->pdf->Open();
    
    // Get the smallest of the x and y card spacing (for use in range-limiting the bleed value)
    $cardSpacing = 0;
    if ($this->pdf->xNumber > 1 && $this->pdf->yNumber > 1) {
      $cardSpacing = min($this->pdf->xSpace, $this->pdf->ySpace);
    }
    if ($this->pdf->xNumber == 1) {
      $cardSpacing = $this->pdf->ySpace;
    }
    if ($this->pdf->yNumber == 1) {
      $cardSpacing = $this->pdf->xSpace;
    }
    
    // Ensure bleed setting is appropriate
    if ($this->bg_image_bleed < 0 || $this->print_border) {
      $this->bg_image_bleed = 0;
    }
    elseif ($this->bg_image_bleed > 0.5*$cardSpacing) {
      $this->bg_image_bleed = 0.5*$cardSpacing;
    }

    // If the word 'vertical' appears in the card format label/name then rotate the page by 270 degrees
    // In future it would be good if the label setup form could be extended to include a 'rotate' setting
    if (stripos($this->pdf->format['name'], 'vertical') !== false || stripos($this->pdf->format['label'], 'vertical') !== false) {
      $this->rotate = true;
      $page_format = array($this->pdf->paper_dimensions[0], $this->pdf->paper_dimensions[1], 'Rotate' => -90);
      $orientation = ($this->pdf->paper_dimensions[0] > $this->pdf->paper_dimensions[1]) ? 'P' : 'L';
      $this->pdf->AddPage($orientation, $page_format, false, false);
    }
    else {
      // Otherwise call AddPage normally without rotation
      $this->pdf->AddPage();
    }

    $this->pdf->setPrintHeader(FALSE);
    $this->pdf->setPrintFooter(FALSE);
    
    $this->pdf->SetGenerator($this, "labelCreator");
    $this->pdf->SetDisplayMode(100);

    foreach ($contactRows as $row => $value) {
      // If making blank cards then exit after making one page
      if (array_key_exists('blank', $value) && $row >= $this->pdf->xNumber * $this->pdf->yNumber) {
        break;
      }
      
      // Construct the full name (without prefix)
      $name = '';
      if (!empty($value['first_name'])) {
        $name = $value['first_name'];
      }
      
      if (!empty($value['middle_name'])) {
        $name .= " " . $value['middle_name'];
      }
      
      if (!empty($value['last_name'])) {
        $name  .= " " . $value['last_name'];
      }
      $contactRows[$row]['name'] = strtoupper($name);
      
      $this->AddPdfLabel($contactRows[$row]);
    }
    $this->pdf->Output($fileName, 'D');
  }

  /**
   * Add a label (card) to PDF
   *
   * @param $contactCardData
   */
  public function AddPdfLabel($contactCardData) {
    if ($this->pdf->countX == $this->pdf->xNumber) {
      // Page full, we start a new one
      if ($this->rotate == true) {
        // Same as for the first page, rotate the page if vertical cards are required for the "label" format
        $page_format = array($this->pdf->paper_dimensions[0], $this->pdf->paper_dimensions[1], 'Rotate' => -90);
        $orientation = ($this->pdf->paper_dimensions[0] > $this->pdf->paper_dimensions[1]) ? 'P' : 'L';
        $this->pdf->AddPage($orientation, $page_format, false, false);
      }
      else {
        // Otherwise call AddPage normally
        $this->pdf->AddPage();
      }
      
      $this->pdf->countX = 0;
      $this->pdf->countY = 0;
    }

    $posX = $this->pdf->marginLeft + ($this->pdf->countX * ($this->pdf->width + $this->pdf->xSpace));
    $posY = $this->pdf->marginTop + ($this->pdf->countY * ($this->pdf->height + $this->pdf->ySpace));
    //$this->pdf->SetXY($posX + $this->pdf->paddingLeft, $posY + $this->pdf->paddingTop);
    // Unlike for mailing labels X and Y origin will not include padding
    $this->pdf->SetXY($posX, $posY);
    $this->labelCreator($contactCardData); // Call custom method instead of CiviCRM's
    $this->pdf->countY++;

    if ($this->pdf->countY == $this->pdf->yNumber) {
      // End of column reached, we start a new one
      $this->pdf->countX++;
      $this->pdf->countY = 0;
    }
  }
  
  /**
   * Function to create / place labels on the PDF document
   *
   * @param $cardData
   */
  public function labelCreator($cardData) {
    // For accurate positioning this function positions most objects in the PDF by coordinates
    // Doing it except the QR code with HTML and CSS could be simpler though with less control.
    
    // Set up dimensions of objects (in mm)    
    $gapRows = 1.25; // Vertical gap between photo and QR code
    $gapCols = 1.25; // Horizontal gap between photo and QR code
    $QRCodeHeight = 0.42 * ($this->pdf->height - $this->pdf->paddingTop * 2 - $gapRows); // = QR code width too

    $logoFile = Civi::settings()->get('rswidcard_logo_url');
    //$logoFile = $this->baseUrl . 'images/PRRPS_logo.png';
    //$logoFile = $this->baseUrl . 'images/PRRPS_logo_railway_worker.png';
    
    $cardText = Civi::settings()->get('rswidcard_card_text');
    if ($cardText) {
      $titleText = '<p style="text-align: center;">' . $cardText . '</p>';
      //$titleText = ''; // Text is now incorporated into logo image so this is not required
    }
    
    // Get path of the contact photo file by converting it from the URL.
    // (CiviCRM stores an absolute URL in the db.) On some servers passing
    // a URL to TCPDF doesn't work, perhaps due to parameters in the URL.
    $photoFile = CRM_RswIdCard_Form_Task_IDCardCommon::getImageURL($cardData['image_URL']);
    
    // Set desired aspect ratio of photo w/h. Image will be cropped to this ratio.
    $photoAspectRatio = 3/4;
    // Determine the photo's native aspect ratio to be used in subsequent calculations
    $photoNativeAspectRatio = CRM_RswIdCard_Form_Task_IDCardCommon::imageAspectRatio($photoFile);

    //$bgImageFile = $this->baseUrl . 'images/Two_Y_class_locomotives_and_railway_employees_pose_at_Quorn_locomotive_sheds_c1890_Murray_Thompson_collection.jpg';
    $bgImageFile = Civi::settings()->get('rswidcard_bg_image_url');
    
    if (!array_key_exists('blank', $cardData)) {  
      $detailText = '<style>p {line-height: normal; margin: 0; padding: 0;}</style>
                     <span style="font-size: 9.5pt;">' . $cardData['name'] . '<br /></span>'.
                     '<span style="font-size: 8pt;">Issued: ' . $cardData['card_issue_date_formatted'] . '</span>';
    }
    else {
      $detailText = '<span style="font-size: 9.5pt;">Name:<br /></span>
                     <span style="font-size: 9.5pt;">Issued:<br /></span>
                     <span style="font-size: 9.5pt;">This is a temporary card</span>';
    }

    $x = $this->pdf->GetAbsX();
    $y = $this->pdf->getY();
    
    // Background image
    if ($this->print_bg_image) {      
      // Make background nearly opaque
      $this->pdf->SetAlpha(0.22);
      // The transform is for image cropping to maintain aspect ratio
      $this->pdf->StartTransform();
      //Clipping mask - rounded corners or not depending on selection
      if ($this->round_corners) {
        $this->pdf->RoundedRect($x-$this->bg_image_bleed, $y-$this->bg_image_bleed, $this->pdf->width + 2*$this->bg_image_bleed, $this->pdf->height + 2*$this->bg_image_bleed, 3.0, '1111', 'CNZ');
      }
      else {
        $this->pdf->Rect($x-$this->bg_image_bleed, $y-$this->bg_image_bleed, $this->pdf->width + 2*$this->bg_image_bleed, $this->pdf->height + 2*$this->bg_image_bleed, 'CNZ');
      }
      
      // Add image, filling the entire card and cropping using the above mask to maintain aspect ratio
      if (CRM_RswIdCard_Form_Task_IDCardCommon::imageAspectRatio($bgImageFile) > ($this->pdf->width + 2*$this->bg_image_bleed)/($this->pdf->height + 2*$this->bg_image_bleed)) {
        $this->pdf->Image($bgImageFile, $x-$this->bg_image_bleed, $y-$this->bg_image_bleed, '', $this->pdf->height + 2*$this->bg_image_bleed, '', true, '', false, 300);
      }
      else {
        $this->pdf->Image($bgImageFile, $x, $y, $this->pdf->width + 2*$this->bg_image_bleed, '', '', true, '', false, 300);
      }
      
      $this->pdf->StopTransform();
      $this->pdf->SetAlpha(1);
    }
    
    // If the print border option is selected draw a rectangle for each card's border. This would only be if not using Avery or similar stock and cards will be manually cut out.
    if ($this->print_border) {
      $border_style = array('width' => 0.06, 'cap' => 'round', 'join' => 'round', 'dash' => '0', 'color' => array(0, 0, 0));
      if ($this->round_corners) {
        $this->pdf->RoundedRect($x, $y, $this->pdf->width, $this->pdf->height, 3.0, '1111', 'D', array('all' => $border_style));
      }
      else {
        $this->pdf->Rect($x, $y, $this->pdf->width, $this->pdf->height, 'D', array('all' => $border_style));
      }
    }
    
    // Dimensions: right side objects    
    $row1Height = $this->pdf->height - $this->pdf->paddingTop * 2 - $gapRows - $QRCodeHeight;
    $photoHeight = $row1Height;
    
    $photoWidth = $photoAspectRatio * $photoHeight;
    $col2Width = max($photoWidth, $QRCodeHeight);
    
    // Left side objects
    if (!empty($titleText)) {
      $textRSWHeight = 4.24;
    } else {
      $textRSWHeight = 0;
    }
    
    $col1Width = $this->pdf->width - $this->pdf->paddingLeft * 2 - $col2Width - $gapCols;
    $logoHeight = $row1Height - $textRSWHeight;
    $logoAspectRatio = CRM_RswIdCard_Form_Task_IDCardCommon::imageAspectRatio($logoFile);
    $logoWidth = $logoHeight * $logoAspectRatio;

    if ($logoWidth > $col1Width) {
      $logoWidth = $col1Width;
      $logoHeight = $logoWidth * $logoAspectRatio;
    }

    $this->pdf->SetFont('helvetica', 'B', '11');
    $this->pdf->SetTextColor(120, 31, 28);
    if (!empty($titleText)) {
      $this->pdf->writeHTMLCell($col1Width, $textRSWHeight, $x + $this->pdf->paddingLeft, $this->pdf->paddingTop + $logoHeight + $y, $titleText);
    }   

    $this->pdf->Image($logoFile, $x + $this->pdf->paddingLeft + ($col1Width - $logoWidth) / 2, $y + $this->pdf->paddingTop, $logoWidth, $logoHeight,
      $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = true, $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());

    if ($photoFile) {
      // If the contact image is not of the correct aspect ratio it will be cropped using a clipping mask in TCPDF
      // This determines how big the uncropped photo needs to be to fill the allowed space
      $photoUncroppedWidth = max($photoNativeAspectRatio * $photoHeight, $photoWidth);
      if ($photoNativeAspectRatio > 0) {
        $photoUncroppedHeight = $photoUncroppedWidth / $photoNativeAspectRatio;
      }
      // Begin a transform to allow a clipping mask for cropping the photo if it is not of the correct aspect ratio
      $this->pdf->StartTransform();
      // Clipping mask
      $this->pdf->Rect($x + $this->pdf->width - $this->pdf->paddingLeft - $photoWidth, $y + $this->pdf->paddingTop, $photoWidth, $photoHeight, 'CNZ');
      // Get the photo file's contents - seems more robust than giving TCPDF the filename
      //var_dump($photoFile);
      $photo = file_get_contents($photoFile);
      // Centre the (uncropped) image
      $this->pdf->Image('@' . $photo, $x + $this->pdf->width - $this->pdf->paddingLeft - $photoUncroppedWidth + ($photoUncroppedWidth-$photoWidth)/2, $y + $this->pdf->paddingTop - ($photoUncroppedHeight-$photoHeight)/2, $photoUncroppedWidth, $photoUncroppedHeight, '', true, '', true, 300);
      //$this->pdf->Image($photoFile, $x + $this->pdf->width - $this->pdf->paddingLeft - $photoUncroppedWidth + ($photoUncroppedWidth-$photoWidth)/2, $y + $this->pdf->paddingTop - ($photoUncroppedHeight-$photoHeight)/2, $photoUncroppedWidth, $photoUncroppedHeight, '', true, '', true, 300);
      $this->pdf->StopTransform();
    }

    $this->pdf->SetTextColor(0, 0, 0);
    $this->pdf->SetFont('helvetica', '', '10');
    $this->pdf->writeHTMLCell($col1Width, 20, $this->pdf->paddingLeft + $x, $this->pdf->paddingTop + $row1Height + 6.5 + $y, $detailText);
    
    $style = array(
      'border' => false,
      'hpadding' => 'auto',
      'vpadding' => 'auto',
      'fgcolor' => array(0, 0, 0),
      'bgcolor' => array(255, 255, 255),
      'position' => '',
    );
    
    $xQRCode = $this->pdf->width - $this->pdf->paddingLeft - $QRCodeHeight;
    $yQRCode = $this->pdf->height - $this->pdf->paddingTop - $QRCodeHeight;
    
    // Create QR code if there is a valid key and contact id
    if (strlen($cardData['QR_key']) == 18 && $cardData['contact_id'] > 0) {
      // Testing showed that 'L' error correction, a contact ID of 7 digits and a key of 18 characters could be 
      // accommodated in a 29x29 QR code, which was the smallest QR that was feasible.
      // Increasing error correction level to 'M' meant that contact ID and key length were too compromised 
      // unless the QR code increased beyond 29x29. This seemed too fine given that it is not being printed very large on the card.
      //$barcode = "https://prr.org.au/rsw/?c=" . $cardData['contact_id'] ."&k=" . $cardData['QR_key'];
      $urlStub = rtrim(Civi::settings()->get('rswidcard_barcode_url'), '/');   
      $barcode = $urlStub . "/?c=" . $cardData['contact_id'] ."&k=" . $cardData['QR_key'];
      $this->pdf->write2DBarcode($barcode, 'QRCODE,L', $x + $xQRCode, $y + $yQRCode, $QRCodeHeight, $QRCodeHeight, $style, 'N');
    }
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  private function setUpBlankCards(&$rows, $number) {
    for ($count = 0; $count < $number; $count++) {
      $rows[$count] = array(
        'blank' => true,
        'image_URL' => null,
        'name' => null,
        'contact_id' => null,
      );
    }
  }
}
