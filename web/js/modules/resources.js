/**
 * Created by zh-l on 12/10/15.
 */
(function(){
    'use strict';

    var module = angular.module('resourcesModule', []);

    module.config(['$routeProvider', function($routeProvider) {
        $routeProvider.when('/resources', {
            templateUrl: '/partials/resources.list',
            controller:  'ResourcesController',
            access: {
                isFree: false
            }
        });
    }]);

    module.controller('ResourcesController', ['$scope', 'userService', function($scope, userService) {
        userService.get().then(function(data) {
            $scope.user = data;
        });

        // Mixpanel analytics
        $scope.logMixpanel = function() {
            mixpanel.identify($scope.user.id);
            mixpanel.track('Document Download');
        };

        $scope.titles = {
            header: 'Resources'
        };

        // Resources list
        $scope.resources = [
            {
                link : '/docs/resources/Brochure.pdf',
                title: 'Brochure'
            },
            {
                link : '/docs/resources/quick_reference_v7.pdf',
                title: 'Quick Reference Guide'
            },
            {
                link : '/docs/resources/RHB_at_a_glance.pdf',
                title: 'REX HomeBuyer Argeement'
            }
        ]
    }]);
})();