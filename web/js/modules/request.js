(function(settings){
    "use strict";
    settings = settings || {};

    var request = angular.module('requestModule', ['helperService'/*, 'ngImgCrop'*/]);

    request.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/flyer/new', {
                templateUrl: '/partials/request.flyer.new',
                controller:  'requestCtrl',
                resolve: request.resolve()
            })
            .when('/request/success',{
                templateUrl: '/partials/request.success',
                controller:  'requestSuccessCtrl',
                resolve: request.resolve()
            })
            .when('/request/approval', {
                templateUrl: '/partials/request.property.approval',
                controller:  'requestPropertyApprovalCtrl',
                resolve: request.resolve()
            })
        ;
    }]);

    request.resolve = function(){
        return {
            data: ["$q", "$http", function($q, $http){
                var deferred = $q.defer();

                $http.get('/user/me')
                    .success(function(data){
                        deferred.resolve(data)
                    })
                    .error(function(data){
                        //actually you'd want deffered.reject(data) here
                        //but to show what would happen on success..
                        deferred.reject(data);
                    })
                    .finally(function(){

                    })
                ;

                return deferred.promise;
            }]
        }
    }

    request.controller('requestPropertyApprovalCtrl', ['redirect', '$scope', 'loadGoogleMapsApi', '$http', 'waitingScreen', '$q', 'getInfoFromGeocoder', function(redirect, $scope, loadGoogleMapsApi, $http, waitingScreen, $q, getInfoFromGeocoder){
        $scope.goto = function(e){
            e.preventDefault();

            redirect('/');
        }

        $scope.address = '';

        var input = document.getElementById('searchPlace');

        function initialize() {
        var markers = [];
            var mapProp = {
                center:new google.maps.LatLng(51.508742, -0.120850),
                zoom:10,
                disableDefaultUI:true,
                mapTypeId:google.maps.MapTypeId.ROADMAP
            };
            var map=new google.maps.Map(document.getElementById("location"),mapProp);

            google.maps.event.addListener(map, 'click', function(event) {
                waitingScreen.show();
                getInfoFromGeocoder({location: event.latLng})
                                .then(function(address){
                                    if(address.length > 0){
                                        $scope.address = address[0].formatted_address;
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
                console.log(place)
                var image = {
                    url: place.url,
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

    request.controller('requestCtrl', ['$scope', 'redirect', 'data', '$http', '$q', '$timeout', 'loadGoogleMapsApi', 'getInfoFromGeocoder', function($scope, redirect, data, $http, $q, $timeout, loadGoogleMapsApi, getInfoFromGeocoder){
        loadGoogleMapsApi();
        var data, file;
        $scope.user = data;
        $scope.request = {
            property: {
                first_rex_id: null,
                address: null,
                mls_number: null,
                image: null
            },
            realtor: {
                first_name: null,
                last_name: null,
                bre_number: null,
                estate_agency: null,
                phone: null,
                email: null,
                image: null
            }
        }

        $scope.goToDashboard = function(e){
            e.preventDefault();

            redirect('/');
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
            getInfoFromGeocoder({address:this.request.property.address})
                .then(function(data){
                    $scope.request.address = $scope.parseGoogleAddressComponents(data[0].address_components);
                    $scope.request.property.image = $scope.prepareImage($scope.cropperPropertyImage.container, 2000, 649, 3000, 974);
                    $scope.request.realtor.image  = $scope.prepareImage($scope.cropperRealtorImage.container, 600, 300, 800, 400);

//                    $scope.propertyImage = $scope.request.property.image;

                    return $http.post('/request/', $scope.request);
                })
                .then(function(data){
                    //success save on backend
                    redirect('/request/success');
                })
                .catch(function(e){
                    alert("We have some problems. Please try later.");
                })
            ;
        }

        $scope.parseGoogleAddressComponents = function(data){
            var result = {
                address: '',
                city:    null,
                state:   null,
                zip:     null
            }

            for(var i in data){
                if($.inArray("street_number", data[i].types) != -1){
                    result.address = data[i].long_name + ' ' + result.address;
                    continue;
                }

                if($.inArray("route", data[i].types) != -1){
                    result.address += ' ' + data[i].long_name;
                    continue;
                }

                if($.inArray("locality", data[i].types) != -1){
                    result.city = data[i].long_name;
                    continue;
                }

                if($.inArray("administrative_area_level_1", data[i].types) != -1){
                    result.state = data[i].short_name;
                    continue;
                }

                if($.inArray("postal_code", data[i].types) != -1){
                    result.zip = data[i].long_name;
                    continue;
                }
            }

            return result;
        }
    }]);

    request.controller('requestSuccessCtrl', ['redirect', '$scope', 'data', function(redirect, $scope, data){
        $scope.user = data;
        $scope.goto = function(e, path){
            e.preventDefault();

            redirect(path);
        }
    }]);

})(settings);