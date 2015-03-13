(function(settings){
    "use strict";
    settings = settings || {};

    var app = angular.module('loApp', ['ngRoute', 'helperService', 'dashboardModule', 'authModule']);

    app.constant('HTTP_CODES', {FORBIDDEN: 403});

    app.config(['$interpolateProvider', '$httpProvider', 'HTTP_CODES', function($interpolateProvider, $httpProvider, HTTP_CODES) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
        $interpolateProvider.startSymbol('[[')
                            .endSymbol(']]');

        $httpProvider.interceptors.push(function($q, $location) {
            return {
                'responseError': function(rejection) {
                    console.log(rejection);
                    if (rejection.status === HTTP_CODES.FORBIDDEN) {
                        $location.path('/login');
                    }

                    return $q.reject(rejection);
                }
            };
        });
    }])
    .run(['$rootScope', function($rootScope){
            $rootScope.debug = settings.debug;
    }])
    ;

    app.config(['$routeProvider',
        function($routeProvider) {
            $routeProvider.
                otherwise({
                    redirectTo: '/'
                });
        }]
    );
})(settings);
