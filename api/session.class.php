<?php
/**
 * session.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package sabretooth
 */

namespace sabretooth;

require_once ADODB_PATH.'/adodb.inc.php';

/**
 * session: handles all session-based information
 *
 * The session class is used to track all information from the time a user logs into the system
 * until they log out.
 * This class is a singleton, instead of using the new operator call {@singleton() 
 * @package sabretooth
 */
final class session extends singleton
{
  /**
   * Constructor.
   * 
   * Since this class uses the singleton pattern the constructor is never called directly.  Instead
   * use the {@link singleton} method.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function __construct( $arguments )
  {
    // WARNING!  When we construct the session we haven't finished setting up the system yet, so
    // don't use the log class in this method!
    
    // one and only one argument should be past to the constructor
    assert( isset( $arguments ) && 1 == count( $arguments ) );
    
    // the first argument is the settings array from an .ini file
    $settings = $arguments[0];
    
    // make sure we have all necessary categories
    assert( isset( $settings[ 'general' ] ) && is_array( $settings[ 'general' ] ) &&
            isset( $settings[ 'db' ] ) && is_array( $settings[ 'general' ] ) );
    
    // copy the setting one category at a time, ignore any unknown categories
    $this->settings[ 'general' ] = $settings[ 'general' ];
    $this->settings[ 'db' ] = $settings[ 'db' ];

    // set error reporting
    error_reporting(
      $this->settings[ 'general' ][ 'development_mode' ] ? E_ALL | E_STRICT : E_ALL );
  }

  public function initialize()
  {
    // set up the database
    $this->db = ADONewConnection( session::self()->get_setting( 'db', 'driver' ) );
    
    if( false == $this->db->Connect(
      session::self()->get_setting( 'db', 'server' ),
      session::self()->get_setting( 'db', 'username' ),
      session::self()->get_setting( 'db', 'password' ),
      session::self()->get_setting( 'db', 'database' ) ) )
    {
      log::alert( 'Unable to connect to the database.' );
    }
    $this->db->SetFetchMode( ADODB_FETCH_ASSOC );
    
    // determine the user (setting the user will also set the site and role)
    $user_name = util::in_devel_mode() && defined( 'STDIN' )
               ? $_SERVER[ 'USER' ]
               : $_SERVER[ 'PHP_AUTH_USER' ];
    $this->set_user( database\user::get_unique_record( 'name', $user_name ) );
    if( NULL == $this->user ) log::err( 'User "'.$user_name.'" not found.' );
  }
  
  /**
   * Get the value of an .ini setting.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $category The category the setting belongs to.
   * @param string $name The name of the setting.
   * @access public
   */
  public function get_setting( $category, $name )
  {
    $value = NULL;
    if( !isset( $this->settings[ $category ] ) ||
        !isset( $this->settings[ $category ][ $name ] ) )
    {
      log::warning(
        "Tried getting value for setting [$category][$name] which doesn't exist." );
    }
    else
    {
      $value = $this->settings[ $category ][ $name ];
    }

    return $value;
  }

  /**
   * Get the database resource.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return database\site
   * @access public
   */
  public function get_db()
  {
    return $this->db;
  }

  /**
   * Get the current role.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return database\role
   * @access public
   */
  public function get_role() { return $this->role; }

  /**
   * Get the current site.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return database\site
   * @access public
   */
  public function get_site() { return $this->site; }

  /**
   * Get the current user.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return database\user
   * @access public
   */
  public function get_user() { return $this->user; }

  /**
   * Set the current site and role.
   * 
   * If the user does not have the proper access then nothing is changed.  
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\site $db_site
   * @param database\role $db_role
   * @access public
   */
  public function set_site_and_role( $db_site, $db_role )
  {
    if( is_null( $db_site ) || is_null( $db_role ) )
    {
      $this->site = NULL;
      $this->role = NULL;
      unset( $_SESSION['current_site_id'] );
      unset( $_SESSION['current_role_id'] );
    }
    else
    {
      // verify that the user has the right access
      if( $this->user->has_access( $db_site, $db_role ) )
      {
        $this->site = $db_site;
        $this->role = $db_role;
        $_SESSION['current_site_id'] = $this->site->id;
        $_SESSION['current_role_id'] = $this->role->id;
      }
    }
  }

  /**
   * Set the current user.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\user $db_user
   * @access public
   */
  public function set_user( $db_user )
  {
    $this->user = $db_user;

    // Determine the site and role
    if( is_null( $this->user ) )
    {
      $this->set_site_and_role( NULL, NULL );
    }
    else
    {
      // do not use set functions or we will loose cookies
      $this->site = NULL;
      $this->role = NULL;

      // see if we already have the current site stored in the php session
      if( isset( $_SESSION['current_site_id'] ) && isset( $_SESSION['current_role_id'] ) )
      {
        $this->set_site_and_role( new database\site( $_SESSION['current_site_id'] ),
                                  new database\role( $_SESSION['current_role_id'] ) );
      }
      
      // if we still don't have a site and role then pick the first one we can find
      if( is_null( $this->site ) || is_null( $this->role ) )
      {
        $db_site_array = $this->user->get_sites();
        if( 0 == count( $db_site_array ) )
          log::err( "User does not have access to any site." );
        $db_site = $db_site_array[0];
        $db_role_array = $this->user->get_roles( $db_site );
        $db_role = $db_role_array[0];

        $this->set_site_and_role( $db_site, $db_role );
      }
    }
  }

  /**
   * An array which holds .ini settings.
   * @var array( mixed )
   * @access private
   */
  private $settings = array();

  /**
   * A reference to the ADODB resource.
   * @var resource
   * @access private
   */
  private $db = NULL;

  /**
   * The active record of the current user.
   * @var database\user
   * @access private
   */
  private $user = NULL;

  /**
   * The active record of the current site.
   * @var database\site
   * @access private
   */
  private $site = NULL;

  /**
   * The last widget loaded into the main slot
   * @var string
   * @access private
   */
  private $main_slot = 'home';
}
?>