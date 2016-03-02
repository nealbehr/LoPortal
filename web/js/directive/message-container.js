/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive('loMessageContainer', ['sessionMessages', '$compile', function(sessionMessages) {
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/session-messages.html',
        link: function(scope, elm, attrs, ctrl) {
            scope.$on('renderSessionMesages', function () {
                scope.messages = sessionMessages.get();
            });

            scope.messages = sessionMessages.get();
        }
    };
}]);
