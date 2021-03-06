<?php
/**
 * head.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 */

namespace beartooth\service\appointment;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * The base class of all head services
 */
class head extends \cenozo\service\head
{
  /**
   * Extends parent method
   */
  protected function setup()
  {
    parent::setup();

    $this->columns['disable_mail'] = array(
      'data_type' => 'tinyint',
      'default' => '0',
      'required' => '1'
    );
  }
}
