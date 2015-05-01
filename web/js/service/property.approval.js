(function(settings){
    'use strict';
    settings = settings || {};

    var propertyApproval = angular.module('approvalModule', []);

    propertyApproval.service("createPropertyApproval", ["$q", "$http", "createPropertyApprovalBase", function($q, $http, createPropertyApprovalBase){
        return function(){
            var approval = new createPropertyApprovalBase();

            approval.save = function(){
                var deferred = $q.defer();
                $http.post('/request/approval', this.getFields4Save())
                    .success(function(data){
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            }

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
            }

            approval.save = function(){
                return this.id? this.update(): this.add();
            }

            approval.update = function(){
                var deferred = $q.defer();
                $http.put('/admin/approval/' + this.id, this.getFields4Save())
                    .success(function(data){
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        console.log(data);
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            }

            approval.add = function(){
                throw new Error("ID not found");
            }

            return approval;
        }
    }]);

    propertyApproval.service("createPropertyApprovalBase", [function(){
        return function(){
            this.id = null;

            this.property = {
                    address: ''
            };

            this.address = null

            this.fill = function(data){
                for(var i in data){
                    this[i] = data[i];
                }

                return this;
            }

            this.getFields4Save = function(){
                var result = {};
                for(var i in this){
                    if(typeof this[i] === "object" && this[i] !== null){
                        result[i] = this[i];
                    }
                }

                return result;
            }

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
                request: "=loRequest",
                titles:  "=loTitles",
                lat:     "=loLat",
                lng:     "=loLng"
            },
            link: function(scope, element, attrs, controllers) {
                scope.isValid = false;

                scope.$watch('request.property.address', function(newVal, oldVal){
                    if(newVal != oldVal){
                        scope.isValid = !!scope.request.property.address;
                    }
                });

                scope.changeSearchField = function(o){
                    scope.isValid = false;
                }

                scope.cancel = function(e){
                    e.preventDefault();

                    history.back();
                }

                var input = document.getElementById('searchPlace');

                function Message(sessionMessages){
                    this.addDanger = function(message){
                        sessionMessages.addDanger(message);

                        return this;
                    }

                    this.show = function(){
                        this.rootScope.$broadcast('showSessionMessage');
                    }
                }

                Message.prototype.rootScope = $rootScope;

                var message = new Message(sessionMessages);

                scope.$watch('request.address', function(newVal){
                    for(var i in scope.request.address){
                        if(scope.request.address[i] == '' || scope.request.address[i] == null){
                            scope.isValid = false;
                            message.addDanger('Address is invalid.')
                                .show()
                            ;
                            break;
                        }
                    }

                    scope.message = null;
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
                }

                scope.save = function(){
                    scope.message = null;
                    waitingScreen.show();
                    if(this.isValid == false){
                        this.checkAddress();
                        return;
                    }

                    scope.request.save()
                        .then(function(data){//success save on backend
                            $rootScope.$broadcast('propertyApprovalSaved');
                        }, function(error){
                                throw typeof error == "object" && 'message' in error
                                                ? error.message
                                                : error;
                        })
                        .catch(function(e){
                            scope.message = typeof e == 'string'? e: e.message;
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        })
                    ;
                }

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

                    var marker = new google.maps.Marker({
                        position: centerLatLng
                    });

                    markers.push(marker);

                    marker.setMap(map);

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

                    var searchBox = new google.maps.places.SearchBox(input);

                    google.maps.event.addListener(searchBox, 'places_changed', function() {
                        var places = searchBox.getPlaces();

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
                            });
                            var image = {
                                url: place.icon,
                                size: new google.maps.Size(71, 71),
                                origin: new google.maps.Point(0, 0),
                                anchor: new google.maps.Point(17, 34),
                                scaledSize: new google.maps.Size(25, 25)
                            };

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

                        map.fitBounds(bounds);
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