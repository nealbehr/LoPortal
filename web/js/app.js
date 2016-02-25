(function(settings){
    "use strict";
    settings = settings || {};

    var app = angular.module(
        'loApp',
        [
            'ngRoute',
            'helperService',
            'dashboardModule',
            'authModule',
            'ngCookies',
            'requestModule',
            'userModule',
            'userProfileModule',
            'adminModule',
            'lenderModule',
            'queueRealtorModule',
            'realtyCompanyModule',
            'salesDirectorModule',
            'ngDialog',
            'requestFlyerModule',
            'approvalModule',
            'googleAutoCompleteModule',
            'resourcesModule',
            'collateralModule',
            'calculatorModule'
        ]
    );

    app.constant('HTTP_CODES', {
        ACCEPTED    : 202,
        UNAUTHORIZED: 401,
        FORBIDDEN   : 403,
        BAD_REQUEST : 400
    });
    app.constant('TOKEN_KEY', 'access_token');
    app.constant('USA_STATES', [
            { code: 'AL', name: 'Alabama'},
            { code: 'AK', name: 'Alaska'},
            { code: 'AZ', name: 'Arizona'},
            { code: 'AR', name: 'Arkansas'},
            { code: 'CA', name: 'California'},
            { code: 'CO', name: 'Colorado'},
            { code: 'CT', name: 'Connecticut'},
            { code: 'DE', name: 'Delaware'},
            { code: 'FL', name: 'Florida'},
            { code: 'GA', name: 'Georgia'},
            { code: 'HI', name: 'Hawaii'},
            { code: 'ID', name: 'Idaho'},
            { code: 'IL', name: 'Illinois'},
            { code: 'IN', name: 'Indiana'},
            { code: 'IA', name: 'Iowa'},
            { code: 'KS', name: 'Kansas'},
            { code: 'KY', name: 'Kentucky'},
            { code: 'LA', name: 'Louisiana'},
            { code: 'ME', name: 'Maine'},
            { code: 'MD', name: 'Maryland'},
            { code: 'MA', name: 'Massachusetts'},
            { code: 'MI', name: 'Michigan'},
            { code: 'MN', name: 'Minnesota'},
            { code: 'MS', name: 'Mississippi'},
            { code: 'MO', name: 'Missouri'},
            { code: 'MT', name: 'Montana'},
            { code: 'NE', name: 'Nebraska'},
            { code: 'NV', name: 'Nevada'},
            { code: 'NH', name: 'New Hampshire'},
            { code: 'NJ', name: 'New Jersey'},
            { code: 'NM', name: 'New Mexico'},
            { code: 'NY', name: 'New York'},
            { code: 'NC', name: 'North Carolina'},
            { code: 'ND', name: 'North Dakota'},
            { code: 'OH', name: 'Ohio'},
            { code: 'OK', name: 'Oklahoma'},
            { code: 'OR', name: 'Oregon'},
            { code: 'PA', name: 'Pennsylvania'},
            { code: 'RI', name: 'Rhode Island'},
            { code: 'SC', name: 'South Carolina'},
            { code: 'SD', name: 'South Dakota'},
            { code: 'TN', name: 'Tennessee'},
            { code: 'TX', name: 'Texas'},
            { code: 'UT', name: 'Utah'},
            { code: 'VT', name: 'Vermont'},
            { code: 'VA', name: 'Virginia'},
            { code: 'WA', name: 'Washington'},
            { code: 'WV', name: 'West Virginia'},
            { code: 'WI', name: 'Wisconsin'},
            { code: 'WY', name: 'Wyoming'}
        ]
    );

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
    }]).run(['$rootScope', 'TOKEN_KEY', '$cookies', 'redirect', '$location', 'userService',
            function($rootScope, TOKEN_KEY, $cookies, redirect, $location, userService
        ) {
            $rootScope.debug       = settings.debug;
            $rootScope.history     = [];
            $rootScope.currentUser = null;
            $rootScope.historyGet  = function(){
                return this.history.length > 1 ? this.history.splice(-2)[0] : "/";
            };

            // Tracking analytics
            $rootScope.$on('$locationChangeSuccess', function(e, next, current) {
                // Google
                if (typeof window.ga === 'function') {
                    ga('send', 'pageview', $location.url());
                }

                // Mixpanel
                if ($rootScope.currentUser) {
                    if ('/calculators' === $location.url()) {
                        mixpanel.identify($rootScope.currentUser.id);
                        mixpanel.track('Calculator page is viewed');
                    }
                }
            });

            $rootScope.$on('$routeChangeStart', function(e, next, curr){
                $rootScope.history.push($location.$$path);
                if($rootScope.history.length > 3){
                    $rootScope.history = $rootScope.history.slice(-2);
                }

                if ('access' in next && !next.access.isFree && $cookies[TOKEN_KEY] == undefined) {
                    $rootScope.currentUser = null;
                    redirect('/login', $location.url());
                }

                // Set current user
                if ($cookies[TOKEN_KEY] !== undefined && !$rootScope.currentUser) {
                    userService.get().then(function(user) {
                        $rootScope.currentUser = user;
                    });
                }
            });
        }
    ]);

    app.config(['$routeProvider',
        function($routeProvider) {
            $routeProvider.
                otherwise({
                    redirectTo: '/'
                });
        }]
    );

})(settings);
