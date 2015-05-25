(function(settings){
    'use strict';
    settings = settings || {};

    var realtyCompanyModule = angular.module('realtyCompanyModule', ['adminModule']);

    realtyCompanyModule.config(['$routeProvider', function($routeProvider) {
        $routeProvider
            .when('/admin/realty', {
                templateUrl: '/partials/admin.panel.realty',
                controller:  'AdminCompaniesController',
                access: {
                    isFree: false
                }
            })
            .when('/admin/realty/new', {
                templateUrl: '/partials/admin.panel.realty.company',
                controller:  'AdminRealtyNewController',
                access: {
                    isFree: false
                }
            })
            .when('/admin/realty/:id/edit', {
                templateUrl: '/partials/admin.panel.realty.company',
                controller:  'AdminRealtyEditController',
                access: {
                    isFree: false
                }
            })
        ;
    }]);

    realtyCompanyModule.service("createRealtyCompany", ["$q","$http", "createRealtyCompanyBase", function($q, $http, createRealtyCompanyBase){
        return function() {
            var realtyBase = new createRealtyCompanyBase();

            realtyBase.delete = function() {
                if(!this.id){
                    alert('Realty Company id has not set.');
                }
                var deferred = $q.defer();
                $http.delete('/admin/realty/' + this.id, {})
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

            realtyBase.save = function(){
                return this.id? this.update(): this.add();
            };

            realtyBase.update = function(){
                var deferred = $q.defer();
                $http.put('/admin/realty/' + this.id, {company: this.getFields4Save()})
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

            realtyBase.add = function(){
                var deferred = $q.defer();
                $http.post('/admin/realty', {company: this.getFields4Save()})
                    .success(function(data){
                        realtyBase.id = data.id;
                        deferred.resolve(data);
                    })
                    .error(function(data){
                        console.log(data);
                        deferred.reject(data);
                    })
                ;
                return deferred.promise;
            };

            return realtyBase;
        }
    }]);

    realtyCompanyModule.service("createRealtyCompanyBase", ["$http","$q", function($http, $q){
        return function () {

            this.id = null;
            this.name = null;
            this.logo = null;

            var self = this;

            this.getPicture = function(){
                return this.logo;
            };
            this.setPicture = function(param){
                this.logo = param;
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
                $http.get('/admin/realty/' + id)
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

    realtyCompanyModule.factory('realtyLogosFactory', ['$http', function($http) {

        var urlBase = '/settings/realty-companies';

        var factory = {};
        factory.getRealtyCompanies = function() {
            //return realtyCompanies;
            return $http.get(urlBase);
        };
        return factory;
    }]);

    realtyCompanyModule.controller('SelectRealtyLogoController', ['$scope', 'realtyLogosFactory', function($scope, realtyLogosFactory) {

        $scope.realtyCompanies = [];

        getRealtyCompanies();

        function getRealtyCompanies() {
            realtyLogosFactory.getRealtyCompanies()
                .success(function (companies) {
                    $scope.realtyCompanies = companies;
                })
                .error(function (error) {
                    $scope.status = 'Unable to load realty companies data: ' + error.message;
                });
        }

        $scope.selectRealtyLogo = function(realtyCompany) {
            $scope.request.realtor.realty_name = realtyCompany.name;
            $scope.request.realtor.realty_logo = realtyCompany.logo;
            $('#chooseRealtyCompanyLogo').modal('hide');
        };
    }]);

    realtyCompanyModule.controller('AdminCompaniesController', ['$scope', function($scope) {
        $scope.settings = settings;
    }]);

    realtyCompanyModule.controller('AdminRealtyNewController', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createRealtyCompany', function($scope, $http, redirect, $compile, waitingScreen, createRealtyCompany) {
        $scope.company = createRealtyCompany();
    }]);

    realtyCompanyModule.controller('AdminRealtyEditController', ['$scope', '$http', 'redirect', '$compile', 'waitingScreen', 'createRealtyCompany', '$routeParams', function($scope, $http, redirect, $compile, waitingScreen, createRealtyCompany, $routeParams){
        createRealtyCompany().get($routeParams.id)
            .then(function(company){
                $scope.company = company;
            });
    }]);

    realtyCompanyModule.directive('loAdminRealtyCompanies', ['$http', 'tableHeadCol', '$location', "ngDialog", "renderMessage", "waitingScreen", 'createRealtyCompany', function($http, tableHeadCol, $location, ngDialog, renderMessage, waitingScreen, createRealtyCompany) {
        return {
            restrict: 'EA',
            templateUrl: '/partials/admin.panel.realty.companies',
            link: function(scope, element, attrs, controllers){
                scope.comanies = [];
                scope.pagination = {};
                scope.messageContainer = angular.element("#messageContainer");

                scope.delete = function(e, key, company) {
                    e.preventDefault();
                    if(!confirm("Are you sure?")){
                        return false;
                    }

                    waitingScreen.show();

                    company.delete()
                        .then(function(data) {
                            renderMessage("Realty company was deleted.", "success", scope.messageContainer, scope);
                            scope.companies.splice(key, 1);
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

                $http.get('/admin/realty', {
                    params: $location.search()
                })
                    .success(function(data){

                        scope.companies = [];
                        for(var i in data.companies){
                            scope.companies.push(createRealtyCompany().fill(data.companies[i]));
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
                            new tableHeadCol(new params({key: "name", title: "Company Name"})),
                            new tableHeadCol(new params({key: "logo", title: "Company Logo", isSortable: false})),
                            new tableHeadCol(new params({key: "action", title: "Actions", isSortable: false}))
                        ];
                    })
                ;

            }
        }
    }]);

    realtyCompanyModule.directive('loAdminRealtyCompanyInfo', ["redirect", "$http", "waitingScreen", "renderMessage", "getRoles", "$location", "$q", "sessionMessages", "$anchorScroll", "loadFile", "$timeout", "pictureObject", function(redirect, $http, waitingScreen, renderMessage, getRoles, $location, $q, sessionMessages, $anchorScroll, loadFile, $timeout, pictureObject){
        return { restrict: 'EA',
            templateUrl: '/partials/admin.panel.realty.form',
            scope: {
                company:   "=loCompany"
            },
            link: function(scope, element, attrs, controllers) {

                scope.container = angular.element('#companyMessage');
                scope.realtyLogo = {};
                scope.hideErrors = true;

                scope.$watch('company.id', function(newVal, oldVal){
                    if(undefined != newVal && newVal == scope.company.id){
                        scope.title = "Edit Realty Company";
                        return;
                    }

                    scope.title = newVal? 'Edit Realty Company': 'Add Realty Company';
                });

                scope.$watch('company', function(newVal, oldVal){
                    if(newVal == undefined || !("id" in newVal)){
                        return;
                    }

                    scope.realtyLogo = new pictureObject(
                        angular.element('#realtyLogo'),
                        {container: $(".realtor-photo > img"), options: {
                            minCropBoxWidth: 100,
                            minCropBoxHeight: 100,
                            maxCropBoxWidth: 350,
                            maxCropBoxHeight: 100
                        }},
                        scope.company
                    );
                });

                scope.cancel = function(e){
                    e.preventDefault();
                    history.back();
                };

                scope.showErrors = function(e){
                    e.preventDefault();
                    this.hideErrors = true;
                };

                scope.gotoErrorMessage = function(){
                    $anchorScroll(scope.container.attr("id"));
                };

                scope.submit = function(formRealty){
                    if(!formRealty.$valid) {
                        this.hideErrors = false;
                        this.gotoErrorMessage();
                        return false;
                    }
                    this.save();
                };

                scope.save = function() {

                    if(scope.company.logo && scope.company.logo.indexOf('http') !== 0) {
                        var isValid = scope.realtyLogo.validateNaturalSize(300, 300);
                        if(!isValid) {
                            renderMessage("Logos should be 300 px high and [300px - 1050] px wide", "danger", scope.container, scope);
                            return;
                        }
                        scope.realtyLogo.prepareFixedHeightImage(300);

                    } else if (scope.company.id === null) {
                        renderMessage("Realty Logo is required", "danger", scope.container, scope);
                        return;
                    }

                    waitingScreen.show();
                    scope.company.save()
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
                    scope.company.delete().
                        then(function(data){
                            sessionMessages.addSuccess("Realty Company was deleted.");
                            scope.company.clear();
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

})(settings);