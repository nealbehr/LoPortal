(function(settings){
    "use strict";
    settings = settings || {};

    var request = angular.module('requestModule', ['helperService']);

    request.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/flyer/new', {
                templateUrl: '/partials/request.flyer.new',
                controller:  'requestCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/request/success/:type',{
                templateUrl: '/partials/request.success',
                controller:  'requestSuccessCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/request/approval', {
                templateUrl: '/partials/request.property.approval',
                controller:  'requestPropertyApprovalCtrl',
                access: {
                    isFree: false
                }
            })
        ;
    }]);

    request.controller('requestPropertyApprovalCtrl', ['redirect', '$scope', 'loadGoogleMapsApi', '$http', 'waitingScreen', '$q', 'getInfoFromGeocoder', 'parseGoogleAddressComponents', function(redirect, $scope, loadGoogleMapsApi, $http, waitingScreen, $q, getInfoFromGeocoder, parseGoogleAddressComponents){
        $scope.message = null;

        $scope.request = {
            property: {
                address: ''
            },
            address: null
        };

        var input = document.getElementById('searchPlace');

        $scope.save = function(){
            $scope.message = null;
            waitingScreen.show();
            getInfoFromGeocoder({address: this.request.property.address})
                .then(function(data){
                    $scope.request.address = parseGoogleAddressComponents(data[0].address_components);
                    $scope.request.property.address = data[0].formatted_address;
                    return $http.post('/request/approval', $scope.request);
                })
                .then(function(data){
                    //success save on backend
                    redirect('/request/success/approval');
                }, function(error){
                    if('message' in error.data){
                        throw error.data.message;
                    }
                })
                .catch(function(e){
                    $scope.message = typeof e == 'string'? e: e.message;
                })
                .finally(function(){
                    waitingScreen.hide();
                })
            ;
        }

        function initialize() {
        var markers = [];
            var mapProp = {
                center:new google.maps.LatLng(37.7749295, -122.41941550000001),
                zoom:10,
                disableDefaultUI:true,
                mapTypeId:google.maps.MapTypeId.ROADMAP
            };
            var map=new google.maps.Map(document.getElementById("location"),mapProp);

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
                                        $scope.request.property.address = address[0].formatted_address;
                                    }
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
        loadGoogleMapsApi()
            .then(initialize);
    }]);

    request.controller('requestCtrl', ['$scope', 'redirect', '$http', '$q', '$timeout', 'getInfoFromGeocoder', 'waitingScreen', 'parseGoogleAddressComponents', "userService", "createRequestFlyer", function($scope, redirect, $http, $q, $timeout, getInfoFromGeocoder, waitingScreen, parseGoogleAddressComponents, userService, createRequestFlyer){
        $scope.titles = {
            button: "Submit",
            header: "Listing Flyer Request Form"
        }

        $scope.realtor = {};

        $scope.$on('requestFlyerSaved', function () {
            redirect('/request/success/flyer');
        });

        userService
            .get()
            .then(function(user){
                $scope.realtor = user;
            })
        ;

        $scope.request = createRequestFlyer()
    }]);

    request.controller('requestSuccessCtrl', ['redirect', '$scope', '$routeParams', function(redirect, $scope, $routeParams){
        $scope.request = getRequestByType($routeParams.type);

        function getRequestByType(type){
            return type == 'approval'
                        ? new requestBase('Request property approval', 'request/approval')
                        : new requestBase('Request Another Flyer', 'flyer/new')
            ;
        }
        function requestBase(title, url){
            this.title = title;
            this.url   = url;
        }
    }]);

})(settings);