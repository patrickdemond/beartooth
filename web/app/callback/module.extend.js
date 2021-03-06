define( [ cenozoApp.module( 'callback' ).getFileUrl( 'module.js' ) ], function() {
  'use strict';

  var module = cenozoApp.module( 'callback' );

  // extend the model factory so that callback events are coloured green when the next interview is at home
  cenozo.providers.decorator( 'CnCallbackCalendarFactory', [
    '$delegate', 'CnHttpFactory',
    function( $delegate, CnHttpFactory ) {
      var instance = $delegate.instance;
      $delegate.instance = function( parentModel, site ) {
        var object = instance( parentModel, site );
        var onCalendar = object.onCalendar;
        object.onCalendar = function( replace, minDate, maxDate, ignoreParent ) {
          return onCalendar( replace, minDate, maxDate, ignoreParent ).then( function() {
            // get a list of all participants callbacks that aren't coloured yet
            var participantIdList = object.cache.filter( item => angular.isUndefined( item.color ) )
                                                .map( item => parseInt( item.getIdentifier() ) );

            if( 0 < participantIdList.length ) {
              CnHttpFactory.instance( {
                path: 'participant',
                data: {
                  select: { column: ['uid', 'qnaire_type'] },
                  modifier: { where: { column: 'participant.id', operator: 'IN', value: participantIdList } }
                }
              } ).get().then( function( response ) {
                var participants = response.data.reduce( function( object, participant ) {
                  object[participant.uid] = participant.qnaire_type;
                  return object;
                }, {} );
                object.cache.filter( item => angular.isUndefined( item.color ) ).forEach( function( item ) {
                  item.color = 'site' == participants[item.title] ? 'default' : 'green';
                } );
                angular.element( 'div.calendar' ).fullCalendar( 'refetchEvents' );
              } );
            }
          } );
        };
        return object;
      };
      return $delegate;
    }
  ] );

} );
