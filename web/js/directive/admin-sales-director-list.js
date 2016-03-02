/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminSalesDirectorList',
    ['$http', '$location', 'tableHeadCol', 'waitingScreen', 'renderMessage', 'createSalesDirector',
        function ($http, $location, tableHeadCol, waitingScreen, renderMessage, createSalesDirector)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/admin-sales-director-list.html',
        link: function (scope, element, attrs, controllers) {
            scope.pagination = {};
            scope.salesDirectors = [];
            scope.isLoaded = false;
            scope.container = angular.element('#salesDirectorMessage');
            scope.searchingString;
            scope.searchKey;

            scope.delete = function (e, key, val) {
                e.preventDefault();
                if (!confirm('Are you sure?')) {
                    return false;
                }

                waitingScreen.show();
                var salesDirector = createSalesDirector();
                salesDirector.id = val.id;
                salesDirector.delete().then(function () {
                    renderMessage('Sales director was deleted.', 'success', scope.container, scope);
                    scope.salesDirectors.splice(key, 1);
                }).catch(function (data) {
                    renderMessage(data.message, 'danger', scope.container, scope);
                }).finally(function () {
                    waitingScreen.hide();
                });

                salesDirector = null;
            };

            waitingScreen.show();

            $http.get('/admin/salesdirector', {params: $location.search()}).success(function (data) {
                for (var i in data.salesDirectors) {
                    scope.salesDirectors.push(createSalesDirector().fill(data.salesDirectors[i]));
                }

                scope.pagination = data.pagination;
                scope.searchingString = $location.search()[data.keySearch];
                scope.searchKey = data.keySearch;

                function params(settings) {
                    this.key = settings.key;
                    this.title = settings.title;
                }

                params.prototype.directionKey = data.keyDirection;
                params.prototype.sortKey = data.keySort;
                params.prototype.defaultDirection = data.defDirection;
                params.prototype.defaultSortKey = data.defField;

                scope.headParams = [
                    new tableHeadCol(new params({key: 'id', title: 'id', isSortable: true})),
                    new tableHeadCol(new params({key: 'name', title: 'Name', isSortable: true})),
                    new tableHeadCol(new params({key: 'email', title: 'Email', isSortable: true})),
                    new tableHeadCol(new params({key: 'phone', title: 'Phone', isSortable: false})),
                    new tableHeadCol(new params({key: 'created_at', title: 'Created', isSortable: true})),
                    new tableHeadCol(new params({key: 'action', title: 'Actions', isSortable: false}))
                ];
            }).finally(function () {
                scope.isLoaded = true;
                waitingScreen.hide();
            });
        }
    }
}
]);
