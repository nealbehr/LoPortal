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
            });
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

    request.controller('requestCtrl', ['$scope', 'redirect', 'data', '$http', '$q', '$timeout', function($scope, redirect, data, $http, $q, $timeout){
        var data, file;
        $scope.user = data;
        $scope.request = {
            property: {
                first_rex_id: null,
                address: "60 Edwards Court Burlingame",
                mls_number: "1",
                image: null
            },
            realtor: {
                first_name: "firstName",
                last_name: "LastName",
                bre_number: "4",
                estate_agency: "5",
                phone: "6",
                email: "s.samoilenko@gmail.com",
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
            this.codeAddress(this.request.property.address)
                .then(function(data){
                    $scope.request.address = data;
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

        $scope.codeAddress = function(address){
            var deferred = $q.defer();
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode( { 'address': address}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    deferred.resolve(
                        $scope.parseGoogleAddressComponents(results[0].address_components)
                    );
                } else {
                    alert("Geocode was not successful for the following reason: " + status);
                    deferred.reject(results);
                }
            });

            return deferred.promise;
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