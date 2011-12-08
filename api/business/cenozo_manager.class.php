<?php
/**
 * cenozo_manager.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package cenozo\business
 * @filesource
 */

namespace beartooth\business;
use beartooth\log, beartooth\util;
use beartooth\database as db;
use beartooth\exception as exc;

/**
 * Extends Cenozo's manager with custom methods
 * 
 * @package beartooth\business
 */
class cenozo_manager extends \cenozo\business\cenozo_manager
{
  /**
   * Identical to the parent method but adds additional noid information to identify the site
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function set_site( $db_site )
  {
    $request = new \HttpRequest();
    $request->enableCookies();
    $request->setUrl( $this->base_url.'self/set_site' );
    $request->setMethod( \HttpRequest::METH_POST );
    $request->setPostFields(
      array( 'noid' => array( 'site.name' => $db_site->name, 'site.cohort' => 'comprehensive' ) ) );
    static::send( $request );

  }
}