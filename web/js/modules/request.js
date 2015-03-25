(function(settings){
    "use strict";
    settings = settings || {};

    var request = angular.module('requestModule', ['helperService', 'ngImgCrop']);

    request.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/flyer/new', {
                templateUrl: '/partials/request.flyer.new',
                controller:  'requestCtrl',
                resolve: {
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
            });
    }]);

    request.controller('requestCtrl', ['$scope', 'redirect', 'data', '$http', '$q', function($scope, redirect, data, $http, $q){
        $scope.user = data;
        $scope.request = {
            property: {
                first_rex_id: null,
                address: "60 Edwards Court Burlingame",
                mls: "1",
                image: null
            },
            realtor: {
                full_name: "2",
                bre_number: "3",
                estate_agency: "4",
                phone: "5",
                email: "6",
                image: null
            }
        }

        $scope.fileSelect = function(evt, variable) {
            var file = evt.currentTarget.files[0];
            var reader = new FileReader();
            reader.onload = function (evt) {
                $scope.$apply(function($scope){
                    $scope[variable] = evt.target.result;
                });
            };

            reader.readAsDataURL(file);
        };
        $scope.propertyImage = '';
        $scope.realtorImage  = '';

        angular.element(document.querySelector('#propertyImage')).on('change',function(e){
            $scope.fileSelect(e, 'propertyImage');
        });
        angular.element(document.querySelector('#realtorImage')).on('change',function(e){
            $scope.fileSelect(e, 'realtorImage');
        });

        $scope.save = function(){
            this.codeAddress(this.request.property.address)
                .then(function(data){
                    $scope.request.address = data;
                    return $http.post('/request/', $scope.request);
                })
                .then(function(data){
                    //success save on backend
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
})(settings);