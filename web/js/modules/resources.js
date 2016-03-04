/**
 * Created by Eugene Lysenko on 12/10/15.
 */
(function(){
    'use strict';

    var module = angular.module('resourcesModule', []);

    module.controller('ResourcesController', ['$scope', 'userService', function($scope, userService) {
        $scope.title = {
            header  : 'Program Resources',
            infoText: 'Learn more about REX HomeBuyer with our Brochure, Quick Reference and At A Glance flyer. Feel '
                +'free to print these out as a resource at your finger tips!'
        };

        userService.get().then(function(data) {
            $scope.user = data;
        });

        // Mixpanel analytics
        $scope.logMixpanel = function(name) {
            mixpanel.identify($scope.user.id);
            mixpanel.track('Download program reference', {'name': name});
        };

        // Resources list
        $scope.resources = [
            {
                link : '/docs/resources/Brochure.pdf',
                title: 'Brochure'
            },
            {
                link : '/docs/resources/RHB_Quick_Reference_2016.pdf',
                title: 'Quick Reference Guide'
            },
            {
                link : '/docs/resources/RHB_at_a_glance.pdf',
                title: 'At A Glance'
            }
        ]
    }]);
})();
