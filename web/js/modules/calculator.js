/**
 * Created by zh-l on 29/01/16.
 */
(function(){
    'use strict';

    var module = angular.module('calculatorModule', []);

    module.config(['$routeProvider', function($routeProvider) {
        $routeProvider.when('/calculators', {
            templateUrl: '/partials/calculator',
            controller:  'CalculatorController',
            access: {
                isFree: false
            }
        });
    }]);

    module.controller('CalculatorController', function($scope) {
        $scope.titles = {
            header: 'Calculators'
        };
    });
})();
