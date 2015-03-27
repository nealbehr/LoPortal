(function(settings){
    'use strict';
    settings = settings || {};

    var helperService = angular.module('helperService', []);

    helperService.run(['$templateCache', function($templateCache){
        $templateCache.put('message.html', "<div class=\"alert fade in\" style=\"z-index: 5;\" role=\"alert\" ng-class=\"{'alert-danger': isDanger(), 'alert-success': !isDanger()}\">" +
                                               "<a href=\"#\" class=\"close\"  data-dismiss=\"alert\" aria-label=\"Close\">close</a>" +
                                               "[[ loBody ]]" +
                                           "</div>");
    }]);

    helperService.directive('loMessage', [function(){
        return { restrict: 'EA',
            templateUrl: 'message.html',
            scope: {
                'loType':        "@",
                'loBody':        "@"
            },
            link: function(scope, element, attrs, controllers){
                scope.isDanger = function(){
                    return scope.loType == 'danger';
                }

                scope.$watch('loType', function (newValue) {
                    scope.loType = newValue;
                });

                scope.$watch('loBody', function (newValue) {
                    scope.loBody = newValue;
                });

            }
        }
    }]);

    helperService.directive('loNavbarHead', ['$http', '$cookieStore', 'redirect', 'TOKEN_KEY', function($http, $cookieStore, redirect, TOKEN_KEY){
        return { restrict: 'EA',
            templateUrl: '/partials/navbar.head',
            link: function(scope, element, attrs, controllers){
                scope.logout = function(e){
                    e.preventDefault();
                    $http.delete('/logout')
                        .success(function(data){
                            $cookieStore.remove(TOKEN_KEY)
                            redirect('/login');
                        })
                        .finally(function(){

                        })
                    ;

                    return false;
                }
            }
        }
    }]);

    helperService.directive('loDashboardRow', ['$timeout', function($timeout){
        return { restrict: 'EA',
            templateUrl: '/partials/dashboard.row',
            scope: {
                items: '=loItems',
                id:    '@loId',
                title: '@loTitle',
                isExpand: '=loIsExpand'
            },
            link: function(scope, element, attrs, controllers){
                scope.$watch('items', function (newValue) {
                    scope.items = newValue;

                    if(scope.isExpand){
                        $('#' + scope.id).collapse('show');
                    }
                });

                $timeout(function(){
                    angular.element("#" + scope.id + " > table").tablesorter();
                });
            }
        }
    }]);

    helperService.directive('validFile', function () {
        return {
            require: 'ngModel',
            restrict: 'E',
            link: function (scope, el, attrs, ngModel) {
                ngModel.$render = function () {
                    ngModel.$setViewValue(el.val());
                };

                el.bind('change', function () {
                    scope.$apply(function () {
                        ngModel.$render();
                    });
                });
            }
        };
    });


    helperService.filter('avatar', function(){
        return function(input){
            return input == null ? "images/ava.jpg": input;
        }
    });

    helperService.filter('fullName', function(){
        return function(user){
            return user.first_name + " " + user.last_name;
        }
    });

    helperService.filter('dashboardType', function(){
        return function(type){
            return type == 1? 'Listing Flyer': 'Property Approval';
        }
    });

    helperService.factory("redirect", ['$location', function($location){
        return function(path, redirectUrl){
            if(undefined != redirectUrl){
                $location.search('redirect_url', redirectUrl);
                $location.path(path);

                return;
            }

            var redirectUrl = $location.search().redirect_url;
            if(undefined != redirectUrl){
                $location.search('redirect_url', null);
                $location.path(redirectUrl);

                return;
            }

            $location.path(path);
        }
    }]);

    helperService.factory("loadImages", ["$q", function($q){
        var deferred = $q.defer();
        function loadImages(images){
           var image = images.shift()
           if(image == undefined){
               deferred.resolve();

               return;
           }

           var bgImg = new Image();
           bgImg.onload = function(){
               loadImages(images);
           };

           bgImg.onabort = function(){
               loadImages(images);
               console.log(image + ' loading has been aborted.')
           }

           bgImg.onerror = function(){
               loadImages(images);
               console.log(image + ' error loading.')
           }

           bgImg.src = image;
        }

        return function(images){
            loadImages(images);

            return deferred.promise;
        };
    }]);
})(settings);