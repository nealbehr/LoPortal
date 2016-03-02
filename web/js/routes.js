/**
 * Created by Eugene Lysenko on 2/26/16.
 */
angular.module('loApp').config(['$routeProvider', function($routeProvider) {
    /**
     * Routes list
     */
    $routeProvider.
        // Authorize
        when('/login', {
            templateUrl: 'template/route/login.html',
            controller:  'authorizeCtrl',
            access: {
                isFree: true
            }
        })
        .when('/recover/:id/:signature', {
            templateUrl: 'template/route/recovery.html',
            controller:  'recoverCtrl',
            access: {
                isFree: true
            }
        })

        // Calculators
        .when('/calculators', {
            templateUrl: 'template/route/calculator.html',
            controller:  'CalculatorController',
            access: {
                isFree: false
            }
        })

        // Request
        .when('/flyer/new', {
            templateUrl: 'template/route/request-flyer-new.html',
            controller:  'RequestController',
            access: {
                isFree: false
            }
        })
        .when('/request/success/:type',{
            templateUrl: 'template/route/request-success.html',
            controller:  'RequestSuccessController',
            access: {
                isFree: false
            }
        })
        .when('/request/success/:type/:id',{
            templateUrl: 'template/route/request-success.html',
            controller:  'RequestSuccessController',
            access: {
                isFree: false
            }
        })
        .when('/', {
            templateUrl: 'template/route/request-property-approval.html',
            controller:  'RequestPropertyApprovalController',
            access: {
                isFree: false
            }
        })
        .when('/flyer/:id/edit', {
            templateUrl: 'template/route/request-flyer-new.html',
            controller:  'RequestFlyerEditController',
            access: {
                isFree: false
            }
        })
        .when('/flyer/from/approval/:id/edit', {
            templateUrl: 'template/route/request-flyer-new.html',
            controller:  'RequestFromApprovalController',
            access: {
                isFree: false
            }
        })

        // Dashboard
        .when('/dashboard/requests', {
            templateUrl: 'template/route/dashboard.html',
            controller:  'dashboardCtrl',
            access: {
                isFree: false
            },
            resolve: {
                data: ["$q", "$http", 'waitingScreen', function($q, $http, waitingScreen){
                    var deferred = $q.defer();
                    waitingScreen.show();
                    $http.get('/dashboard/')
                        .success(function(data){
                            deferred.resolve(data)
                        })
                        .error(function(data){
                            deferred.reject(data);
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        })
                    ;

                    return deferred.promise;
                }]
            }
        })
        .when('/dashboard/collateral', {
            templateUrl: 'template/route/dashboard-collateral.html',
            controller:  'dashboardCollateralCtrl',
            access: {
                isFree: false
            }
        })

        // Admin
        .when('/admin/user/new', {
            templateUrl: 'template/route/admin-panel-user.html',
            controller:  'adminUserNewCtrl',
            access: {
                isFree: false
            }
        })
        .when('/admin/user/:id/edit', {
            templateUrl: 'template/route/admin-panel-user.html',
            controller:  'AdminUserEditController',
            resolve: {
                officerData: ['$route', 'createUser', function($route, createUser) {
                    return createUser().get($route.current.params.id);
                }]
            },
            access: {
                isFree: false
            }
        })
        .when('/admin', {
            templateUrl: 'template/route/admin.html',
            controller:  'adminCtrl',
            access: {
                isFree: false
            }
        })
        .when('/admin/queue', {
            templateUrl: 'template/route/admin-queue.html',
            controller:  'adminQueueCtrl',
            access: {
                isFree: false
            }
        })
        .when('/admin/flyer/:id/edit', {
            templateUrl: 'template/route/admin-request-flyer-edit.html',
            controller:  'AdminRequestFlyerEditController',
            access: {
                isFree: false
            }
        })
        .when('/admin/approval/:id/edit', {
            templateUrl: 'template/route/admin-request-property-approval.html',
            controller:  'propertyApprovalEditCtrl',
            access: {
                isFree: false
            }
        })
        .when('/admin/realtors', {
            templateUrl: 'template/route/admin-realtors.html',
            controller:  'realtorsCtrl',
            access: {
                isFree: false
            }
        })

        // Сollateral
        .when('/admin/collateral', {
            templateUrl: 'template/route/admin-collateral-tab.html',
            controller : 'adminCollateralListCtrl',
            access     : {
                isFree: false
            }
        })
        .when('/admin/collateral/new', {
            templateUrl: 'template/route/admin-collateral.html',
            controller : 'adminCollateralEditCtrl',
            access     : {
                isFree: false
            }
        })
        .when('/admin/collateral/:id/edit', {
            templateUrl: 'template/route/admin-collateral.html',
            controller : 'adminCollateralEditCtrl',
            access     : {
                isFree: false
            }
        })

        // Lender
        .when('/admin/lender', {
            templateUrl: 'template/route/admin-lender.html',
            controller:  'AdminLendersController',
            access: {
                isFree: false
            }
        })
        .when('/admin/lender/new', {
            templateUrl: 'template/route/admin-panel-lender.html',
            controller:  'AdminNewLenderController',
            access: {
                isFree: false
            }
        })
        .when('/admin/lender/:id/edit', {
            templateUrl: 'template/route/admin-panel-lender.html',
            controller:  'AdminEditLenderController',
            access: {
                isFree: false
            }
        })

        // Realtor
        .when('/admin/realtor', {
            templateUrl: 'template/route/admin-realtor-tab.html',
            controller : 'queueRealtorCtrl',
            access     : { isFree: false }
        })
        .when('/admin/realtor/new', {
            templateUrl: 'template/route/admin-realtor.html',
            controller :  'queueRealtorCtrl',
            access     : { isFree: false }
        })
        .when('/admin/realtor/:id/edit', {
            templateUrl: 'template/route/admin-realtor.html',
            controller :  'queueRealtorEditCtrl',
            access     : { isFree: false }
        })

        // Realty
        .when('/admin/realty', {
            templateUrl: 'template/route/admin-panel-realty.html',
            controller:  'AdminCompaniesController',
            access: {
                isFree: false
            }
        })
        .when('/admin/realty/new', {
            templateUrl: 'template/route/admin-panel-realty-company.html',
            controller:  'AdminRealtyNewController',
            access: {
                isFree: false
            }
        })
        .when('/admin/realty/:id/edit', {
            templateUrl: 'template/route/admin-panel-realty-company.html',
            controller:  'AdminRealtyEditController',
            access: {
                isFree: false
            }
        })

        // Resources
        .when('/resources', {
            templateUrl: 'template/route/resources-list.html',
            controller:  'ResourcesController',
            access: {
                isFree: false
            }
        })

        // Sales director
        .when('/admin/salesdirector', {
            templateUrl: 'template/route/admin-sales-director-tab.html',
            controller : 'salesDirectorCtrl',
            access     : { isFree: false }
        })
        .when('/admin/salesdirector/new', {
            templateUrl: 'template/route/admin-sales-director.html',
            controller : 'salesDirectorCtrl',
            access     : { isFree: false }
        })
        .when('/admin/salesdirector/:id/edit', {
            templateUrl: 'template/route/admin-sales-director.html',
            controller : 'salesDirectorEditCtrl',
            access     : { isFree: false }
        })

        // User
        .when('/user/:id/edit', {
            templateUrl: 'template/route/profile.html',
            controller:  'userProfileCtrl',
            resolve: {
                officerData: ["$q", "$http", '$route', 'waitingScreen', 'userService', 'createProfileUser', function($q, $http, $route, waitingScreen, userService, createProfileUser) {
                    return userService.get()
                }]
            },
            access: {
                isFree: false
            }
        })
}]);
