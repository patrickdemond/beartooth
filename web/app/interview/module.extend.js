// extend the framework's module
define( [ cenozoApp.module( 'interview' ).getFileUrl( 'module.js' ) ], function() {
  'use strict';

  var module = cenozoApp.module( 'interview' );

  cenozo.insertPropertyAfter( module.columnList, 'uid', 'qnaire', {
    column: 'qnaire.name',
    title: 'Questionnaire'
  } );

  // add future_appointment as a hidden input (to be used below)
  module.addInput( '', 'future_appointment', { type: 'hidden' } );
  module.addInput( '', 'last_participation_consent', { type: 'hidden' } );
  module.addInput( '', 'qnaire_id', {
    title: 'Questionnaire',
    type: 'enum',
    isConstant: true
  }, 'participant' );

  // extend the list factory
  cenozo.providers.decorator( 'CnInterviewListFactory', [
    '$delegate', 'CnHttpFactory',
    function( $delegate, CnHttpFactory ) {
      var instance = $delegate.instance;
      $delegate.instance = function( parentModel ) {
        var object = instance( parentModel );

        // enable the add button if:
        //   1) the interview list's parent is a participant model
        //   2) all interviews are complete for this participant
        //   3) another qnaire is available for this participant
        object.afterList( function() {
          object.parentModel.getAddEnabled = function() { return false; };

          var path = object.parentModel.getServiceCollectionPath();
          if( 'participant' == object.parentModel.getSubjectFromState() &&
              null !== path.match( /participant\/[^\/]+\/interview/ ) ) {
            var queueRank = null;
            var qnaireRank = null;
            var lastInterview = null;

            // get the participant's last interview
            CnHttpFactory.instance( {
              path: path,
              data: {
                modifier: { order: { 'qnaire.rank': true }, limit: 1 },
                select: { column: [ { table: 'qnaire', column: 'rank' }, 'end_datetime' ] }
              },
              onError: function( response ) {} // ignore errors
            } ).query().then( function( response ) {
              if( 0 < response.data.length ) lastInterview = response.data[0];

              // get the participant's current queue rank
              return CnHttpFactory.instance( {
                path: path.replace( '/interview', '' ),
                data: {
                  select: { column: [
                    { table: 'queue', column: 'rank', alias: 'queueRank' },
                    { table: 'qnaire', column: 'rank', alias: 'qnaireRank' }
                  ] }
                },
                onError: function( response ) {} // ignore errors
              } ).query().then( function( response ) {
                queueRank = response.data.queueRank;
                qnaireRank = response.data.qnaireRank;
              } );
            } ).then( function( response ) {
              object.parentModel.getAddEnabled = function() {
                return object.parentModel.$$getAddEnabled() &&
                       null != queueRank &&
                       null != qnaireRank && (
                         null == lastInterview || (
                           null != lastInterview.end_datetime &&
                           lastInterview.rank != qnaireRank
                         )
                       );
              };
            } );
          }
        } );

        return object;
      };
      return $delegate;
    }
  ] );

  // extend the view factory
  cenozo.providers.decorator( 'CnInterviewViewFactory', [
    '$delegate', '$state',
    function( $delegate, $state ) {
      var instance = $delegate.instance;
      $delegate.instance = function( parentModel, root ) {
        var object = instance( parentModel, root );

        function getAppointmentEnabled( type ) {
          var completed = null !== object.record.end_datetime;
          var participating = false !== object.record.last_participation_consent;
          var future = object.record.future_appointment;
          return 'add' == type ? ( !completed && participating && !future ) : future;
        }

        function updateEnableFunctions() {
          object.appointmentModel.getAddEnabled = function() {
            return angular.isDefined( object.appointmentModel.module.actions.add ) &&
                   getAppointmentEnabled( 'add' );
          };
          object.appointmentModel.getDeleteEnabled = function() {
            return angular.isDefined( object.appointmentModel.module.actions.delete ) &&
                   getAppointmentEnabled( 'delete' );
          };
        }

        // override onView
        object.onView = function( force ) {
          return object.$$onView( force ).then( function() {
            // check that the state type matches the interview's type
            if( $state.params.type != object.record.type ) {
              $state.go( 'error.404' );
              throw new Error( 'Interview type does not match state parameters, redirecting to 404.' );
            }

            // set the correct type and refresh the list
            if( angular.isDefined( object.appointmentModel ) ) updateEnableFunctions();
          } );
        };

        // override appointment list's onDelete
        object.deferred.promise.then( function() {
          if( angular.isDefined( object.appointmentModel ) ) {
            object.appointmentModel.listModel.onDelete = function( record ) {
              return object.appointmentModel.listModel.$$onDelete( record ).then( function() {
                object.onView();
              } );
            };
          }
        } );

        return object;
      };
      return $delegate;
    }
  ] );

  // extend the model factory
  cenozo.providers.decorator( 'CnInterviewModelFactory', [
    '$delegate', '$state', 'CnHttpFactory',
    function( $delegate, $state, CnHttpFactory ) {
      var instance = $delegate.instance;
      // extend getBreadcrumbTitle
      // (metadata's promise will have already returned so we don't have to wait for it)
      function extendObject( object ) {
        object.type = $state.params.type;

        angular.extend( object, {
          getBreadcrumbTitle: function() {
            var qnaire = object.metadata.columnList.qnaire_id.enumList.findByProperty(
              'value', object.viewModel.record.qnaire_id );
            return qnaire ? qnaire.name : 'unknown';
          },

          // pass type when transitioning to view state
          transitionToViewState: function( record ) {
            return $state.go(
              object.module.subject.snake + '.view',
              { type: record.type, identifier: record.getIdentifier() }
            );
          },

          // extend getMetadata
          getMetadata: function() {
            return object.$$getMetadata().then( function() {
              return CnHttpFactory.instance( {
                path: 'qnaire',
                data: {
                  select: { column: [ 'id', 'name' ] },
                  modifier: { order: 'rank' }
                }
              } ).query().then( function success( response ) {
                object.metadata.columnList.qnaire_id.enumList = [];
                response.data.forEach( function( item ) {
                  object.metadata.columnList.qnaire_id.enumList.push( { value: item.id, name: item.name } );
                } );
              } );
            } );
          }
        } );
      }

      extendObject( $delegate.root );

      $delegate.instance = function() {
        var object = instance();
        extendObject( object );
        return object;
      };

      return $delegate;
    }
  ] );

} );
