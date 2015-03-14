(function(settings){
    "use strict";
    settings = settings || {};

    var authorize = angular.module('authModule', []);

    authorize.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/login', {
                templateUrl: '/partials/login',
                controller:  'authorizeCtrl'
            });
    }]);

    authorize.controller('authorizeCtrl', ['$scope', '$http', function($scope, $http){
        $scope.user = {email: "", password: ""};

        $scope.isValidEmail = function(formLogin){
            return (formLogin.$submitted || formLogin.email.$touched) && (formLogin.email.$error.email || formLogin.email.$error.required);
        }

        $scope.isValidPassword = function(formLogin){
            return (formLogin.$submitted || formLogin.password.$touched) && (formLogin.password.$error.required);
        }

        $scope.complete = function($event){
            angular.element($event.target).autocomplete({
                source: function( request, response ) {
                    $http.get('/authorize/autocomplete/' + request.term)
                        .success(function(data){
                            response(data);
                        })
                    ;
                },
                minLength: 3
            });
        }

        $scope.submit = function(){
            $http.post('/signin', this.user)
                .then(function(data){

                })
                .finally(function(){

                });
            ;
        }
    }]);
})(settings);