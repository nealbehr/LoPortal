/**
 * Created by Eugene Lysenko on 12/21/15.
 */
(function(settings) {
    'use strict';
    settings   = settings || {};

    var module = angular.module('collateralModule', ['adminModule']);

    /**
     * Constants
     */
    var PATH   = '/admin/collateral';

    /**
     * Routes list
     */
    module.config(['$routeProvider', function($routeProvider) {
        $routeProvider.when(PATH, {
            templateUrl: '/partials/admin.collateral.tab',
            controller : 'collateralCtrl',
            access     : { isFree: false }
        }).when(PATH+'/new', {
            templateUrl: '/partials/admin.collateral',
            controller :  'collateralCtrl',
            access     : { isFree: false }
        }).when(PATH+'/:id/edit', {
            templateUrl: '/partials/admin.collateral',
            controller :  'collateralEditCtrl',
            access     : { isFree: false }
        });
    }]);

    /**
     * Controllers
     */
    module.controller('collateralCtrl', ['$scope', 'createTemplate', function($scope, createTemplate) {
        $scope.template = createTemplate();
    }]);

    module.controller(
        'collateralEditCtrl',
        ['$scope', 'createTemplate', '$routeParams', 'waitingScreen', 'renderMessage',
            function($scope, createTemplate, $routeParams, waitingScreen, renderMessage) {
                waitingScreen.show();
                createTemplate().get($routeParams.id).then(function(data) {
                    $scope.template = data;
                }).catch(function(data) {
                    $scope.template = createTemplate();
                    renderMessage(data.message, 'danger', angular.element('#message-box'), $scope);
                }).finally(function() {
                    waitingScreen.hide();
                });
                $scope.PATH = PATH;
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
                        $http.delete(PATH+'/'+this.id, {}).success(function(data) {
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
            this.id          = null;
            this.name        = null;
            this.description = null;
            this.picture     = null;
            this.category    = {
                id:   null,
                name: null
            };
            this.format      = {
                id:   null,
                name: null
            };

            this.getPicture = function() {
                return this.picture;
            };

            this.setPicture = function(param) {
                this.picture = param;
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

    /**
     * Directives
     */
    module.directive(
        'loAdminCollateralForm',
        ['waitingScreen', 'renderMessage', 'sessionMessages', '$anchorScroll', '$http', 'loadFile',
            function(waitingScreen, renderMessage, sessionMessages, $anchorScroll, $http, loadFile)
            {
                return {
                    restrict   : 'EA',
                    templateUrl: '/partials/admin.collateral.form',
                    scope      : { template: '=loTemplate' },
                    link       : function(scope, element, attrs, controllers) {
                        // Variables
                        scope.message    = angular.element('#message-box');
                        scope.hideErrors = true;
                        scope.formats    = [];
                        scope.categories = [];

                        scope.$watch('template.id', function(newVal, oldVal) {
                            scope.title = newVal? 'Edit Template': 'Add Template';
                        });

                        angular.element('#picture-input').on('change', function(e) {
                            loadFile(e).then(function(base64) {
                                scope.template.setPicture(base64);
                            });
                        });

                        // Get categories options
                        $http.get(PATH+'-categories', { cache: true }).success(function(data) {
                            // Set default option
                            if (scope.template.hasOwnProperty('category')
                                && null === scope.template.category.id
                                && undefined !== data[0]
                            ) {
                                scope.template.category.id = data[0].id;
                            }
                            scope.categories = data;
                        });

                        // Get formats options
                        $http.get(PATH+'-formats', { cache: true }).success(function(data) {
                            // Set default option
                            if (scope.template.hasOwnProperty('format')
                                && null === scope.template.format.id
                                && undefined !== data[0]
                            ) {
                                scope.template.format.id = data[0].id;
                            }
                            scope.formats = data;
                        });

                        scope.submit = function(form) {
                            if (!form.$valid) {
                                scope.hideErrors = false;
                                $anchorScroll(scope.message.attr('id'));
                                return false;
                            }

                            waitingScreen.show();

                            scope.template.save().then(function() {
                                sessionMessages.addSuccess('Successfully saved.');
                                history.back();
                            }).catch(function(data) {
                                var errors = '';
                                if ('message' in data) {
                                    errors += data.message+' ';
                                }

                                if ('form_errors' in data) {
                                    errors += data.form_errors.join(' ');
                                }

                                renderMessage(errors, 'danger', scope.message, scope);
                                scope.hideErrors = false;
                                $anchorScroll(scope.message.attr('id'));
                            }).finally(function() {
                                waitingScreen.hide();
                            });
                        };

                        scope.cancel = function(e) {
                            e.preventDefault();
                            history.back();
                        };

                        scope.showErrors = function(e) {
                            e.preventDefault();
                            scope.hideErrors = true;
                        };

                        scope.delete = function(e) {
                            e.preventDefault();
                            if (!confirm('Are you sure?')) {
                                return false;
                            }

                            scope.template.delete();
                            scope.template.clear();
                            sessionMessages.addSuccess('Template was deleted');
                            history.back();
                        }
                    }
                }
            }
        ]);
})(settings);
