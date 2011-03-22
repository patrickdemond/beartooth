<?php
/**
 * qnaire_add_sample.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package sabretooth\ui
 * @filesource
 */

namespace sabretooth\ui;

/**
 * widget qnaire add_sample
 * 
 * @package sabretooth\ui
 */
class qnaire_add_sample extends base_add_list
{
  /**
   * Constructor
   * 
   * Defines all variables which need to be set for the associated template.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $name The name of the sample.
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'qnaire', 'sample', $args );
  }

  /**
   * Overrides the sample list widget's method.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return int
   * @access protected
   */
  public function determine_sample_count( $modifier = NULL )
  {
    // only include samples which have no qnaire assigned to them
    if( is_null( $modifier ) ) $modifier = new \sabretooth\database\modifier();
    $modifier->where( 'qnaire_id', '=', NULL );
    return \sabretooth\database\sample::count( $modifier );
  }

  /**
   * Overrides the sample list widget's method.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return array( record )
   * @access protected
   */
  public function determine_sample_list( $modifier = NULL )
  {
    // only include samples which have no qnaire assigned to them
    if( is_null( $modifier ) ) $modifier = new \sabretooth\database\modifier();
    $modifier->where( 'qnaire_id', '=', NULL );
    return \sabretooth\database\sample::select( $modifier );
  }
}
?>
