<?php
/**
 * site_add.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package sabretooth\ui
 * @filesource
 */

namespace sabretooth\ui;

/**
 * widget site add
 * 
 * @package sabretooth\ui
 */
class site_add extends base_view
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
    parent::__construct( 'site', 'add', $args );
    
    // define all columns defining this record
    $this->add_item( 'name', 'string', 'Name' );
    $this->add_item( 'timezone', 'enum', 'Time Zone' );
    $this->add_item( 'operators_expected', 'number', 'Minimum expected operators' );
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
    
    // create enum arrays
    $timezones = \sabretooth\database\site::get_enum_values( 'timezone' );
    $timezones = array_combine( $timezones, $timezones );

    // set the view's items
    $this->set_item( 'name', '', true );
    $this->set_item( 'timezone', key( $timezones ), true, $timezones );
    $this->set_item( 'operators_expected', 0, true );

    $this->finish_setting_items();
  }
}
?>