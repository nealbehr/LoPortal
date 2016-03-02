/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loDashboardRow',
    ['$timeout', 'tableHeadColSample',
        function ($timeout, tableHeadColSample)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/dashboard-row.html',
        scope: {
            items   : '=loItems',
            id      : '@loId',
            title   : '@loTitle',
            isExpand: '=loIsExpand',
            state   : "@loState"
        },
        link: function (scope, element, attrs, controllers) {
            scope.requestType = settings.queue.type;
            function params(key, title) {
                this.key = key;
                this.title = title;
            }

            params.prototype.directionKey = 'reverse';
            params.prototype.scope = scope;
            params.prototype.sortKey = 'predicate';
            params.prototype.defaultDirection = true;
            params.prototype.defaultSortKey = 'created_at';


            scope.headParams = [
                new tableHeadColSample(new params("created_at.date", "Created")),
                new tableHeadColSample(new params("address", "Property Address")),
                new tableHeadColSample(new params("request_type", "Type"))
            ];

            scope.predicate = scope.headParams[0].key;
            scope.reverse = true;

            $timeout(function () {
//                    angular.element("#" + scope.id + " > table").tablesorter();
            });

            scope.$watch("isExpand", function (newValue) {
                if (newValue) {
                    $('#' + scope.id).collapse('show');
                }
            });

            scope.isApprovedProperty = function (item) {
                return item.request_type == this.requestType.propertyApproval && item.state == settings.queue.state.approved;
            };
            scope.isApprovedFlyer = function (item) {
                return item.request_type == this.requestType.flyer && item.state == settings.queue.state.approved
            };
            scope.canCancel = function (item) {
                return item.state == settings.queue.state.requested || item.state == settings.queue.state.draft;
            };
            scope.isComplete = function (item) {
                return item.state == settings.queue.state.draft;
            };
            scope.isDeclined = function (item) {
                return item.state == settings.queue.state.declined;
            };
        }
    }
}]);
