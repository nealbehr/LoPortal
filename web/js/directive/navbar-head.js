/**
 * Created by Eugene Lysenko on 2/26/16.
 */
angular.module('loApp').directive(
    'loNavbarHead',
    ['$http', '$cookieStore', 'redirect', 'TOKEN_KEY', 'userService', 'waitingScreen', 'Tab',
        function ($http, $cookieStore, redirect, TOKEN_KEY, userService, waitingScreen, Tab)
{
    return {
        restrict   : 'EA',
        templateUrl: 'template/directive/navbar-head.html',
        link       : function (scope, element, attrs, controllers) {
            scope.user = {};
            scope.isUserLoaded = false;
            scope.headerTabs = [
                new Tab({path: '/dashboard/collateral', title: 'Custom Collateral'}),
                new Tab({path: '/resources', title: 'Program Resources'}),
                new Tab({path: '/calculators', title: 'Calculators'}),
                new Tab({path: '/', title: 'New Property Prequalification'}),
                new Tab({path: '/flyer/new', title: 'New Listing Flyer'}),
                new Tab({path: '/dashboard/requests', title: 'Requests Queue'})
            ];

            userService.get().then(function (user) {
                scope.user = user;
                scope.isUserLoaded = true;
                scope.headerTabs.push(new Tab({path: '/user/' + user.id + '/edit', title: 'Edit profile'}));
                if (user.isAdmin()) {
                    scope.headerTabs.push(new Tab({path: '/admin', title: 'Admin panel'}));
                }
            });

            angular.element('.dropdown-toggle').click(function (e) {
                var target = $(e.target);
                if (target.is('a')) {
                    e.stopPropagation();
                }
            });

            scope.logout = function (e) {
                e.preventDefault();

                waitingScreen.show();

                $http.delete('/logout').success(function (data) {
                    $cookieStore.remove(TOKEN_KEY);
                    userService.get().then(function (user) {
                        user.clear();
                        redirect('/login');
                    });
                }).finally(function () {
                    scope.isUserLoaded = false;
                    waitingScreen.hide();
                });

                return false;
            }
        }
    }
}]);
