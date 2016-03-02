/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminRequests', 
    ['$http', 'tableHeadCol', '$location', 'ngDialog', 'renderMessage', 'waitingScreen', 'sessionMessages', 
        function ($http, tableHeadCol, $location, ngDialog, renderMessage, waitingScreen, sessionMessages) 
{
    return {
        restrict   : 'EA',
        templateUrl: 'template/directive/admin-panel-requests.html',
        link       : function (scope, element, attrs, controllers) {
            scope.queue = [];
            scope.searchKey;
            scope.searchingString;
            scope.pagination = {};
            scope.messageContainer = angular.element("#messageContainer");
            scope.states = settings.queue.state;

            waitingScreen.show();
            $http.get('/admin/queue', {
                params: $location.search()
            })
                .success(function (data) {
                    scope.queue = data.queue;
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
                        new tableHeadCol(new params({key: "id", title: "Request ID"})),
                        new tableHeadCol(new params({key: "user_id", title: "User ID"})),
                        new tableHeadCol(new params({key: "address", title: "Property Address"})),
                        new tableHeadCol(new params({key: "mls_number", title: "MLS<br>Number"})),
                        new tableHeadCol(new params({key: "created_at", title: "Created", isSortable: true})),
                        new tableHeadCol(new params({key: "request_type", title: "Type"})),
                        new tableHeadCol(new params({key: "state", title: "Status"})),
                        new tableHeadCol(new params({key: "action", title: "Actions", isSortable: false}))
                    ];
                })
                .finally(function () {
                    waitingScreen.hide();
                })
            ;

            scope.getDialogByRequest = function (request) {
                return ngDialog.open({
                    template: (request.request_type == settings.queue.type.flyer 
                        ? 'template/directive/admin-request-approve-flyer.html'
                        : 'template/directive/admin-request-approve.html'),
                    showClose: false,
                    controller: (request.request_type == settings.queue.type.flyer 
                        ? 'adminApproveFlyerCtrl' 
                        : 'adminApproveCtrl'),
                    data: {
                        request: request
                    }
                });
            };

            scope.approve = function (e, request) {
                e.preventDefault();

                var dialog = this.getDialogByRequest(request);

                dialog.closePromise.then(function (data) {
                    if (data.value == undefined || data.value.state == undefined) {
                        return;
                    }

                    if (data.value.state == "success") {
                        request.state = data.value.requestState;
                        sessionMessages.addSuccess("Approved").render();
                        return;
                    }

                    renderMessage(data.value.message, data.value.state, scope.messageContainer, scope);
                });
            };

            scope.decline = function (e, request) {
                e.preventDefault();
                var dialog = ngDialog.open({
                    template: 'template/directive/admin-request-decline.html',
                    showClose: false,
                    controller: 'adminDiscardCtrl',
                    data: {
                        request: request
                    }
                });

                dialog.closePromise.then(function (data) {
                    if (data.value == undefined || data.value.state == undefined) {
                        return;
                    }

                    if (data.value.state == "success") {
                        request.state = settings.queue.state.declined;
                        sessionMessages.addSuccess("Declined").render();
                        return;
                    }

                    renderMessage(data.value.message, data.value.state, scope.messageContainer, scope);
                });
            };
        }
    }
}]);
