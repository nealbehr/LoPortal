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
            })
            .when('/recover/:id/:signature', {
                templateUrl: '/partials/recovery',
                controller:  'recoverCtrl',
                access: {
                    isFree: true
                }
            })
        ;
    }]);

    authorize.controller('recoverCtrl', ['$http', 'waitingScreen', '$routeParams', '$scope', 'renderMessage', function($http, waitingScreen, $routeParams, $scope, renderMessage){
        waitingScreen.show();
        $scope.password;
        $http.put('/authorize/confirm/password/' +  $routeParams.id, {signature: $routeParams.signature})
            .success(function(data){
                $scope.password = data.password;
            })
            .error(function(data, code){
                var message = 'message' in data? data.message: "We have some problems.";
                renderMessage(message, "danger", angular.element('#message'), $scope);
            })
            .finally(function(){
                waitingScreen.hide();
            });
    }]);

    authorize.controller(
        'authorizeCtrl',
        ['$scope', '$http', 'redirect', '$cookieStore', 'TOKEN_KEY', 'HTTP_CODES', '$compile', 'waitingScreen',
            function($scope, $http, redirect, $cookieStore, TOKEN_KEY, HTTP_CODES, $compile, waitingScreen) {

        if($cookieStore.get(TOKEN_KEY)){
            redirect('/');
        }

                $scope.first_time = '0';

        $scope.user = {
            email     : null,
            password  : null,
            first_time: '0'
        };
        $scope.errorMessage          = null;
        $scope.emailForResetPassword = null;

        $scope.isValidEmail = function(form) {
            if(!form.email){
                return;
            }

            return (form.$submitted || form.email.$touched) && (form.email.$error.email || form.email.$error.required);
        };

        $scope.isValidPassword = function(form) {
            if(!form.password){
                return;
            }

            return (form.$submitted || form.password.$touched) && (form.password.$error.required);
        };

        $scope.complete = function($event) {
            angular.element($event.target).autocomplete({
                source: function( request, response ) {
                    $http.get('/authorize/autocomplete/' + request.term)
                        .success(function(data){
                            response(data);
                        })
                    ;
                },
                minLength: 3,
                select: function(event, ui){
                    $scope.user.email = ui.item.value;
                }
            });
        };

        $scope.signin = function(e) {
            e.preventDefault();

            this.errorMessage = null;

            waitingScreen.show();

            $http.post('/authorize/signin', this.user).success(function(data) {
                $cookieStore.put(TOKEN_KEY, data);
                redirect('/');
            }).error(function(error, status) {
                // Confirm the introduction
                if ($scope.user.first_time == '0' && HTTP_CODES.ACCEPTED == status) {
                    angular.element('#modal-introduction').modal('show');
                }
                // Show error
                else {
                    $scope.errorMessage = error.message;
                }
            }).finally(function() {
                waitingScreen.hide();
            });
        };

        $scope.resetPassword = function(form){
            waitingScreen.show();
            $('#resetPass').modal('hide');
            $http.post('/authorize/reset/' + this.emailForResetPassword, {})
                .success(function(data){
                    $scope.renderMessage(data, "success");
                })
                .error(function(data){
                    $scope.renderMessage(data.message, "danger");
                })
                .finally(function(){
                    waitingScreen.hide();
                    // 1) hide gray screen
                });
        };

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