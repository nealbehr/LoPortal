/**
 * Created by Eugene Lysenko on 12/21/15.
 */
(function(settings) {
    'use strict';
    settings   = settings || {};

    var module = angular.module('collateralModule', ['adminModule', 'checklist-model']);

    /**
     * Constants
     */
    var PATH    = '/admin/collateral',
        ARCHIVE = {
            id        : 0,
            name      : 'Archive',
            admin_name: 'Archive',
            user_name : 'Archive'
        };

    /**
     * Controllers
     */
    module.controller(
        'adminCollateralListCtrl',
        ['$scope', 'createTemplate', 'waitingScreen', '$http', '$q',
        function($scope, createTemplate, waitingScreen, $http, $q) {
        $scope.categories = {};
        $scope.templates  = [];

        waitingScreen.show();

        var categories = $http.get('/request/template/categories', {cache: true}),
            templates  = $http.get(PATH);
        $q.all([categories, templates]).then(function(response) {
            $scope.categories = response[0].data;
            $scope.categories.push(ARCHIVE);
            $scope.templates  = response[1].data;
        }).finally(function() {
            waitingScreen.hide();
        });
    }]);

    module.controller(
        'adminCollateralEditCtrl',
        ['$scope', 'createTemplate', '$routeParams', 'waitingScreen', 'renderMessage',
            function($scope, createTemplate, $routeParams, waitingScreen, renderMessage) {
                $scope.template = createTemplate();

                if ($routeParams.id) {
                    waitingScreen.show();

                    createTemplate().get($routeParams.id).then(function(data) {
                        $scope.template = data;
                    }).catch(function(data) {
                        renderMessage(data.message, 'danger', angular.element('#message-box'), $scope);
                    }).finally(function() {
                        waitingScreen.hide();
                    });
                }
            }
        ]
    );

    /**
     * Services
     */
    module.service(
        'createTemplate',
        ['$q', '$http', 'createTemplateBase',
            function($q, $http, createTemplateBase) {
                return function() {
                    var model = new createTemplateBase();
                    
                    model.delete = function() {
                        var deferred = $q.defer();
                        $http.delete(PATH+'/'+this.id).success(function(data) {
                            deferred.resolve(data);
                        }).error(function(data){
                            deferred.reject(data);
                        });

                        return deferred.promise;
                    };

                    model.save = function() {
                        return this.id ? this.update() : this.add();
                    };

                    model.update = function() {
                        var deferred = $q.defer();
                        $http.put(PATH+'/'+this.id, {template: this.getFields4Save()}).success(function(data) {
                            deferred.resolve(data);
                        }).error(function(data){
                            deferred.reject(data);
                        });

                        return deferred.promise;
                    };

                    model.add = function() {
                        var deferred = $q.defer();
                        $http.post(PATH, {template: this.getFields4Save()}).success(function(data) {
                            model.id = data.id;
                            deferred.resolve(data);
                        }).error(function(data){
                            deferred.reject(data);
                        });

                        return deferred.promise;
                    };

                    return model;
                }
            }
        ]
    );

    module.service('createTemplateBase', ['$q', '$http', function($q, $http) {
        return function() {
            var self = this;
            
            // Variables
            this.id              = null;
            this.archive         = 0;
            this.category_id     = null;
            this.format_id       = null;
            this.co_branded      = 0;
            this.lenders         = [];
            this.lenders_all     = 1;
            this.states          = [];
            this.states_all      = 1;
            this.name            = null;
            this.description     = null;
            this.preview_picture = null;
            this.file_type       = null;
            this.file            = null;

            this.getFile = function() {
                return this.file;
            };

            this.setFile = function(param) {
                this.file = param;
                return this;
            };

            this.fill = function(data) {
                for (var key in data) {
                    if (data.hasOwnProperty(key)) {
                        this[key] = data[key];
                    }
                }
                return this;
            };

            this.getFields4Save = function() {
                var result = {};
                for (var key in this) {
                    if (this.hasOwnProperty(key)) {
                        if (typeof this[key] == 'function') {
                            continue;
                        }
                        result[key] = this[key];
                    }
                }
                return result;
            };

            this.get = function(id) {
                var deferred = $q.defer();
                $http.get(PATH+'/'+id).success(function(data) {
                    self.fill(data);
                    deferred.resolve(self)
                }).error(function(data) {
                    deferred.reject(data);
                });

                return deferred.promise;
            };

            this.clear = function() {
                for (var key in this) {
                    if (this.hasOwnProperty(key)) {
                        if (typeof this[key] == 'function') {
                            continue;
                        }
                        this[key] = undefined;
                    }
                }
            };

            this.save = function() {
                throw new Error('Add must be override');
            };

            this.add = function() {
                throw new Error('Request add must be override');
            };

            this.update = function() {
                throw new Error('Request update must be override');
            };
        }
    }]);
})(settings);
