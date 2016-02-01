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

    module.controller('CalculatorController', ['$scope', function($scope) {
        $scope.title = {
            header  : 'Calculators',
            infoText: 'Use our calculators to determine how much extra home you can afford with REX, or see how much '
                +'you can reduce your monthly payment.'
        };
    }]);
})();
