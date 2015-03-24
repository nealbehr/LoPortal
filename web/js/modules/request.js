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
                name: "2",
                bre: "3",
                agency: "4",
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
                    return $scope.senRequestTo1Rex(data);
                })
                .then(function(id){
                    console.log(data);
                    $scope.request.property.first_rex_id = id;
                    return $http.post('/request/', $scope.request);
                })
                .then(function(data){
                    //success save on backend
                })
                .catch(function(e){
                    alert("We have some problems. Please try later.");
                })
            ;
            console.log('fffff');
        }

        $scope.senRequestTo1Rex = function(data){
//        var deferred = $q.defer();
//            $http.defaults.headers.common.Authorization = 'Basic YmVlcDpib29w'
            return $http.post('http://tools.1rex.com/inquiries.json', data, {
                headers: {
                    'Authorization': 'FirstREX Admin007'
                }
            })
//                .success(function(data){
//                deferred.resolve(data.id);
//                })
//            .error(function(data){
//                deferred.reject(data);
//            })
                ;
//        return deferred.promise;
        }

        $scope.codeAddress = function(address){
            //return $http.get('https://maps.googleapis.com/maps/api/geocode/json?address='+address+'&key=');

            var deferred = $q.defer();
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode( { 'address': address}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    console.log(results[0]);
                    deferred.resolve(results[0]);
                } else {
                    alert("Geocode was not successful for the following reason: " + status);
                    deferred.reject(results);
                }
            });

            return deferred.promise;
        }
    }]);
})(settings);