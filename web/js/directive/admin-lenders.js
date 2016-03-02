/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminLenders',
    ['$http', 'tableHeadCol', '$location', "ngDialog", "renderMessage", "waitingScreen", 'createLender',
        function ($http, tableHeadCol, $location, ngDialog, renderMessage, waitingScreen, createLender)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/admin-panel-lenders.html',
        link: function (scope, element, attrs, controllers) {
            scope.lenders = [];
            scope.pagination = {};
            scope.messageContainer = angular.element("#messageContainer");

            scope.delete = function (e, key, lender) {
                e.preventDefault();
                if (!confirm("Are you sure?")) {
                    return false;
                }

                waitingScreen.show();

                lender.delete()
                    .then(function (data) {
                        renderMessage("Lender was deleted.", "success", scope.messageContainer, scope);
                        scope.lenders.splice(key, 1);
                    })
                    .catch(function (data) {
                        if ('message' in data) {
                            renderMessage(data.message, "danger", scope.messageContainer, scope);
                            scope.gotoErrorMessage();
                        }
                    })

                    .finally(function () {
                        waitingScreen.hide();
                    })
                ;
            };

            waitingScreen.show();
            $http.get('/admin/lender', {
                params: $location.search()
            })
                .success(function (data) {

                    scope.lenders = [];
                    for (var i in data.lenders) {
                        scope.lenders.push(createLender().fill(data.lenders[i]));
                    }
                    scope.searchKey = data.keySearch;
                    scope.pagination = data.pagination;
                    scope.searchingString = $location.search()[data.keySearch];

                    function params(settings) {
                        this.key = settings.key;
                        this.title = settings.title;
                    }

                    params.prototype.directionKey = data.keyDirection;
                    params.prototype.sortKey = data.keySort;
                    params.prototype.defaultDirection = data.defDirection;
                    params.prototype.defaultSortKey = data.defField;

                    scope.headParams = [
                        new tableHeadCol(new params({key: "id", title: "id"})),
                        new tableHeadCol(new params({key: "name", title: "Name"})),
                        new tableHeadCol(new params({key: "disclosure", title: "Disclosures", isSortable: false})),
                        new tableHeadCol(new params({key: "picture", title: "Logo", isSortable: false})),
                        new tableHeadCol(new params({key: "action", title: "Actions", isSortable: false}))
                    ];
                })
                .finally(function () {
                    waitingScreen.hide();
                });
        }
    }
}]);
