(function(settings){
    "use strict";
    settings = settings || {};

    var admin = angular.module('adminModule', []);

    admin.config(['$routeProvider', function($routeProvider) {
        $routeProvider.
            when('/admin/user/new', {
                templateUrl: '/partials/admin.panel.user',
                controller:  'adminUserNewCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/admin/user/:id/edit', {
                templateUrl: '/partials/admin.panel.user',
                controller:  'adminUserEditCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/admin', {
                templateUrl: '/partials/admin',
                controller:  'adminCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/admin/queue', {
                templateUrl: '/partials/admin.queue',
                controller:  'adminQueueCtrl',
                access: {
                    isFree: false
                }
            });
    }]);

    admin.controller('adminQueueCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createUser', function($scope, $http, redirect, $compile, waitingScreen, createUser){
        $('[data-toggle="tooltip"]').tooltip();
    }]);

    admin.controller('adminUserNewCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createUser', function($scope, $http, redirect, $compile, waitingScreen, createUser){
        $scope.officer = createUser();

    }]);

    admin.controller('adminUserEditCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createUser', '$routeParams', function($scope, $http, redirect, $compile, waitingScreen, createUser, $routeParams){
        createUser().get($routeParams.id)
            .then(function(user){
                $scope.officer = user;
        });
        $scope.redirectUrl = '/admin';
    }]);

    admin.controller('adminCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', function($scope, $http, redirect, $compile, waitingScreen){

    }]);

    admin.directive('loAdminNavBar', ['$location', function($location){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.nav.bar',
            link: function(scope, element, attrs, controllers){
                var tab = function(params){
                    params = params || {}
                    this.path;
                    this.title;

                    this.isActive = function(){
                        return this.location.path() == this.path;
                    }

                    for(var i in params){
                        this[i] = params[i];
                    }
                }

                tab.prototype.location = $location;

                scope.tabs = [
                    new tab({path: '/admin', title: "User Management"}),
                    new tab({path: '/admin/queue', title: "Request Management"})
                ]
            }
        }
    }]);

    admin.directive('loAdminUsers', ['$http', "getRoles", "$q", "$location", "tableHeadCol", "waitingScreen", "createUser", "renderMessage", function($http, getRoles, $q, $location, tableHeadCol, waitingScreen, createUser, renderMessage){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.panel.users',
            link: function(scope, element, attrs, controllers){
                var locationParams = $location.search();

                scope.pagination = {};
                scope.users = [];
                scope.roles = {};
                scope.searchingString;
                scope.sortKey;
                scope.directionKey;
                scope.isLoaded = false;
                scope.searchKey;

                scope.sortByKey = function(param){
                    if(!param.isSortable){
                        return false;
                    }

                    var newLocationParams = {}

                    if(locationParams[this.sortKey] == param.key){
                        newLocationParams[this.directionKey] = locationParams[this.directionKey] != undefined && locationParams[this.directionKey] == "desc"? "asc": "desc";
                    }

                    newLocationParams[this.sortKey] = param.key;

                    $location.search(newLocationParams);
                }

                scope.search = function(){
                    if(this.searchingString == ""){
                        delete locationParams[this.searchKey];
                    }else{
                        locationParams[this.searchKey] = this.searchingString;
                    }

                    $location.search(locationParams);
                }

                scope.getUrl = function(isNext){
                    return '/#' + $location.path() + '?' + this.getParams(isNext? scope.pagination.next: scope.pagination.previous);
                }

                scope.getParams = function(page){
                    var params = angular.copy(locationParams);
                    if(page){
                        params.page = page;
                    }
                    return $.param(params);
                }

                scope.getUsers = function(){
                    var deferred = $q.defer();
                    $http.get('/admin/user', {
                        params: locationParams
                    }).success(function(data){
                        return deferred.resolve(data);
                    })

                    return deferred.promise;
                }

                getRoles()
                    .then(function(data){
                        scope.roles = data;
                    })
                    .then(function(){
                        return scope.getUsers();
                    })
                    .then(function(data){
                        scope.pagination = data.pagination;
                        scope.users = [];
                        for(var i in data.users){
                            scope.users.push(createUser().fill(data.users[i]));
                        }
                        scope.searchingString = locationParams[data.keySearch];
                        scope.searchKey = data.keySearch;
                        scope.sortKey = data.keySort;
                        scope.directionKey = data.keyDirection;

                        tableHeadCol.prototype.directionKey     = scope.directionKey;
                        tableHeadCol.prototype.sortKey          = scope.sortKey;
                        tableHeadCol.prototype.defaultDirection = data.defDirection;
                        tableHeadCol.prototype.defaultSortKey   = data.defField;

                        scope.headParams = [
                            new tableHeadCol({key: "id", title: "id"}),
                            new tableHeadCol({key: "first_name", title: "First<br>Name"}),
                            new tableHeadCol({key: "last_name", title: "Last<br>Name"}),
                            new tableHeadCol({key: "email", title: "Email", isSortable: false}),
                            new tableHeadCol({key: "role", title: "Role", isSortable: false}),
                            new tableHeadCol({key: "title", title: "Title", isSortable: false}),
                            new tableHeadCol({key: "phone", title: "Primary<br>Phone", isSortable: false}),
                            new tableHeadCol({key: "mobile", title: "Mobile<br>Phone", isSortable: false}),
                            new tableHeadCol({key: "created_at", title: "Created", isSortable: false}),
                            new tableHeadCol({key: "action", title: "Actions", isSortable: false}),
                        ];
                    })
                    .finally(function(){
                        scope.isLoaded = true;
                    })
                ;

                scope.container = angular.element("#userMessage");
                scope.delete = function(e, key, user){
                    e.preventDefault();
                    if(!confirm("Are you sure?")){
                        return false;
                    }

                    waitingScreen.show();


                    user.delete().then(function(){
                            renderMessage("User was deleted.", "success", scope.container, scope);
                            scope.users.splice(key, 1);
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        })
                    ;
                }
            }
        }
    }]);

    admin.directive('loAdminRequests', ['$http', 'tableHeadCol', '$location', function($http, tableHeadCol, $location){
        return {
            restrict: 'EA',
            templateUrl: '/partials/admin.panel.requests',
            link: function(scope, element, attrs, controllers){
                scope.queue = []
                var locationParams = $location.search();

                $http.get('/admin/queue')
                    .success(function(data){
                        scope.queue = data.queue;
                        console.log(data)

                        tableHeadCol.prototype.directionKey   = scope.directionKey;
                        tableHeadCol.prototype.sortKey        = scope.sortKey;
                        tableHeadCol.prototype.defaultDirection = data.defDirection;
                        tableHeadCol.prototype.defaultSortKey   = data.defField;

                        scope.headParams = [
                            new tableHeadCol({key: "id", title: "Request ID"}),
                            new tableHeadCol({key: "user_id", title: "User ID"}),
                            new tableHeadCol({key: "address", title: "Property Address"}),
                            new tableHeadCol({key: "mls_number", title: "MLS<br>Number"}),
                            new tableHeadCol({key: "created_at", title: "Created", isSortable: false}),
                            new tableHeadCol({key: "request_type", title: "Type"}),
                            new tableHeadCol({key: "state", title: "Status"}),
                            new tableHeadCol({key: "action", title: "Actions", isSortable: false}),
                        ];
                    })
                ;
            }
        }
    }]);

    admin.factory('tableHeadCol', ['$sce', '$location', function($sce, $location){
        function headCol(params){
            params = params || {}
            this.key;
            this.title;
            this.isSortable = true;

            this.isSortedUp = function(){
                return this.isSortedDirection('desc');
            }

            this.isSortedDown = function(){
                return this.isSortedDirection('asc');
            }

            this.isSortedDirection = function(direction){
                if(!this.isCurrentlySorted()){
                    return false;
                }

                return (this.getLocationParams()[this.getDirectionKey()] || this.getDefaultDirection()).toLowerCase() == direction;
            }

            this.isCurrentlySorted = function(){
                return (this.getLocationParams()[this.getSortKey()] || this.getDefaultSortKey()) == this.key;
            }

            this.getLocationParams = function(){
                return this.location.search();
            }

            this.getDefaultDirection = function(){
                if(!("defaultDirection" in this)){
                    throw new Error('Property defaultDirection have not set.');
                }

                return this.defaultDirection;
            }

            this.getDefaultSortKey = function(){
                if(!("defaultSortKey" in this)){
                    throw new Error('Property defaultSortKey have not set.');
                }

                return this.defaultSortKey;
            }

            this.getDirectionKey = function(){
                if(!("directionKey" in this)){
                    throw new Error('Property directionKey have not set.');
                }

                return this.directionKey;
            }

            this.getSortKey = function(){
                if(!("sortKey" in this)){
                    throw new Error('Property sortKey have not set.');
                }

                return this.sortKey;
            }

            //Init
            for(var i in params){
                this[i] = params[i];
            }

            this.title = this.sce.trustAsHtml(this.title);
        }

        headCol.prototype.location = $location;
        headCol.prototype.sce      = $sce;

        return headCol;
    }]);

    admin.filter('adminUserRole', function(){
        return function(input, roles){
            /** input can be array or object */
            var role;
            for(var i in input){
                role = input[i];
            }

            for(i in roles){
                if(roles[i] == role){
                    return i;
                }
            }
        }
    });

    admin.filter('requestType', ['$http', function($http){
        var filterFn = function initFilter(){
            return "loading";
        }

        $http.get('/settings/request/type')
            .success(function(result) {
                filterFn = function newFilter(str){
                    return result[str];
                }
            })
        ;

        return function tempFilter(str) {
            return filterFn(str);
        };
    }]);

    admin.filter('requestState', ['$http', function($http){
        var filterFn = function initFilter(){
            return "loading";
        }

        $http.get('/settings/request/state')
            .success(function(result) {
                filterFn = function newFilter(str){
                    return result[str];
                }
            })
        ;

        return function tempFilter(str) {
            return filterFn(str);
        };
    }]);
})(settings);