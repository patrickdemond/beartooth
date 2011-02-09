<?php
/**
 * operation_list.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package sabretooth\ui
 * @filesource
 */

namespace sabretooth\ui;

/**
 * operation.list widget
 * 
 * @package sabretooth\ui
 */
class operation_list extends base_list
{
  /**
   * Constructor
   * 
   * Defines all variables required by the operation list.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'operation', $args );
    
    $session = \sabretooth\session::self();

    // define all template variables for this list
    $this->set_heading( 'Operation list' );
    $this->checkable = false;
    $this->viewable = false;
    $this->editable = false;
    $this->removable = false;

    $this->columns = array(
      array( 'id' => 'type',
             'heading' => 'type',
             'sortable' => true ),
      array( 'id' => 'subject',
             'heading' => 'subject',
             'sortable' => true ),
      array( 'id' => 'name',
             'heading' => 'name',
             'sortable' => true ),
      array( 'id' => 'restricted',
             'heading' => 'restricted',
             'sortable' => false ),
      array( 'id' => 'description',
             'heading' => 'description',
             'sortable' => false,
             'align' => 'left' ) );
  }

  /**
   * Set the rows array needed by the template.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function set_rows()
  {
    // reset the array
    $this->rows = array();
    
    foreach( $this->get_record_list() as $record )
    {
      array_push( $this->rows, 
        array( 'id' => $record->id,
               'columns' => array( 'type' => $record->type,
                                   'subject' => $record->subject,
                                   'name' => $record->name,
                                   'restricted' => $record->restricted ? 'yes' : 'no',
                                   'description' => $record->description ) ) );
    }
  }
}
?>