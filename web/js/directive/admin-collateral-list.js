/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminCollateralList',
    ['$http', '$location', 'waitingScreen', 'renderMessage', 'createTemplate',
        function ($http, $location, waitingScreen, renderMessage, createTemplate)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/admin-collateral-list.html',
        scope: {
            categories: '=loCategories',
            templates: '=loTemplates'
        },
        link: function (scope, element, attrs, controllers) {
            scope.archive = function (e, template) {
                e.preventDefault();

                waitingScreen.show();

                template.archive = template.archive == '0' ? '1' : '0';

                createTemplate().fill(template).update().then(function () {
                    $http.get('/admin/collateral').then(function (response) {
                        scope.templates = response.data;
                    }).finally(function () {
                        waitingScreen.hide();
                    });
                });
            };
        }
    }
}]);
