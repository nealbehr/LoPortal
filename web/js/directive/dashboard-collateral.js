/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'dashboardCollateral',
    ['createRequestFlyer', 'createDraftRequestFlyer', 'waitingScreen', '$http',
        function (createRequestFlyer, createDraftRequestFlyer, waitingScreen, $http)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/dashboard-collateral-row.html',
        scope: {
            items: '=loItems'
        },
        link: function (scope, el, attrs, ngModel) {
            scope.categories = [
                {
                    id: 0,
                    name: 'Listing Flyers',
                    items: []
                },
                {
                    id: 1,
                    name: 'Archive',
                    items: []
                }
            ];

            scope.$watch('items', function (newValue) {
                scope.items = newValue;
                for (var i in scope.items) {
                    if (scope.items[i].archive === '0') {
                        scope.categories[0].items.push(scope.items[i]);
                    }
                    else {
                        scope.categories[1].items.push(scope.items[i]);
                    }
                }
            });

            scope.archive = function (e, index, queue) {
                e.preventDefault();

                waitingScreen.show();

                $http.get('/request/' + queue.id).success(function (data) {
                    var flaer = (new createDraftRequestFlyer(queue.id)).fill(data);
                    if (queue.archive == '0') {
                        var to = scope.categories[0].items,
                            from = scope.categories[1].items;
                        queue.archive = flaer.property.archive = '1';
                    }
                    else {
                        var to = scope.categories[1].items,
                            from = scope.categories[0].items;
                        queue.archive = flaer.property.archive = '0';
                    }

                    flaer.update().finally(function () {
                        (to || []).splice(index, 1);
                        (from || []).push(queue);

                        waitingScreen.hide();
                    });
                });
            };
        }
    };
}]);