<?php
/**
 * user_add.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package beartooth\ui
 * @filesource
 */

namespace beartooth\ui\widget;
use beartooth\log, beartooth\util;
use beartooth\business as bus;
use beartooth\database as db;
use beartooth\exception as exc;

/**
 * widget user add
 * 
 * @package beartooth\ui
 */
class user_add extends \cenozo\ui\push\user_add
{
  /**
   * Finish setting the variables in a widget.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function finish()
  {
    parent::finish();
    
    $session = bus\session::self();
    $is_top_tier = 3 == $session->get_role()->tier;

    // create enum arrays
    $modifier = new db\modifier();
    $modifier->where( 'name', '!=', 'onyx' );
    $modifier->where( 'tier', '<=', $session->get_role()->tier );
    $roles = array();
    foreach( db\role::select( $modifier ) as $db_role ) $roles[$db_role->id] = $db_role->name;
    
    // set the view's items
    $this->set_item( 'role_id', array_search( 'interviewer', $roles ), true, $roles );

    $this->finish_setting_items();
  }
}
?>
