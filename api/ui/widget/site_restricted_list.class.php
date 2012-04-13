<?php
/**
 * site_restricted_list.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package beartooth\ui
 * @filesource
 */

namespace beartooth\ui\widget;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * Base class for all list widgets which may be restricted by site.
 * 
 * @package beartooth\ui
 */
abstract class site_restricted_list extends \cenozo\ui\widget\site_restricted_list
{
  /**
   * Overrides the parent class method based on the restrict site member.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return int
   * @access protected
   */
  protected function determine_record_count( $modifier = NULL )
  {
    if( !is_null( $this->db_restrict_site ) )
    {
      if( NULL == $modifier ) $modifier = lib::create( 'database\modifier' );
      $site_column = ( $this->jurisdiction_based ? 'jurisdiction.' : '' ).'site_id';
      $modifier->where( $site_column, '=', $this->db_restrict_site->id );
    }

    // skip the parent method
    // php doesn't allow parent::parent::method() so we have to do the less safe code below
    $base_list_class_name = lib::get_class_name( 'ui\widget\base_list' );
    return $base_list_class_name::determine_record_count( $modifier );
  }

  /**
   * Overrides the parent class method based on the restrict site member.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\modifier $modifier Modifications to the list.
   * @return array( record )
   * @access protected
   */
  protected function determine_record_list( $modifier = NULL )
  {
    if( !is_null( $this->db_restrict_site ) )
    {
      if( NULL == $modifier ) $modifier = lib::create( 'database\modifier' );
      $site_column = ( $this->jurisdiction_based ? 'jurisdiction.' : '' ).'site_id';
      $modifier->where( $site_column, '=', $this->db_restrict_site->id );
    }

    // skip the parent method
    // php doesn't allow parent::parent::method() so we have to do the less safe code below
    $base_list_class_name = lib::get_class_name( 'ui\widget\base_list' );
    return $base_list_class_name::determine_record_list( $modifier );
  }

  /**
   * Whether the subject is jurisdiction based.
   * @var boolean
   * @access protected
   */
  protected $jurisdiction_based = false;
}
?>