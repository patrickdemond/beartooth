<?php
/**
 * self_menu.class.php
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
 * widget self menu
 * 
 * @package beartooth\ui
 */
class self_menu extends \cenozo\ui\push\self_menu
{
  /**
   * Constructor
   * 
   * Defines all variables which need to be set for the associated template.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'self', 'menu', $args );

    $exclude = array(
      'address',
      'appointment',
      'availability',
      'consent',
      'interviewer',
      'phase',
      'phone',
      'phone_call' );
    $this->exclude_widget_list = array_merge( $this->exclude_widget_list, $exclude );
  }

  /**
   * Finish setting the variables in a widget.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function finish()
  {
    parent::finish();

    $lists = $this->get_variables( 'lists' );

    // insert the participant tree after participant list
    if( 'interviewer' != bus\session::self()->get_role() )
      $lists[] = array( 'heading' => 'Participant Tree',
                        'subject' => 'participant',
                        'name' => 'tree' );

    $this->set_variable( 'lists', $lists );
  }
}
?>
