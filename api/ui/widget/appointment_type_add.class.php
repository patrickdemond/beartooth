<?php
/**
 * appointment_type_add.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace beartooth\ui\widget;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * widget appointment_type add
 */
class appointment_type_add extends \cenozo\ui\widget\base_view
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
    parent::__construct( 'appointment_type', 'add', $args );
  }

  /**
   * Processes arguments, preparing them for the operation.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @throws exception\notice
   * @access protected
   */
  protected function prepare()
  {
    parent::prepare();
    
    // add items to the view
    $this->add_item( 'qnaire_id', 'hidden' );
    $this->add_item( 'name', 'string', 'Name' );
    $this->add_item( 'description', 'text', 'Description' );
  }

  /**
   * Sets up the operation with any pre-execution instructions that may be necessary.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function setup()
  {
    parent::setup();
    
    // this widget must have a parent, and it's subject must be a qnaire
    if( is_null( $this->parent ) || 'qnaire' != $this->parent->get_subject() )
      throw lib::create( 'exception\runtime',
        'Phase widget must have a parent with qnaire as the subject.', __METHOD__ );
    
    // set the view's items
    $this->set_item( 'qnaire_id', $this->parent->get_record()->id );
    $this->set_item( 'name', '', true );
    $this->set_item( 'description', '' );
  }
}
