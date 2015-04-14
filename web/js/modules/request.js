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
        loadGoogleMapsApi(initialize);
    }]);

    request.controller('requestCtrl', ['$scope', 'redirect', '$http', '$q', '$timeout', 'loadGoogleMapsApi', 'getInfoFromGeocoder', 'waitingScreen', 'parseGoogleAddressComponents', function($scope, redirect, $http, $q, $timeout, loadGoogleMapsApi, getInfoFromGeocoder, waitingScreen, parseGoogleAddressComponents){
        $('[data-toggle="tooltip"]').tooltip();
        loadGoogleMapsApi();
        $scope.request = {
            property: {
                first_rex_id: null,
                address: null,
                mls_number: null,
                listing_price: null,
                photo: null
            },
            realtor: {
                first_name: null,
                last_name: null,
                bre_number: null,
                estate_agency: null,
                phone: null,
                email: null,
                photo: null
            }
        }

        $scope.fileSelect = function(evt, variable, imageInfo) {
            var file = evt.currentTarget.files[0];

            var reader = new FileReader();
            reader.onload = function (evt) {
                $scope.$apply(function(){
                    $scope[variable] = evt.target.result;
                });

                imageInfo.container.cropper('destroy');
                imageInfo.container.cropper(imageInfo.options);
            };

            reader.readAsDataURL(file);
        };

        $scope.propertyImage = '';
        $scope.realtorImage  = '';

        $scope.cropperPropertyImage = {container: $(".property-photo > img"), options: {aspectRatio: 3 / 2}};
        $scope.cropperRealtorImage  = {container: $(".realtor-photo > img"), options: {aspectRatio: 4 / 3, minContainerWidth: 100}};

        angular.element(document.querySelector('#propertyImage')).on('change',function(e){
            $scope.fileSelect(e, 'propertyImage', $scope.cropperPropertyImage);
        });

        angular.element(document.querySelector('#realtorImage')).on('change',function(e){
            $scope.fileSelect(e, 'realtorImage', $scope.cropperRealtorImage);
        });

        $scope.prepareImage = function(image, heightMax, heightMin, widthMax, widthMin){
            var info = image.cropper("getCropBoxData");

            return image.cropper("getCroppedCanvas",
                                 { "width": this.getBetween(info.width, widthMax, widthMin),
                                   "height": this.getBetween(info.height, heightMax, heightMin)
                                 })
                        .toDataURL();
        }

        $scope.getBetween = function(number, max, min){
            if(number > max){
                return max;
            }

            return number < min? min: number;
        }

        $scope.save = function(){
            waitingScreen.show();
            getInfoFromGeocoder({address:this.request.property.address})
                .then(function(data){
                    $scope.request.address = parseGoogleAddressComponents(data[0].address_components);
                    $scope.request.property.address = data[0].formatted_address;
                    $scope.request.property.photo = $scope.prepareImage($scope.cropperPropertyImage.container, 2000, 649, 3000, 974);
                    $scope.request.realtor.photo  = $scope.prepareImage($scope.cropperRealtorImage.container, 600, 300, 800, 400);

//                    $scope.propertyImage = $scope.request.property.image;

                    return $http.post('/request/', $scope.request);
                })
                .then(function(data){
                    //success save on backend
                    redirect('/request/success/flyer');
                })
                .catch(function(e){
                    alert("We have some problems. Please try later.");
                })
                .finally(function(){
                    waitingScreen.hide();
                })
            ;
        }
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