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
            });
    }]);

    admin.controller('adminUserNewCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createUser', function($scope, $http, redirect, $compile, waitingScreen, createUser){
        $scope.officer = createUser();

        $scope.messageContainer = angular.element("#adminMessage");
    }]);

    admin.controller('adminUserEditCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createUser', '$routeParams', function($scope, $http, redirect, $compile, waitingScreen, createUser, $routeParams){
        createUser().get($routeParams.id)
            .then(function(user){
                $scope.officer = user;
        });

        $scope.messageContainer = angular.element("#adminMessage");
    }]);

    admin.controller('adminCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', function($scope, $http, redirect, $compile, waitingScreen){
        $scope.messageContainer = angular.element("#adminMessage");
    }]);

    admin.directive('loAdminNavBar', [function(){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.nav.bar',
            link: function(scope, element, attrs, controllers){

            }
        }
    }]);

    admin.directive('loAdminUsers', ['$http', "getRoles", "$q", "$location", "$sce", "waitingScreen", "createUser", "renderMessage", function($http, getRoles, $q, $location, $sce, waitingScreen, createUser, renderMessage){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.panel.users',
            link: function(scope, element, attrs, controllers){
                var locationParams = $location.search();
                scope.headParams = [
                    {
                        key: "id",
                        title:  $sce.trustAsHtml("id"),
                        isSortable: true
                    },
                    {
                        key: "first_name",
                        title: $sce.trustAsHtml('First<br>Name'),
                        isSortable: true
                    },
                    {
                        key: "last_name",
                        title:  $sce.trustAsHtml("Last<br>Name"),
                        isSortable: true
                    },
                    {
                        key: "email",
                        title: $sce.trustAsHtml("Email"),
                        isSortable: false
                    },
                    {
                        key: "role",
                        title: $sce.trustAsHtml("Role"),
                        isSortable: false
                    },
                    {
                        key: "title",
                        title: $sce.trustAsHtml("Title"),
                        isSortable: false
                    },
                    {
                        key: "phone",
                        title: $sce.trustAsHtml("Primary<br>Phone"),
                        isSortable: false
                    },
                    {
                        key: "mobile",
                        title: $sce.trustAsHtml("Mobile<br>Phone"),
                        isSortable: false
                    },
                    {
                        key: "created_at",
                        title: $sce.trustAsHtml("Created"),
                        isSortable: false
                    },
                    {
                        key: "action",
                        title: $sce.trustAsHtml("Actions"),
                        isSortable: false
                    }
                ]

                scope.pagination = {};
                scope.users = [];
                scope.roles = {};
                scope.searchingString;
                scope.sortKey;
                scope.directionKey;
                scope.isLoaded = false;
                scope.searchKey;

                scope.isSortedUp = function(key){
                    return this.isSortedDirection(key, 'asc');
                }

                scope.isSortedDown = function(key){
                    return this.isSortedDirection(key, 'desc');
                }

                scope.isSortedDirection = function(key, direction){
                    if(!this.isSorted(key)){
                        return false;
                    }

                    return ($location[this.directionKey] || "asc").toLowerCase() == direction;
                }

                scope.isSorted = function(key){
                    return ($location[this.sortKey] || "id") == key;
                }

                scope.sortByKey = function(param){
                    if(!param.isSortable){
                        return false;
                    }

                    var newLocationParams = {}

                    if(locationParams[this.sortKey] == param.key){
                        newLocationParams[this.directionKey] = locationParams[this.directionKey] == "desc"? "asc": "desc";
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
    })
})(settings);