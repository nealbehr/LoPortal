(function(settings){
    "use strict";
    settings = settings || {};

    var authorize = angular.module('authModule', []);

    authorize.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/login', {
                templateUrl: '/partials/login',
                controller:  'authorizeCtrl',
                access: {
                    isFree: true
                }
            });
    }]);

    authorize.controller('authorizeCtrl', ['$scope', '$http', 'redirect', '$cookieStore', 'TOKEN_KEY', '$compile', 'waitingScreen', function($scope, $http, redirect, $cookieStore, TOKEN_KEY, $compile, waitingScreen){
        if($cookieStore.get(TOKEN_KEY)){
            redirect('/');
        }

        $scope.user = {email: "", password: ""};
        $scope.errorMessage          = null;
        $scope.emailForResetPassword = null;

        $scope.isValidEmail = function(form){
            if(!form.email){
                return;
            }

            return (form.$submitted || form.email.$touched) && (form.email.$error.email || form.email.$error.required);
        }

        $scope.isValidPassword = function(form){
            if(!form.password){
                return;
            }

            return (form.$submitted || form.password.$touched) && (form.password.$error.required);
        }

        $scope.complete = function($event){
            angular.element($event.target).autocomplete({
                source: function( request, response ) {
                    $http.get('/authorize/autocomplete/' + request.term)
                        .success(function(data){
                            console.log(data)
                            response(data);
                        })
                    ;
                },
                minLength: 3
            });
        }

        $scope.signin = function(){
            this.errorMessage = null;
            waitingScreen.show();
            $http.post('/authorize/signin', this.user)
                .success(function(data){
                    $cookieStore.put(TOKEN_KEY, data);
                    redirect('/');
                })
                .error(function(data){
                    $scope.errorMessage = data.message;
                })
                .finally(function(){
                    waitingScreen.hide();
                });
            ;
        }

        $scope.resetPassword = function(form){
            $http.post('/authorize/reset/' + this.emailForResetPassword, {})
                .success(function(data){
                    $scope.renderMessage(data, "success");
                })
                .error(function(data){
                    $scope.renderMessage(data.message, "danger");
                })
                .finally(function(){
                    $('#resetPass').modal('hide')
                    // 1) hide gray screen
                });
            ;
        }

        $scope.renderMessage = function(message, type){
            var angularDomEl = angular.element('<div lo-message></div>')
                .attr({
                    'lo-body': message,
                    'lo-type': type
                });

            angular.element("form[name='formLogin']").prepend($compile(angularDomEl)($scope));
        }
    }]);
})(settings);