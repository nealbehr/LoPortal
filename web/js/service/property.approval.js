(function(settings){
    'use strict';
    settings = settings || {};

    var propertyApproval = angular.module('approvalModule', []);

    propertyApproval.service("createPropertyApproval", ["$q", "$http", "createPropertyApprovalBase", function($q, $http, createPropertyApprovalBase){
        return function(){
            var approval = new createPropertyApprovalBase();

            approval.save = function(){
                this.property.state = settings.queue.state.requested;
                return $http.post('/request/approval', this.getFields4Save());
            };

            return approval;
        }
    }]);

    propertyApproval.service("createAdminPropertyApproval", ["$q", "$http", "createPropertyApprovalBase", function($q, $http, createPropertyApprovalBase){
        return function(){
            var approval = new createPropertyApprovalBase();

            approval.get = function(id){
                if(this.id !== null){
                    return $q.when(this);
                }

                var deferred = $q.defer();
                $http.get('/admin/approval/' + id)
                    .success(function(data){
                        approval.id = id;
                        approval.fill(data);

                        deferred.resolve(approval)
                    })
                    .error(function(data){
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            };

            approval.save = function(){
                return this.id? this.update(): this.add();
            };

            approval.update = function(){
                return $http.put('/admin/approval/' + this.id, this.getFields4Save())
            };

            approval.add = function(){
                throw new Error("ID not found");
            };

            return approval;
        }
    }]);

    propertyApproval.service("createPropertyApprovalBase", [function(){
        return function(){
            this.id = null;

            this.property = {
                 address: '',
                state: null
            };

            this.address = null;

            this.fill = function(data){
                for(var i in data){
                    this[i] = data[i];
                }

                return this;
            };

            this.getFields4Save = function(){
                var result = {};
                for(var i in this){
                    if(typeof this[i] === "object" && this[i] !== null){
                        result[i] = this[i];
                    }
                }

                return result;
            };

            this.save = function(){
                throw new Error("Request save must be override");
            }
        }
    }]);

    propertyApproval.directive('loPropertyApprovalEdit', ["$location", "createAdminRequestFlyer", "$routeParams", "parseGoogleAddressComponents", "loadFile", "$timeout", "redirect", "waitingScreen", "getInfoFromGeocoder", "$q", "loadGoogleMapsApi", "$rootScope", "sessionMessages", function($location, createAdminRequestFlyer, $routeParams, parseGoogleAddressComponents, loadFile, $timeout, redirect, waitingScreen, getInfoFromGeocoder, $q, loadGoogleMapsApi, $rootScope, sessionMessages){
        return {
            restrict: 'EA',
            templateUrl: '/partials/request.property.approval.form',
            scope: {
                requestIn: "=loRequest",
                titles:  "=loTitles",
                lat:     "=loLat",
                lng:     "=loLng"
            },
            link: function(scope, element, attrs, controllers) {
                scope.isValid = false;
                scope.request = {};

                scope.$watch('requestIn', function(newVal, oldVal){
//                    if(newVal != oldVal){
                        scope.request = scope.requestIn;
                        if(scope.request.property && scope.request.property.address){
                            scope.isValid = true;
                        }
//                    }
                });

                scope.changeSearchField = function(o){
                    scope.isValid = false;
                };

                scope.cancel = function(e){
                    e.preventDefault();

                    history.back();
                };

                var input = document.getElementById('searchPlace');

                function Message(sessionMessages){
                    this.addDanger = function(message){
                        sessionMessages.addDanger(message);

                        return this;
                    };

                    this.show = function(){
                        this.rootScope.$broadcast('showSessionMessage');
                    }
                }

                Message.prototype.rootScope = $rootScope;

                var message = new Message(sessionMessages);

                scope.$watch('request.address', function(newVal, oldVal){
                    if(newVal == oldVal){
                        return;
                    }

                    for(var i in scope.request.address){
                        if(scope.request.address[i] == '' || scope.request.address[i] == null){
                            scope.isValid = false;
                            message.addDanger('Address is invalid.')
                                .show()
                            ;
                            return;
                        }
                    }

                    scope.isValid = true;
                });

                scope.checkAddress = function(){
                    getInfoFromGeocoder({address: this.request.property.address})
                        .then(function(data){
                            scope.request.address = parseGoogleAddressComponents(data[0].address_components);

                            scope.request.property.address = data[0].formatted_address;
                        })
                        .catch(function(e){
                            scope.isValid = false;
                            message.addDanger(typeof e == 'string'? e: e.message)
                                .show()
                            ;
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        })
                    ;
                };

                scope.save = function(){
                    waitingScreen.show();
                    if(this.isValid == false){
                        this.checkAddress();
                        return;
                    }

                    scope.request.save()
                        // Save on backend
                        .success(function(data) {
                            $rootScope.$broadcast('propertyApprovalSaved', data);
                        })
                        .error(function(e){
                            message.addDanger(typeof e == "object" && 'message' in e ? e.message: e)
                                .show()
                            ;
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        })
                    ;
                };

                function initialize(lat, lng) {
                    var markers = [];
                    var centerLatLng = new google.maps.LatLng(lat, lng);
                    var mapProp = {
                        center: centerLatLng,
                        zoom:10,
                        disableDefaultUI:true,
                        mapTypeId:google.maps.MapTypeId.ROADMAP
                    };
                    var map=new google.maps.Map(document.getElementById("location"), mapProp);

                    function setDefaultMarker(){
                        var marker = new google.maps.Marker({
                            position: centerLatLng
                        });

                        markers.push(marker);

                        marker.setMap(map);
                    }

                    setDefaultMarker();

                    google.maps.event.addListener(map, 'click', function(event) {
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
                            .then(function(address){
                                if(address.length > 0){
                                    scope.request.address = parseGoogleAddressComponents(address[0].address_components);
                                    scope.request.property.address = address[0].formatted_address;
                                }
                            })
                            .catch(function(e){
                                scope.request.property.address = '';
                                scope.isValid = false;
                                message.addDanger(typeof e == 'string'? e: e.message)
                                    .show()
                                ;
                            })
                            .finally(function(){
                                waitingScreen.hide();
                            })
                        ;
                    });

//                    var searchBox = new google.maps.places.SearchBox(input);
                    var searchBox = new google.maps.places.Autocomplete(input, { types: ['geocode'] });

                    google.maps.event.addListener(searchBox, 'place_changed', function() {
                        scope.$apply(function(){
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
                            scope.$apply(function(){
                                scope.request.address = parseGoogleAddressComponents(place.address_components);
                                if("formatted_address" in place){
                                    scope.request.property.address = place.formatted_address;
                                }
                            });

                            if(!place.address_components){
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

                        if(markers.length < 1){
                            setDefaultMarker();
                            map.setCenter(centerLatLng);
                            map.setZoom(10);
                        }else{
                            map.fitBounds(bounds);
                        }
                    });

                    google.maps.event.addListener(map, 'bounds_changed', function() {
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

                scope.$watch('lng', function(newVal, oldVal){
                    if(!newVal || !scope.lat){
                        return;
                    }

                    scope.loadMap();
                });

                scope.loadMap = function(){
                    loadGoogleMapsApi()
                        .then(function(){
                            initialize(scope.lat, scope.lng);
                        })
                    ;
                }
            }
        }
    }]);
})(settings);