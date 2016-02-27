(function(settings){
    "use strict";
    settings = settings || {};

    var admin = angular.module('adminModule', ['headColumnModule']);

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
     * Get statuses for approve/decline forms
     */
    admin.factory('getStatusesByType', ['$q', '$http', function($q, $http) {
        return function(type, cacheable) {
            var deferred = $q.defer();
            $http.get('/admin/status/all', {
                cache :  cacheable || true,
                params: {
                    filterValue: type,
                    searchBy   : 'type'
                }
            }).success(function(data) {
                deferred.resolve(data)
            }).error(function(data) {
                deferred.reject(data);
            });

            return deferred.promise;
        }
    }]);

    admin.controller(
        'adminDiscardCtrl',
        ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'getStatusesByType',
            function($scope, $http, redirect, $compile, waitingScreen, getStatusesByType)
        {
        $scope.reason;
        $scope.statusId;
        $scope.other;
        $scope.statuses = [];

        waitingScreen.show();
        getStatusesByType('decline').then(function(data) {
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
                    other   : this.other,
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
        ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'getStatusesByType',
            function($scope, $http, redirect, $compile, waitingScreen, getStatusesByType)
        {
        $scope.reason;
        $scope.marketingCollateral;
        $scope.filename;
        $scope.note;
        $scope.statusId;
        $scope.statuses = [];

        waitingScreen.show();
        getStatusesByType('approve').then(function(data) {
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
                    reason  : this.note,
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
        ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'getStatusesByType',
            function($scope, $http, redirect, $compile, waitingScreen, getStatusesByType)
        {
        $scope.note;
        $scope.statusId;
        $scope.statuses = [];

        waitingScreen.show();
        getStatusesByType('approve').then(function(data) {
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
                    reason  : this.note,
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
        };

        $scope.remove = function(e){
            e.preventDefault();

            $scope.marketingCollateral = null;
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