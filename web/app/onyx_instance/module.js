define( function() {
  'use strict';

  try { var module = cenozoApp.module( 'onyx_instance', true ); } catch( err ) { console.warn( err ); return; }
  angular.extend( module, {
    identifier: {}, // standard
    name: {
      singular: 'onyx instance',
      plural: 'onyx instances',
      possessive: 'onyx instance\'s',
      friendlyColumn: 'username'
    },
    columnList: {
      name: {
        column: 'user.name',
        title: 'Name'
      },
      interviewer: {
        column: 'interviewer.name',
        title: 'Interviewer',
        help: 'Blank for site onyx-instances.'
      },
      active: {
        column: 'user.active',
        title: 'Active',
        type: 'boolean'
      },
      last_access_datetime: {
        title: 'Last Activity',
        type: 'datetime'
      }
    },
    defaultOrder: {
      column: 'name',
      reverse: false
    }
  } );

  module.addInputGroup( '', {
    active: {
      title: 'Active',
      type: 'boolean'
    },
    username: {
      title: 'Username',
      type: 'string'
    },
    password: {
      title: 'Password',
      type: 'string',
      regex: '^((?!(password)).){8,}$', // length >= 8 and can't have "password"
      isExcluded: 'view',
      help: 'Passwords must be at least 8 characters long and cannot contain the word "password"'
    },
    interviewer_user_id: {
      title: 'Interviewer',
      type: 'lookup-typeahead',
      typeahead: {
        table: 'user',
        select: 'CONCAT( user.first_name, " ", user.last_name, " (", user.name, ")" )',
        where: [ 'user.first_name', 'user.last_name', 'user.name' ]
      },
      help: 'Determines which interviewer this instance belongs to, or blank if this is a site instance.'
    }
  } );

  if( angular.isDefined( module.actions.edit ) ) {
    module.addExtraOperation( 'view', {
      title: 'Set Password',
      operation: function( $state, model ) { model.viewModel.setPassword(); }
    } );
  }

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnOnyxInstanceAdd', [
    'CnOnyxInstanceModelFactory',
    function( CnOnyxInstanceModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'add.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnOnyxInstanceModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnOnyxInstanceList', [
    'CnOnyxInstanceModelFactory',
    function( CnOnyxInstanceModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'list.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnOnyxInstanceModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.directive( 'cnOnyxInstanceView', [
    'CnOnyxInstanceModelFactory',
    function( CnOnyxInstanceModelFactory ) {
      return {
        templateUrl: module.getFileUrl( 'view.tpl.html' ),
        restrict: 'E',
        scope: { model: '=?' },
        controller: function( $scope ) {
          if( angular.isUndefined( $scope.model ) ) $scope.model = CnOnyxInstanceModelFactory.root;
        }
      };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnOnyxInstanceAddFactory', [
    'CnBaseAddFactory',
    function( CnBaseAddFactory ) {
      var object = function( parentModel ) { CnBaseAddFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnOnyxInstanceListFactory', [
    'CnBaseListFactory',
    function( CnBaseListFactory ) {
      var object = function( parentModel ) { CnBaseListFactory.construct( this, parentModel ); };
      return { instance: function( parentModel ) { return new object( parentModel ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnOnyxInstanceViewFactory', [
    'CnBaseViewFactory', 'CnModalPasswordFactory', 'CnModalMessageFactory', 'CnHttpFactory',
    function( CnBaseViewFactory, CnModalPasswordFactory, CnModalMessageFactory, CnHttpFactory ) {
      var object = function( parentModel, root ) {
        var self = this;
        CnBaseViewFactory.construct( this, parentModel, root, 'activity' );

        // custom operation
        this.setPassword = function() {
          CnModalPasswordFactory.instance( {
            confirm: false,
            showCancel: true
          } ).show().then( function( response ) {
            if( angular.isObject( response ) ) {
              CnHttpFactory.instance( {
                path: 'onyx_instance/' + self.record.getIdentifier(),
                data: { password: response.requestedPass },
                onError: function( response ) {
                  if( 403 == response.status ) {
                    CnModalMessageFactory.instance( {
                      title: 'Unable To Change Password',
                      message: 'Sorry, you do not have access to resetting the password for this onyx instance.',
                      error: true
                    } ).show();
                  } else { CnModalMessageFactory.httpError( response ); }
                }
              } ).patch().then( function() {
                CnModalMessageFactory.instance( {
                  title: 'Password Reset',
                  message: 'The password has been successfully changed.'
                } ).show();
              } );
            }
          } );
        };
      }
      return { instance: function( parentModel, root ) { return new object( parentModel, root ); } };
    }
  ] );

  /* ######################################################################################################## */
  cenozo.providers.factory( 'CnOnyxInstanceModelFactory', [
    'CnBaseModelFactory',
    'CnOnyxInstanceAddFactory', 'CnOnyxInstanceListFactory', 'CnOnyxInstanceViewFactory',
    'CnHttpFactory',
    function( CnBaseModelFactory,
              CnOnyxInstanceAddFactory, CnOnyxInstanceListFactory, CnOnyxInstanceViewFactory,
              CnHttpFactory ) {
      var object = function( root ) {
        var self = this;
        CnBaseModelFactory.construct( this, module );
        this.addModel = CnOnyxInstanceAddFactory.instance( this );
        this.listModel = CnOnyxInstanceListFactory.instance( this );
        this.viewModel = CnOnyxInstanceViewFactory.instance( this, root );
      };

      return {
        root: new object( true ),
        instance: function() { return new object( false ); }
      };
    }
  ] );

} );
