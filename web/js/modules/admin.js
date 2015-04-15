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

    }]);

    admin.controller('adminUserNewCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createUser', function($scope, $http, redirect, $compile, waitingScreen, createUser){
        $scope.officer = createUser();

    }]);

    admin.controller('adminUserEditCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createUser', '$routeParams', function($scope, $http, redirect, $compile, waitingScreen, createUser, $routeParams){
        createUser().get($routeParams.id)
            .then(function(user){
                $scope.officer = user;
        });
    }]);

    admin.controller('adminCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', function($scope, $http, redirect, $compile, waitingScreen){

    }]);

    admin.controller('adminDiscardCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', function($scope, $http, redirect, $compile, waitingScreen){
        $scope.reason;
//        $scope.ngDialogData;
        $scope.decline = function(){
            waitingScreen.show();
            $http.patch("/admin/queue/decline/" + $scope.ngDialogData.request.id, {reason: this.reason})
                .success(function(data){
                    $scope.closeThisDialog({state: "success"});
                })
                .error(function(data, code){
                    $scope.closeThisDialog({state: "danger", message: (typeof data == "object" && data !== null && "message" in data? data.message: data)});
                })
                .finally(function(){
                    waitingScreen.hide();
                });
            ;
        }
    }]);

    admin.controller('adminApproveFlyerCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', function($scope, $http, redirect, $compile, waitingScreen){
        $scope.reason;
        $scope.marketingCollateral;
        $scope.filename;
        $scope.approve = function(){
            waitingScreen.show();
            $http.patch("/admin/queue/approve/flyer/" + $scope.ngDialogData.request.id, {file: this.marketingCollateral, reason: this.reason})
                .success(function(data){
                    $scope.closeThisDialog({state: "success", requestState: ($scope.marketingCollateral? settings.queue.state.approved: settings.queue.state.listingFlyerPending)});
                })
                .error(function(data, code){
                    $scope.closeThisDialog({state: "danger", message: (typeof data == "object" && data !== null && "message" in data? data.message: data)});
                })
                .finally(function(){
                    waitingScreen.hide();
                });
            ;
        }

        $scope.change = function(e){
            e.preventDefault();
            $("#uploadPdf").click();
        }

        $scope.remove = function(e){
            e.preventDefault();

            $scope.marketingCollateral = null;
        }
    }]);

    admin.controller('adminApproveCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', function($scope, $http, redirect, $compile, waitingScreen){
        $scope.reason;
        $scope.approve = function(){
            waitingScreen.show();
            $http.patch("/admin/queue/approve/" + $scope.ngDialogData.request.id, {reason: this.reason})
                .success(function(data){
                    $scope.closeThisDialog({state: "success", requestState: settings.queue.state.approved});
                })
                .error(function(data, code){
                    $scope.closeThisDialog({state: "danger", message: (typeof data == "object" && data !== null && "message" in data? data.message: data)});
                })
                .finally(function(){
                    waitingScreen.hide();
                });
            ;
        }

        $scope.remove = function(e){
            e.preventDefault();

            $scope.marketingCollateral = null;
        }
    }]);

    admin.directive('loAdminNavBar', ['$location', 'Tab', function($location, Tab){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.nav.bar',
            link: function(scope, element, attrs, controllers){
                scope.tabs = [
                    new Tab({path: '/admin', title: "User Management"}),
                    new Tab({path: '/admin/queue', title: "Request Management"})
                ]
            }
        }
    }]);

    admin.directive('loAdminUsers', ['$http', "getRoles", "$location", "tableHeadCol", "waitingScreen", "createUser", "renderMessage", "$q", function($http, getRoles, $location, tableHeadCol, waitingScreen, createUser, renderMessage, $q){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.panel.users',
            link: function(scope, element, attrs, controllers){
                scope.pagination = {};
                scope.users = [];
                scope.roles = {};
                scope.searchingString;
                scope.isLoaded = false;
                scope.searchKey;

                scope.getUsers = function(){
                    var deferred = $q.defer();
                    $http.get('/admin/user', {
                        params: $location.search()
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
                        scope.searchingString = $location.search()[data.keySearch];
                        scope.searchKey = data.keySearch;

                        tableHeadCol.prototype.directionKey     = data.keyDirection;
                        tableHeadCol.prototype.sortKey          = data.keySort;
                        tableHeadCol.prototype.defaultDirection = data.defDirection;
                        tableHeadCol.prototype.defaultSortKey   = data.defField;

                        scope.headParams = [
                            new tableHeadCol({key: "id", title: "id"}),
                            new tableHeadCol({key: "first_name", title: "First<br>Name"}),
                            new tableHeadCol({key: "last_name", title: "Last<br>Name"}),
                            new tableHeadCol({key: "email", title: "Email", isSortable: true}),
                            new tableHeadCol({key: "role", title: "Role", isSortable: false}),
                            new tableHeadCol({key: "title", title: "Title", isSortable: false}),
                            new tableHeadCol({key: "phone", title: "Primary<br>Phone", isSortable: false}),
                            new tableHeadCol({key: "mobile", title: "Mobile<br>Phone", isSortable: false}),
                            new tableHeadCol({key: "created_at", title: "Created", isSortable: true}),
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

                scope.resetPassword = function(e, user){
                    e.preventDefault();
                    waitingScreen.show();

                    user.resetPassword().then(function(){
                            renderMessage("Password has been reset.", "success", scope.container, scope);
                        })
                        .catch(function(data){
                            var message = "message" in data? data.message: data;
                            renderMessage(message, "danger", scope.container, scope);
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        })
                    ;
                }
            }
        }
    }]);

    admin.directive('loAdminRequests', ['$http', 'tableHeadCol', '$location', "ngDialog", "renderMessage", function($http, tableHeadCol, $location, ngDialog, renderMessage){
        return {
            restrict: 'EA',
            templateUrl: '/partials/admin.panel.requests',
            link: function(scope, element, attrs, controllers){
                scope.queue = []
                scope.searchKey;
                scope.searchingString;
                scope.pagination = {};
                scope.messageContainer = angular.element("#messageContainer")
                scope.states = settings.queue.state;

                $http.get('/admin/queue', {
                        params: $location.search()
                    })
                    .success(function(data){
                        scope.queue     = data.queue;
                        scope.searchKey = data.keySearch;
                        scope.pagination = data.pagination;
                        scope.searchingString = $location.search()[data.keySearch];
                        tableHeadCol.prototype.directionKey     = data.keyDirection;
                        tableHeadCol.prototype.sortKey          = data.keySort;
                        tableHeadCol.prototype.defaultDirection = data.defDirection;
                        tableHeadCol.prototype.defaultSortKey   = data.defField;

                        scope.headParams = [
                            new tableHeadCol({key: "id", title: "Request ID"}),
                            new tableHeadCol({key: "user_id", title: "User ID"}),
                            new tableHeadCol({key: "address", title: "Property Address"}),
                            new tableHeadCol({key: "mls_number", title: "MLS<br>Number"}),
                            new tableHeadCol({key: "created_at", title: "Created", isSortable: true}),
                            new tableHeadCol({key: "request_type", title: "Type"}),
                            new tableHeadCol({key: "state", title: "Status"}),
                            new tableHeadCol({key: "action", title: "Actions", isSortable: false}),
                        ];
                    })
                ;

                scope.getDialogByRequest = function(request){
                    return ngDialog.open({
                        template: request.request_type == settings.queue.type.flyer? '/partials/admin.request.approve.flyer': '/partials/admin.request.approve',
                        showClose: false,
                        controller: request.request_type == settings.queue.type.flyer? 'adminApproveFlyerCtrl': 'adminApproveCtrl',
                        data: {
                            request: request
                        }
                    });
                }

                scope.approve = function(e, request){
                    e.preventDefault();

                    var dialog = this.getDialogByRequest(request);

                    dialog.closePromise.then(function (data) {
                        if(data.value == undefined || data.value.state == undefined){
                            return;
                        }

                        if(data.value.state == "success"){
                            request.state = data.value.requestState;
                            renderMessage("Approved", data.value.state, scope.messageContainer, scope);
                            return;
                        }

                        renderMessage(data.value.message, data.value.state, scope.messageContainer, scope);
                    });
                }

                scope.decline = function (e, request) {
                    e.preventDefault();
                    var dialog = ngDialog.open({
                        template: '/partials/admin.request.decline',
                        showClose: false,
                        controller: 'adminDiscardCtrl',
                        data: {
                            request: request
                        }
                    });

                    dialog.closePromise.then(function (data) {
                        if(data.value == undefined || data.value.state == undefined){
                            return;
                        }

                        if(data.value.state == "success"){
                            request.state = settings.queue.state.declined;
                            renderMessage("Declined", data.value.state, scope.messageContainer, scope);
                            return;
                        }

                        renderMessage(data.value.message, data.value.state, scope.messageContainer, scope);
                    });
                };
            }
        }
    }]);

    admin.factory('tableHeadCol', ['$sce', '$location', function($sce, $location){
        function headCol(params){
            params = params || {}
            this.key;
            this.title;
            this.isSortable = true;

            this.sort = function(){
                if(!this.isSortable){
                    return false;
                }

                var newLocationParams = {}

                newLocationParams[this.getDirectionKey()] = this.getDirection();

                newLocationParams[this.getSortKey()] = this.key;

                this.location.search(newLocationParams);
            }

            this.getDirection = function(){
                if(this.getLocationParams()[this.getSortKey()] == undefined && this.key == this.getDefaultSortKey() || (this.getLocationParams()[this.getSortKey()] == this.key && this.getLocationParams()[this.getDirectionKey()] == undefined)){
                    return "desc"
                }else if(this.getLocationParams()[this.getSortKey()] != this.key){
                    return this.getDefaultDirection();
                }else{
                    return this.getLocationParams()[this.getDirectionKey()] != undefined && this.getLocationParams()[this.getDirectionKey()] == "asc" ? "desc" : "asc";
                }
            }

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

    admin.directive('loAdminPanelSearch', ["$location", function($location){
        return {
            restrict: 'EA',
            templateUrl: '/partials/admin.panel.search',
            scope: {
                searchKey: "=loSearchKey",
                searchingString: "=loSearchingString"
            },
            link: function(scope, element, attrs, controllers){
                scope.search = function(){
                    var locationParams = $location.search();
                    if(this.searchingString == ""){
                        delete locationParams[scope.searchKey];
                    }else{
                        locationParams[scope.searchKey] = scope.searchingString;
                    }

                    $location.search(locationParams);
                }
            }
        }
    }]);

    admin.directive('loAdminPanelPagination', ["$location", function($location){
        return {
            restrict: 'EA',
            templateUrl: '/partials/admin.pagination',
            scope: {
                pagination: "=loPagination"
            },
            link: function(scope, element, attrs, controllers){
                scope.getUrl = function(isNext){
                    return '/#' + $location.path() + '?' + this.getParams(isNext? scope.pagination.next: scope.pagination.previous);
                }

                scope.getParams = function(page){
                    var params = angular.copy($location.search());
                    if(page){
                        params.page = page;
                    }

                    return $.param(params);
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