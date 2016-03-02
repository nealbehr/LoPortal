/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminRealtorList',
    ['$http', '$location', 'tableHeadCol', 'waitingScreen', 'renderMessage', 'createRealtor',
        function ($http, $location, tableHeadCol, waitingScreen, renderMessage, createRealtor)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/admin-realtor-list.html',
        link: function (scope, element, attrs, controllers) {
            scope.pagination = {};
            scope.realtors = [];
            scope.isLoaded = false;
            scope.container = angular.element('#realtorMessage');
            scope.searchingString;
            scope.searchKey;

            scope.delete = function (e, key, val) {
                e.preventDefault();
                if (!confirm('Are you sure?')) {
                    return false;
                }

                waitingScreen.show();
                var realtor = createRealtor();
                realtor.id = val.id;
                realtor.delete().then(function () {
                    renderMessage('Realtor was deleted.', 'success', scope.container, scope);
                    scope.realtors.splice(key, 1);
                }).catch(function (data) {
                    renderMessage(data.message, 'danger', scope.container, scope);
                }).finally(function () {
                    waitingScreen.hide();
                });

                realtor = null;
            };

            waitingScreen.show();

            $http.get('/admin/realtor', {params: $location.search()}).success(function (data) {
                for (var i in data.realtors) {
                    scope.realtors.push(createRealtor().fill(data.realtors[i]));
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
                    new tableHeadCol(new params({key: 'photo', title: 'Photo', isSortable: false})),
                    new tableHeadCol(new params({key: 'first_name', title: 'First name', isSortable: true})),
                    new tableHeadCol(new params({key: 'last_name', title: 'Last name', isSortable: true})),
                    new tableHeadCol(new params({key: 'email', title: 'Email', isSortable: true})),
                    new tableHeadCol(new params({key: 'phone', title: 'Phone', isSortable: false})),
                    new tableHeadCol(new params({key: 'bre_number', title: 'BRE number', isSortable: false})),
                    new tableHeadCol(new params({key: 'created_at', title: 'Created', isSortable: true})),
                    new tableHeadCol(new params({key: 'action', title: 'Actions', isSortable: false}))
                ];
            }).finally(function () {
                scope.isLoaded = true;
                waitingScreen.hide();
            });
        }
    }
}]);
