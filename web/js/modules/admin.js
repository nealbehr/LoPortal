(function(settings){
    "use strict";
    settings = settings || {};

    var admin = angular.module('adminModule', ['headColumnModule']);

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
            })
            .when('/admin/flyer/:id/edit', {
                templateUrl: '/partials/admin.request.flyer.edit',
                controller:  'AdminRequestFlyerEditController',
                access: {
                    isFree: false
                }
            })
            .when('/admin/approval/:id/edit', {
                templateUrl: '/partials/admin.request.property.approval',
                controller:  'propertyApprovalEditCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/admin/realtors', {
                templateUrl: '/partials/admin.realtors',
                controller:  'realtorsCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/admin/realtor/:id/edit', {
                templateUrl: '/partials/admin.realtor.edit',
                controller:  'realtorEditCtrl',
                access: {
                    isFree: false
                }
            });
    }]);

    admin.controller('realtorsCtrl', ['$scope', 'createAdminRequestFlyer', '$routeParams', "createProfileUser", 'sessionMessages', "$http", function($scope, createAdminRequestFlyer, $routeParams, createProfileUser, sessionMessages, $http){

    }]);

    admin.controller('realtorEditCtrl', ['$scope', 'createAdminRequestFlyer', '$routeParams', "createProfileUser", 'sessionMessages', "$http", function($scope, createAdminRequestFlyer, $routeParams, createProfileUser, sessionMessages, $http){

    }]);

    admin.controller('AdminRequestFlyerEditController', ['$scope', 'createAdminRequestFlyer', '$routeParams', "createProfileUser", 'sessionMessages', "$http", function($scope, createAdminRequestFlyer, $routeParams, createProfileUser, sessionMessages, $http){
        $scope.request = {};
        $scope.realtor = {};
        $scope.titles = {
            button: "Submit",
            header: "Edit Listing Flyer Request"
        };

        $scope.$on('requestFlyerSaved', function () {
            sessionMessages.addSuccess("Successfully saved.");
            history.back();
        });

        $http.get('/request/' + $routeParams.id)
            .success(function(info){
                $scope.request = (new createAdminRequestFlyer($routeParams.id)).fill(info);
                $scope.realtor = createProfileUser().fill(info.user);
            })
        ;
    }]);

    admin.controller('propertyApprovalEditCtrl', ['$scope', 'createAdminPropertyApproval', '$routeParams', 'getInfoFromGeocoder', 'sessionMessages', function($scope, createAdminPropertyApproval, $routeParams, getInfoFromGeocoder, sessionMessages){
        $scope.request = {};
        $scope.lat;
        $scope.lng;
        $scope.titles = {
            button: "Save",
            header: "Edit Property Approval Request Form"
        };

        $scope.approval;

        $scope.$on('propertyApprovalSaved', function () {
            sessionMessages.addSuccess("Successfully saved.");
            history.back();
        });

        createAdminPropertyApproval()
            .get($routeParams.id)
            .then(function(approval){
                $scope.approval = approval;
                return getInfoFromGeocoder({address: $scope.approval.property.address});
            })
            .then(function(data){
                var googleInfo = data.shift();
                $scope.lat = googleInfo.geometry.location.lat();
                $scope.lng = googleInfo.geometry.location.lng();

                $scope.request = $scope.approval;
            })
            .catch(function(error){
                console.log(error);
                alert(error);
            })
        ;
    }]);

    admin.controller('adminQueueCtrl', ['$scope', function($scope){
        $scope.settings  = settings;
        $scope.stateRows = {};
        $scope.typeRows  = {};

        $scope.stateRows[settings.queue.state.requested]      = {id: 'requested', title: 'Requested'};
        $scope.stateRows[settings.queue.state.approved]       = {id: 'approved', title: 'Approved'};
        $scope.stateRows[settings.queue.state.declined]       = {id: 'declined', title: 'Declined'};
        $scope.stateRows[settings.queue.state.draft]          = {id: 'draft', title: 'Incomplete'};

        $scope.typeRows[settings.queue.type.flyer]            = {id: 'flyer', title: 'Listing Flyer'};
        $scope.typeRows[settings.queue.type.propertyApproval] = {id: 'propertyApproval', title: 'Property Approval'};
    }]);

    admin.controller('adminUserNewCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createUser', function($scope, $http, redirect, $compile, waitingScreen, createUser){
        $scope.officer = createUser();

    }]);

    admin.controller('AdminUserEditController', ['$scope', 'officerData', function($scope, officerData){
        $scope.officer = officerData;
    }]);

    admin.controller('adminCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', function($scope, $http, redirect, $compile, waitingScreen){

    }]);

    /**
     * Get statuses
     */
    admin.factory('getStatuses', ['$q', '$http', function($q, $http) {
        var data = [];

        return function(params, needReload) {
            var deferred = $q.defer();
            if (data.length !== 0 && !needReload) {
                return $q.when(data);
            }

            $http.get('/admin/status/all', params).success(function(response) {
                data = response;
                deferred.resolve(response);
            }).error(function(response) {
                deferred.reject(response);
            });

            return deferred.promise;
        }
    }]);

    admin.controller(
        'adminDiscardCtrl',
        ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'getStatuses',
            function($scope, $http, redirect, $compile, waitingScreen, getStatuses)
        {
        $scope.reason;
        $scope.statusId;
        $scope.statuses = [];

        /**
         * Get statuses
         */
        waitingScreen.show();
        getStatuses({
            params: {
                filterValue: 'decline',
                searchBy   : 'type'
            }
        }).then(function(data) {
            // Set status id
            if (typeof data[0] !== 'undefined' && data[0].hasOwnProperty('id')) {
                $scope.statusId = data[0].id;
            }
            $scope.statuses = data;
        }).finally(function() {
            waitingScreen.hide();
        });

        $scope.decline = function(){
            waitingScreen.show();
            $http.patch(
                '/admin/queue/decline/'+$scope.ngDialogData.request.id,
                {
                    reason  : this.reason,
                    statusId: this.statusId
                }
            )
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

    admin.controller(
        'adminApproveFlyerCtrl',
        ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'getStatuses',
            function($scope, $http, redirect, $compile, waitingScreen, getStatuses)
        {
        $scope.reason;
        $scope.marketingCollateral;
        $scope.filename;
        $scope.note;
        $scope.statusId;
        $scope.statuses = [];

        /**
         * Get statuses
         */
        waitingScreen.show();
        getStatuses({
            params: {
                filterValue: 'approve',
                searchBy   : 'type'
            }
        }).then(function(data) {
            // Set status id
            if (typeof data[0] !== 'undefined' && data[0].hasOwnProperty('id')) {
                $scope.statusId = data[0].id;
            }
            $scope.statuses = data;
        }).finally(function() {
            waitingScreen.hide();
        });

        $scope.approve = function(){
            waitingScreen.show();
            $http.patch(
                '/admin/queue/approve/flyer/'+$scope.ngDialogData.request.id,
                {
                    file    : this.marketingCollateral,
                    reason  : this.reason,
                    note    : this.note,
                    statusId: this.statusId
                }
            )
                .success(function(data){
                    $scope.closeThisDialog({state: "success", requestState: settings.queue.state.approved});
                })
                .error(function(data, code){
                    $scope.closeThisDialog({state: "danger", message: (typeof data == "object" && data !== null && "message" in data? data.message: data)});
                })
                .finally(function(){
                    waitingScreen.hide();
                });
        };

        $scope.change = function(e){
            e.preventDefault();
            $("#uploadPdf").click();
        };

        $scope.remove = function(e){
            e.preventDefault();

            $scope.marketingCollateral = null;
        }
    }]);

    admin.controller(
        'adminApproveCtrl',
        ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'getStatuses',
            function($scope, $http, redirect, $compile, waitingScreen, getStatuses)
        {
        $scope.note;
        $scope.statusId;
        $scope.statuses = [];

        /**
         * Get statuses
         */
        waitingScreen.show();
        getStatuses({
            params: {
                filterValue: 'approve',
                searchBy   : 'type'
            }
        }).then(function(data) {
            // Set status id
            if (typeof data[0] !== 'undefined' && data[0].hasOwnProperty('id')) {
                $scope.statusId = data[0].id;
            }
            $scope.statuses = data;
        }).finally(function() {
            waitingScreen.hide();
        });

        $scope.approve = function(){
            waitingScreen.show();
            $http.patch(
                '/admin/queue/approve/'+$scope.ngDialogData.request.id,
                {
                    note    : this.note,
                    statusId: this.statusId
                }
            ).success(function(data){
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
                    new Tab({path: '/admin', title: "User Management", button_text: "Add User", button_href: "/admin/user/new"}),
                    new Tab({path: '/admin/queue', title: "Request Management"}),
                    new Tab({path: '/admin/lender', title: "Lender", button_text: "Add Lender", button_href: "/admin/lender/new"}),
                    new Tab({
                        path       : '/admin/realtor',
                        title      : 'Realtor',
                        button_text: 'Add Realtor',
                        button_href: '/admin/realtor/new'
                    }),
                    new Tab({path: '/admin/realty', title: "Realty Company", button_text: "Add Company", button_href: "/admin/realty/new"}),
                    new Tab({
                        path       : '/admin/salesdirector',
                        title      : 'Sales Director',
                        button_text: 'Add Sales Director', 
                        button_href: '/admin/salesdirector/new'
                    })
                ]
            }
        }
    }]);

    admin.directive('loAdminUsers', ['$http', "getRoles", "getLenders", "$location", "tableHeadCol", "waitingScreen", "createUser", "renderMessage", "$q", function($http, getRoles, getLenders, $location, tableHeadCol, waitingScreen, createUser, renderMessage, $q){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.panel.users',
            link: function(scope, element, attrs, controllers){
                scope.pagination = {};
                scope.users = [];
                scope.roles = {};
                scope.lenders = [];
                scope.searchingString;
                scope.isLoaded = false;
                scope.searchKey;

                scope.getUsers = function() {
                    var deferred = $q.defer();
                    waitingScreen.show();
                    $http.get('/admin/user', {
                        params: $location.search()
                    }).success(function(data){
                        return deferred.resolve(data);
                    }).finally(function() {
                        waitingScreen.hide();
                    });

                    return deferred.promise;
                };

                getRoles()
                    .then(function(data){
                        scope.roles = data;
                    })
                    .then(function(){
                        scope.lenders = getLenders();
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

                        function params(settings){
                            this.key   = settings.key;
                            this.title = settings.title;
                        }

                        params.prototype.directionKey     = data.keyDirection;
                        params.prototype.sortKey          = data.keySort;
                        params.prototype.defaultDirection = data.defDirection;
                        params.prototype.defaultSortKey   = data.defField;

                        scope.headParams = [
                            new tableHeadCol(new params({key: "id", title: "id"})),
                            new tableHeadCol(new params({key: "first_name", title: "First<br>Name"})),
                            new tableHeadCol(new params({key: "last_name", title: "Last<br>Name"})),
                            new tableHeadCol(new params({key: "email", title: "Email", isSortable: true})),
                            new tableHeadCol(new params({key: "password", title: "Password", isSortable: false})),
                            new tableHeadCol(new params({key: "role", title: "Role", isSortable: false})),
                            new tableHeadCol(new params({key: "title", title: "Title", isSortable: false})),
                            new tableHeadCol(new params({key: "phone", title: "Primary<br>Phone", isSortable: false})),
                            new tableHeadCol(new params({key: "mobile", title: "Mobile<br>Phone", isSortable: false})),
                            new tableHeadCol(new params({key: "created_at", title: "Created", isSortable: true})),
                            new tableHeadCol(new params({key: "action", title: "Actions", isSortable: false}))
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
                };

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

    admin.directive('loAdminRequests', ['$http', 'tableHeadCol', '$location', "ngDialog", "renderMessage", "waitingScreen", 'sessionMessages', function($http, tableHeadCol, $location, ngDialog, renderMessage, waitingScreen, sessionMessages){
        return {
            restrict: 'EA',
            templateUrl: '/partials/admin.panel.requests',
            link: function(scope, element, attrs, controllers){
                scope.queue = [];
                scope.searchKey;
                scope.searchingString;
                scope.pagination = {};
                scope.messageContainer = angular.element("#messageContainer")
                scope.states = settings.queue.state;

                waitingScreen.show();
                $http.get('/admin/queue', {
                        params: $location.search()
                    })
                    .success(function(data){
                        scope.queue     = data.queue;
                        scope.searchKey = data.keySearch;
                        scope.pagination = data.pagination;
                        scope.searchingString = $location.search()[data.keySearch];

                        function params(settings){
                            this.key   = settings.key;
                            this.title = settings.title;
                        }

                        params.prototype.directionKey     = data.keyDirection;
                        params.prototype.sortKey          = data.keySort;
                        params.prototype.defaultDirection = data.defDirection;
                        params.prototype.defaultSortKey   = data.defField;

                        scope.headParams = [
                            new tableHeadCol(new params({key: "id", title: "Request ID"})),
                            new tableHeadCol(new params({key: "user_id", title: "User ID"})),
                            new tableHeadCol(new params({key: "address", title: "Property Address"})),
                            new tableHeadCol(new params({key: "mls_number", title: "MLS<br>Number"})),
                            new tableHeadCol(new params({key: "created_at", title: "Created", isSortable: true})),
                            new tableHeadCol(new params({key: "request_type", title: "Type"})),
                            new tableHeadCol(new params({key: "state", title: "Status"})),
                            new tableHeadCol(new params({key: "action", title: "Actions", isSortable: false}))
                        ];
                    })
                    .finally(function() {
                        waitingScreen.hide();
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
                };

                scope.approve = function(e, request){
                    e.preventDefault();

                    var dialog = this.getDialogByRequest(request);

                    dialog.closePromise.then(function (data) {
                        if(data.value == undefined || data.value.state == undefined){
                            return;
                        }

                        if(data.value.state == "success"){
                            request.state = data.value.requestState;
                            sessionMessages.addSuccess("Approved").render();
                            return;
                        }

                        renderMessage(data.value.message, data.value.state, scope.messageContainer, scope);
                    });
                };

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
                            sessionMessages.addSuccess("Declined").render();
                            return;
                        }

                        renderMessage(data.value.message, data.value.state, scope.messageContainer, scope);
                    });
                };
            }
        }
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
            link: function(scope, element, attrs, controllers) {

                scope.getUrl = function(isNext){
                    return '/#' + $location.path() + '?' + this.getParams(isNext? scope.pagination.next: scope.pagination.previous);
                };

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

    admin.filter('replaceOnTitle', function() {
        return function(val, obj) {
            for (var key in obj) {
                if (obj[key].hasOwnProperty('title') && parseInt(key) === parseInt(val)) {
                    return obj[key]['title'];
                }
            }
        };
    });
})(settings);