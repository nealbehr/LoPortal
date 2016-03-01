/**
 * Created by Eugene Lysenko on 2/26/16.
 */
angular.module('loApp').directive(
    'loPropertyApprovalForm',
    ['$location', 'createAdminRequestFlyer', '$routeParams', 'parseGoogleAddressComponents', 'loadFile', '$timeout',
        'redirect', 'waitingScreen', 'getInfoFromGeocoder', '$q', 'loadGoogleMapsApi', '$rootScope', 'sessionMessages',
        'googleAddress',
        function ($location, createAdminRequestFlyer, $routeParams, parseGoogleAddressComponents, loadFile, $timeout,
                  redirect, waitingScreen, getInfoFromGeocoder, $q, loadGoogleMapsApi, $rootScope, sessionMessages,
                  googleAddress)
{
    return {
        restrict   : 'EA',
        templateUrl: 'template/directive/property-approval-form.html',
        scope      : {
            requestIn: "=loRequest",
            titles: "=loTitles",
            lat: "=loLat",
            lng: "=loLng"
        },
        link: function (scope, element, attrs, controllers) {
            // Information text
            scope.infoText = {
                buttonBuyer : 'There is an active buyer, or a homeowner looking to Refi.',
                buttonSeller: 'This property is being listed for sale. You can also make a listing flyer for these '
                    +'properties.'
            };

            scope.isValid = false;
            scope.request = {};
            scope.userType = settings.queue.userType;

            scope.$watch('requestIn', function (newVal, oldVal) {
                scope.request = scope.requestIn;
                if (scope.request.property && scope.request.property.address) {
                    scope.isValid = true;
                }
            });

            // Validation address string
            scope.changeSearchField = function () {
                scope.isValid = false;
                googleAddress.stringIsValid(scope.request.property.address).then(function (data) {
                    if (data.length > 0) {
                        scope.request.address = parseGoogleAddressComponents(data);
                        scope.isValid = googleAddress.objectIsValid(scope.request.address);

                    }
                    else {
                        scope.request.address = {};
                        scope.isValid = false;
                    }
                });
            };

            scope.cancel = function (e) {
                e.preventDefault();

                history.back();
            };

            var input = document.getElementById('searchPlace');

            function Message(sessionMessages) {
                this.addDanger = function (message) {
                    sessionMessages.addDanger(message);

                    return this;
                };

                this.show = function () {
                    this.rootScope.$broadcast('renderSessionMesages');
                }
            }

            Message.prototype.rootScope = $rootScope;

            var message = new Message(sessionMessages);

            scope.save = function (e) {
                e.preventDefault();

                waitingScreen.show();

                // Validation address object
                if (!googleAddress.objectIsValid(scope.request.address)) {
                    waitingScreen.hide();
                    scope.isValid = false;
                    message.addDanger('Address is invalid.').show();
                    return false;
                }

                scope.request.save()
                    // Save on backend
                    .success(function (data) {
                        $rootScope.$broadcast('propertyApprovalSaved', data);
                    })
                    .error(function (e) {
                        message.addDanger(typeof e == "object" && 'message' in e ? e.message : e)
                            .show()
                        ;
                    })
                    .finally(function () {
                        waitingScreen.hide();
                    })
                ;
            };

            function initialize(lat, lng) {
                var mapConfig = {
                        zoom: 13,
                        zoomProperty: 16
                    },
                    markers = [],
                    centerLatLng = new google.maps.LatLng(lat, lng),
                    map = new google.maps.Map(document.getElementById('location'), {
                        center: centerLatLng,
                        zoom: mapConfig.zoom,
                        disableDefaultUI: true,
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    });

                function setDefaultMarker() {
                    var marker = new google.maps.Marker({
                        position: centerLatLng
                    });

                    markers.push(marker);

                    marker.setMap(map);
                }

                setDefaultMarker();

                google.maps.event.addListener(map, 'click', function (event) {
                    waitingScreen.show();

                    for (var i = 0, marker; marker = markers[i]; i++) {
                        marker.setMap(null);
                    }

                    markers = [];

                    var marker = new google.maps.Marker({
                        position: event.latLng,
                        map: map
                    });

                    markers.push(marker);

                    map.setCenter(event.latLng);

                    getInfoFromGeocoder({location: event.latLng})
                        .then(function (address) {
                            if (address.length > 0) {
                                scope.request.address = parseGoogleAddressComponents(address[0].address_components);
                                scope.request.property.address = address[0].formatted_address;
                            }
                        })
                        .catch(function (e) {
                            scope.request.property.address = '';
                            scope.isValid = false;
                            message.addDanger(typeof e == 'string' ? e : e.message)
                                .show()
                            ;
                        })
                        .finally(function () {
                            waitingScreen.hide();
                        })
                    ;
                });

//                    var searchBox = new google.maps.places.SearchBox(input);
                var searchBox = new google.maps.places.Autocomplete(input, {types: ['geocode']});

                google.maps.event.addListener(searchBox, 'place_changed', function () {
                    scope.$apply(function () {
                        scope.isValid = false;
                    });

                    var places = [searchBox.getPlace()];

                    if (places.length == 0) {
                        return;
                    }

                    for (var i = 0, marker; marker = markers[i]; i++) {
                        marker.setMap(null);
                    }

                    // For each place, get the icon, place name, and location.
                    markers = [];
                    var bounds = new google.maps.LatLngBounds();

                    for (var i = 0, place; place = places[i]; i++) {
                        scope.$apply(function () {
                            scope.request.address = parseGoogleAddressComponents(place.address_components);
                            if ("formatted_address" in place) {
                                scope.request.property.address = place.formatted_address;
                            }
                        });

                        if (!place.address_components) {
                            continue;
                        }

                        var image = place.icon
                                ? {
                                url: place.icon,
                                size: new google.maps.Size(71, 71),
                                origin: new google.maps.Point(0, 0),
                                anchor: new google.maps.Point(17, 34),
                                scaledSize: new google.maps.Size(25, 25)
                            }
                                : null
                            ;

                        // Create a marker for each place.
                        var marker = new google.maps.Marker({
                            map: map,
                            icon: image,
                            title: place.name,
                            position: place.geometry.location
                        });

                        markers.push(marker);

                        bounds.extend(place.geometry.location);
                    }

                    if (markers.length < 1) {
                        setDefaultMarker();
                        map.setCenter(centerLatLng);
                        map.setZoom(mapConfig.zoom);
                    }
                    else {
                        // Zoom control for search property
                        map.setZoom(mapConfig.zoomProperty);
                        map.setCenter(marker.getPosition());

                        // Zoom control default
                        // map.fitBounds(bounds);
                    }
                });

                google.maps.event.addListener(map, 'bounds_changed', function () {
                    var bounds = map.getBounds();
                    searchBox.setBounds(bounds);
                });
            }

//                scope.$watch('lat', function(newVal, oldVal){
//                    if(!newVal || !scope.lng){
//                        return;
//                    }
//
//                    scope.loadMap();
//                });

            scope.$watch('lng', function (newVal, oldVal) {
                if (!newVal || !scope.lat) {
                    return;
                }

                scope.loadMap();
            });

            scope.loadMap = function () {
                loadGoogleMapsApi()
                    .then(function () {
                        initialize(scope.lat, scope.lng);
                    })
                ;
            }
        }
    }
}]);
