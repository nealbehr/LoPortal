/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive('loAdminPanelPagination', ["$location", function ($location) {
    return {
        restrict   : 'EA',
        templateUrl: 'template/directive/admin-pagination.html',
        scope      : {
            pagination: '=loPagination'
        },
        link: function (scope, element, attrs, controllers) {
            scope.getUrl = function (isNext) {
                return '/#'+$location.path()+'?'
                    +this.getParams(isNext ? scope.pagination.next : scope.pagination.previous);
            };

            scope.getParams = function (page) {
                var params = angular.copy($location.search());
                if (page) {
                    params.page = page;
                }

                return $.param(params);
            }
        }
    }
}]);
