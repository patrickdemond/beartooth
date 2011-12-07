<?php
/**
 * setting_manager.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package beartooth\business
 * @filesource
 */

namespace beartooth\business;
use beartooth\log, beartooth\util;
use beartooth\database as db;
use beartooth\exception as exc;

/**
 * Manages software settings
 * 
 * @package beartooth\business
 */
class setting_manager extends \cenozo\business\setting_manager
{
  /**
   * Constructor.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @throws exception\argument
   * @access protected
   */
  protected function __construct( $arguments )
  {
    parent::__construct( $arguments );

    $static_settings = $arguments[0];

    // add a few categories to the manager
    foreach( array( 'audit_db', 'voip' ) as $category )
    {
      // make sure the category exists
      if( !array_key_exists( $category, $static_settings ) )
        throw new exc\argument( 'static_settings['.$category.']', NULL, __METHOD__ );
      
      $this->static_settings[$category] = $static_settings[$category];
    }

    // get the survey database settings from the limesurvey config file
    $file = LIMESURVEY_PATH.'/config.php';
    if( !file_exists( $file ) )
      throw new exc\runtime( 'Cannot find limesurvey config.php file.', __METHOD__ );

    include $file;
    $this->static_settings['survey_db'] =
      array( 'driver' => $databasetype,
             'server' => $databaselocation,
             'username' => $databaseuser,
             'password' => $databasepass,
             'database' => $databasename,
             'prefix' => $dbprefix );

    // have the audit settings mirror limesurvey, if necessary
    foreach( $this->static_settings['audit_db'] as $key => $value )
    {
      if( false === $value && 'enabled' != $key )
        $this->static_settings['audit_db'][$key] =
          $this->static_settings['survey_db'][$key];
    }
  }
}
