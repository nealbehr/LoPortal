(function(settings){
    "use strict";
    settings = settings || {};

    var app = angular.module('loApp', ['ngRoute', 'helperService', 'dashboardModule', 'authModule', 'ngCookies']);

    app.constant('HTTP_CODES', {FORBIDDEN: 403});
    app.constant('TOKEN_KEY', 'access_token');

    app.config(['$interpolateProvider', '$httpProvider', '$cookiesProvider', 'HTTP_CODES', 'TOKEN_KEY', function($interpolateProvider, $httpProvider, $cookiesProvider, HTTP_CODES, TOKEN_KEY) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
        $interpolateProvider.startSymbol('[[')
                            .endSymbol(']]');

        var date = new Date();
        date.setDate((new Date()).getDate() + (settings.tokenExpire || 5));

        $cookiesProvider.expires = date;

        $httpProvider.interceptors.push(function($q, $location, $cookieStore, redirect) {
            return {
                request: function(config) {
                    if($cookieStore.get(TOKEN_KEY)){
                        config.headers['x-session-token'] = $cookieStore.get(TOKEN_KEY);
                    }

                    if(settings.requestTimeout && settings.debug == false){
                        config.timeout = settings.requestTimeout;
                    }

                    return config;
                },
                'responseError': function(rejection) {
                    console.log(rejection);
                    if (rejection.status === HTTP_CODES.FORBIDDEN) {
                        redirect('/login', $location.url());
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
