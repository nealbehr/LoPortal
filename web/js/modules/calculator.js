/**
 * Created by zh-l on 29/01/16.
 */
(function(){
    'use strict';

    var module = angular.module('calculatorModule', []);

    module.controller('CalculatorController', ['$scope', function($scope) {
        $scope.title = {
            header  : 'Calculators',
            infoText: 'Use our calculators to determine how much extra home you can afford with REX, or see how much '
                +'you can reduce your monthly payment.'
        };
    }]);
})();
