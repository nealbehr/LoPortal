(function(settings) {
    'use strict';
    settings = settings || {};

    var PATH = '/admin/collateral';

    var module = angular.module('collateralModule', ['adminModule']);

    module.config(['$routeProvider', function($routeProvider) {
        $routeProvider.when(PATH, {
            templateUrl: '/partials/admin.collateral.tab',
            controller : 'collateralCtrl',
            access     : { isFree: false }
        }).when(PATH+'/new', {
            templateUrl: '/partials/admin.collateral',
            controller :  'collateralCtrl',
            access     : { isFree: false }
        });
    }]);

    module.controller('collateralCtrl', ['$scope', function($scope) {
    }]);

    module.directive(
        'loAdminCollateralForm',
        ['waitingScreen', 'renderMessage', 'sessionMessages', '$anchorScroll', 'pictureObject', '$http',
            function(waitingScreen, renderMessage, sessionMessages, $anchorScroll, pictureObject, $http)
            {
                return {
                    restrict   : 'EA',
                    templateUrl: '/partials/admin.collateral.form',
                    scope      : { realtor: '=loTemplate' },
                    link       : function(scope, element, attrs, controllers) {
                        scope.$watch('template.id', function(newVal, oldVal) {
                            scope.title = newVal? 'Edit Template': 'Add Template';
                        });

                        scope.cancel = function(e) {
                            e.preventDefault();
                            history.back();
                        };
                    }
                }
            }
        ]);
})(settings);
