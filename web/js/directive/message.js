/**
 * Created by Eugene Lysenko on 2/26/16.
 */
angular.module('loApp').directive('loMessage', [function () {
    return {
        restrict   : 'EA',
        templateUrl: 'template/directive/message.html',
        scope      : {
            'loType': "@",
            'loBody': "@"
        },
        link: function (scope, element, attrs, controllers) {
            scope.isDanger = function () {
                return scope.loType == 'danger';
            };

            scope.$watch('loType', function (newValue) {
                scope.loType = newValue;
            });

            scope.$watch('loBody', function (newValue) {
                scope.loBody = newValue;
            });

        }
    }
}]);
