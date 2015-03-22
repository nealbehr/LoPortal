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

    helperService.filter('avatar', function(){
        return function(input){
            return input == null ? "images/ava.jpg": input;
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
//                alert('Please, reload game.');
               console.log(image + ' loading has been aborted.')
           }

           bgImg.onerror = function(){
               loadImages(images);
//                alert('Please, reload game.');
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