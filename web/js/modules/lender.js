(function(settings){
    'use strict';
    settings = settings || {};

    var lenderModule = angular.module('lenderModule', ['adminModule']);

    lenderModule.config(['$routeProvider', function($routeProvider) {
        $routeProvider
            .when('/admin/lender', {
                templateUrl: '/partials/admin.lender',
                controller:  'adminLendersCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/admin/lender/new', {
                templateUrl: '/partials/admin.panel.lender',
                controller:  'adminLenderNewCtrl',
                access: {
                    isFree: false
                }
            })
            .when('/admin/lender/:id/edit', {
                templateUrl: '/partials/admin.panel.lender',
                controller:  'adminLenderEditCtrl',
                access: {
                    isFree: false
                }
            })
        ;
    }]);

    lenderModule.service("createLender", ["$q","$http", "createLenderBase", function($q, $http, createLenderBase){
        return function() {
            var lenderBase = new createLenderBase();

            lenderBase.delete = function() {
                if(!this.id){
                    alert('Lender id has not set.');
                }
                var deferred = $q.defer();
                $http.delete('/admin/lender/' + this.id, {})
                    .success(function(data) {
                        if(data.status == 'error') {
                            deferred.reject(data);
                        } else {
                            deferred.resolve(data);
                        }
                    })
                    .error(function(data){
                        console.log(data);
                        deferred.reject(data);
                    })
                ;
                return deferred.promise;
            };

            lenderBase.save = function(){
                return this.id? this.update(): this.add();
            };

            lenderBase.update = function(){
                var deferred = $q.defer();
                $http.put('/admin/lender/' + this.id, {lender: this.getFields4Save()})
                    .success(function(data){
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        console.log(data);
                        deferred.reject(data);
                    })
                ;
                return deferred.promise;
            };

            lenderBase.add = function(){
                var deferred = $q.defer();
                $http.post('/admin/lender', {lender: this.getFields4Save()})
                    .success(function(data){
                        lenderBase.id = data.id;
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        console.log(data);
                        deferred.reject(data);
                    })
                ;
                return deferred.promise;
            };

            return lenderBase;
        }
    }]);

    lenderModule.service("createLenderBase", ["$http","$q", function($http, $q){
        return function () {

            this.id = null;
            this.name = null;
            this.address = null;
            this.disclosure = null;
            this.picture = null;

            var self = this;

            this.getPicture = function(){
                return this.picture;
            };
            this.setPicture = function(param){
                this.picture = param;
                return this;
            };

            this.fill = function(data){
                for(var key in data) {
                    if (data.hasOwnProperty(key)) {
                        this[key] = data[key];
                    }
                }
                return this;
            };

            this.getFields4Save = function(){
                var result = {};
                for(var key in this) {
                    if (this.hasOwnProperty(key)) {
                        if(typeof this[key] == 'function'){
                            continue;
                        }
                        result[key] = this[key];
                    }
                }
                return result;
            };

            this.get = function(id){
                var deferred = $q.defer();
                $http.get('/admin/lender/' + id)
                    .success(function(data){
                        self.fill(data);
                        deferred.resolve(self)
                    })
                    .error(function(data){
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            };

            this.clear = function() {
                for(var key in this){
                    if (this.hasOwnProperty(key)) {
                        if(typeof this[key] == 'function'){
                            continue;
                        }
                        this[key] = undefined;
                    }
                }
            };

            this.save = function(){
                throw new Error("User add must be override");
            };

            this.add = function(){
                throw new Error("Request add must be override");
            };

            this.update = function(){
                throw new Error("Request update must be override");
            };

        }
    }]);

    lenderModule.controller('adminLendersCtrl', ['$scope', function($scope){
        $scope.settings = settings;
    }]);

    lenderModule.controller('adminLenderNewCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createLender', function($scope, $http, redirect, $compile, waitingScreen, createLender) {
        $scope.lender = createLender();
    }]);

    lenderModule.controller('adminLenderEditCtrl', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createLender', '$routeParams', function($scope, $http, redirect, $compile, waitingScreen, createLender, $routeParams){
        createLender().get($routeParams.id)
            .then(function(lender){
                $scope.lender = lender;
            });
    }]);

    lenderModule.directive('loAdminLenders', ['$http', 'tableHeadCol', '$location', "ngDialog", "renderMessage", "waitingScreen", 'createLender', function($http, tableHeadCol, $location, ngDialog, renderMessage, waitingScreen, createLender) {
        return {
            restrict: 'EA',
            templateUrl: '/partials/admin.panel.lenders',
            link: function(scope, element, attrs, controllers){
                scope.lenders = [];
                scope.pagination = {};
                scope.messageContainer = angular.element("#messageContainer");

                scope.delete = function(e, key, lender) {
                    e.preventDefault();
                    if(!confirm("Are you sure?")){
                        return false;
                    }

                    waitingScreen.show();

                    lender.delete()
                    .then(function(data) {
                        renderMessage("Lender was deleted.", "success", scope.messageContainer, scope);
                        scope.lenders.splice(key, 1);
                    })
                        .catch(function(data) {
                            if('message' in data){
                                renderMessage(data.message, "danger", scope.messageContainer, scope);
                                scope.gotoErrorMessage();
                            }
                        })

                    .finally(function(){
                            waitingScreen.hide();
                        })
                    ;
                };

                $http.get('/admin/lender', {
                    params: $location.search()
                })
                    .success(function(data){

                        scope.lenders = [];
                        for(var i in data.lenders){
                            scope.lenders.push(createLender().fill(data.lenders[i]));
                        }
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
                            new tableHeadCol(new params({key: "id", title: "id"})),
                            new tableHeadCol(new params({key: "name", title: "Lender<br>name"})),
                            new tableHeadCol(new params({key: "address", title: "Lender<br>address", isSortable: false})),
                            new tableHeadCol(new params({key: "disclosure", title: "Lender<br>disclosure", isSortable: false})),
                            new tableHeadCol(new params({key: "picture", title: "Lender<br>logo", isSortable: false})),
                            new tableHeadCol(new params({key: "action", title: "Actions", isSortable: false}))
                        ];
                    })
                ;

            }
        }
    }]);

    lenderModule.directive('loAdminLenderInfo', ["redirect", "$http", "waitingScreen", "renderMessage", "getRoles", "$location", "$q", "sessionMessages", "$anchorScroll", "loadFile", "$timeout", "pictureObject", function(redirect, $http, waitingScreen, renderMessage, getRoles, $location, $q, sessionMessages, $anchorScroll, loadFile, $timeout, pictureObject){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.panel.lender.form',
            scope: {
                lender:   "=loLender"
            },
            link: function(scope, element, attrs, controllers) {

                scope.container = angular.element('#lenderMessage');
                scope.lenderPicture;

                scope.$watch('lender.id', function(newVal, oldVal){
                    if(undefined != newVal && newVal == scope.lender.id){
                        scope.title = "Edit Lender";
                        return;
                    }

                    scope.title = newVal? 'Edit Lender': 'Add Lender';
                });

                scope.$watch('lender', function(newVal, oldVal){
                    if(newVal == undefined || !("id" in newVal)){
                        return;
                    }

                    scope.lenderPicture = new pictureObject(
                        angular.element('#lenderPhoto'),
                        {container: $(".realtor-photo > img"), options: {
                            minContainerWidth: 300,
                            minContainerHeight: 100,
                            minCropBoxWidth: 300,
                            minCropBoxHeight: 100,
                            aspectRatio: 3
                        }},
                        scope.lender
                    );
                });

                scope.cancel = function(e){
                    e.preventDefault();
                    history.back();
                };

                scope.gotoErrorMessage = function(){
                    $anchorScroll(scope.container.attr("id"));
                };

                scope.submit = function(formLender){
                    if(!formLender.$valid){
                        this.gotoErrorMessage();
                        return false;
                    }
                    this.save();
                };

                scope.save = function(){
                    waitingScreen.show();

                    if(scope.lender.picture) {
                        scope.lenderPicture.prepareImage(100, 100, 300, 100);
                    }

                    scope.lender.save()
                        .then(function(data){
                            sessionMessages.addSuccess("Successfully saved.");
                            history.back();
                        })
                        .catch(function(data){
                            var errors = "";
                            if("message" in data){
                                errors = data.message + " ";
                            }

                            if("form_errors" in data){
                                errors += data.form_errors.join(" ");
                            }

                            renderMessage(errors, "danger", scope.container, scope);
                            scope.gotoErrorMessage();
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        }
                    );
                };

                scope.delete = function(e){
                    e.preventDefault();

                    if(!confirm("Are you sure?")){
                        return false;
                    }

                    waitingScreen.show();
                    scope.lender.delete().
                        then(function(data){
                            sessionMessages.addSuccess("Lender was deleted.");
                            scope.lender.clear();
                            history.back();
                        })
                        .catch(function(data){
                            if('message' in data){
                                renderMessage(data.message, "danger", scope.container, scope);
                                scope.gotoErrorMessage();
                            }
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        });
                }
            }
        }
    }]);

    lenderModule.filter('lenderImage', function(){
        return function(input){
            return "" != input && null !== input && input !== undefined
                ? input
                : '/images/empty-lender.png';
        }
    });

})(settings);