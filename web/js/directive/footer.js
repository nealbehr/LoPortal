/**
 * Created by Eugene Lysenko on 2/26/16.
 */
angular.module('loApp').directive('loFooter', [function() {
    return {
        restrict   : 'EA',
        templateUrl: 'template/directive/footer.html',
        link       : function(scope, element, attrs, controllers) {

        }
    }
}]);
