/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loDashboardCollateralList',
    ['$http', '$location', 'waitingScreen', 'renderMessage', 'createTemplate',
        function($http, $location, waitingScreen, renderMessage, createTemplate)
{
    return {
        restrict   : 'EA',
        templateUrl: 'template/directive/dashboard-template-list.html',
        scope      : {
            categories: '=loCategories',
            templates : '=loTemplates'
        },
        link: function (scope, element, attrs, controllers) {
            scope.PATH  = '/dashboard';
            scope.title = {
                header  : 'Custom Collateral',
                infoText: 'Download one of our flyers from our Consumer, Real Estate Agent or Listing '
                    +'Flyer section. These are customized with your photo and contact information.'
            };
        }
    }
}]);
