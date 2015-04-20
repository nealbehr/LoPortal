(function(settings){
    "use strict";
    settings = settings || {};

    var app = angular.module('loApp', ['ngRoute', 'helperService', 'dashboardModule', 'authModule', 'ngCookies', 'requestModule', 'userModule', 'userProfileModule', 'adminModule', 'ngDialog', 'requestFlyerModule', 'approvalModule']);

    app.constant('HTTP_CODES', {FORBIDDEN: 403});
    app.constant('TOKEN_KEY', 'access_token');

    app.config(['$interpolateProvider', '$httpProvider', '$cookiesProvider', 'HTTP_CODES', 'TOKEN_KEY', function($interpolateProvider, $httpProvider, $cookiesProvider, HTTP_CODES, TOKEN_KEY) {
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';
        $httpProvider.defaults.headers.put['Content-Type'] = 'application/x-www-form-urlencoded';
        $httpProvider.defaults.headers.patch['Content-Type'] = 'application/x-www-form-urlencoded';
        $interpolateProvider.startSymbol('[[')
                            .endSymbol(']]');

        var date = new Date();
        date.setDate((new Date()).getDate() + (settings.tokenExpire || 5));

        $cookiesProvider.expires = date;

        $httpProvider.interceptors.push(["$q", "$location", "$cookieStore", "redirect", function($q, $location, $cookieStore, redirect) {
            return {
                request: function(config) {
                    if($cookieStore.get(TOKEN_KEY)){
                        config.headers['x-session-token'] = $cookieStore.get(TOKEN_KEY);
                    }

                    if(settings.requestTimeout && settings.debug == false){
                        config.timeout = settings.requestTimeout;
                    }

                    if('data' in config){
                        config.data = $.param(config.data);
                    }

                    return config;
                },
                'responseError': function(rejection) {
                    console.log(rejection);
                    if (rejection.status === HTTP_CODES.FORBIDDEN) {
                        $cookieStore.remove(TOKEN_KEY);
                        $cookieStore.remove("PHPSESSID");
                        redirect('/login', $location.url());
                    }

                    return $q.reject(rejection);
                }
            };
        }]);
    }])
    .run(['$rootScope', 'TOKEN_KEY', '$cookies', 'redirect', '$location', function($rootScope, TOKEN_KEY, $cookies, redirect, $location){
            $rootScope.debug = settings.debug;

            $rootScope.$on('$routeChangeStart', function(e, next, curr){
                if ('access' in next && !next.access.isFree && $cookies[TOKEN_KEY] == undefined) {
                    redirect('/login', $location.url());
                }
            });
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
