/**
 * Created by zh-l on 12/10/15.
 */
(function(){
    'use strict';

    var module = angular.module('resourcesModule', []);

    module.controller('ResourcesController', ['$scope', 'userService', function($scope, userService) {
        userService.get().then(function(data) {
            $scope.user = data;
        });

        // Mixpanel analytics
        $scope.logMixpanel = function(name) {
            mixpanel.identify($scope.user.id);
            mixpanel.track('Download program reference', {'name': name});
        };

        $scope.title = {
            header  : 'Program Resources',
            infoText: 'Learn more about REX HomeBuyer with our Brochure, Quick Reference and At A Glance flyer. Feel '
                +'free to print these out as a resource at your finger tips!'
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
