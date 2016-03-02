/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminRealtyCompanies',
    ['$http', 'tableHeadCol', '$location', "ngDialog", "renderMessage", "waitingScreen", 'createRealtyCompany',
        function($http, tableHeadCol, $location, ngDialog, renderMessage, waitingScreen, createRealtyCompany)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/admin-panel-realty-companies.html',
        link: function(scope, element, attrs, controllers){
            scope.comanies = [];
            scope.pagination = {};
            scope.messageContainer = angular.element("#messageContainer");

            scope.delete = function(e, key, company) {
                e.preventDefault();
                if(!confirm("Are you sure?")){
                    return false;
                }

                waitingScreen.show();

                company.delete()
                    .then(function(data) {
                        renderMessage("Realty company was deleted.", "success", scope.messageContainer, scope);
                        scope.companies.splice(key, 1);
                    })
                    .catch(function(data) {
                        if('message' in data){
                            renderMessage(data.message, "danger", scope.messageContainer, scope);
                            scope.gotoErrorMessage();
                        }
                    })

                    .finally(function(){
                        waitingScreen.hide();
                    })
                ;
            };

            waitingScreen.show();
            $http.get('/admin/realty', {
                params: $location.search()
            }).success(function(data){

                scope.companies = [];
                for(var i in data.companies){
                    scope.companies.push(createRealtyCompany().fill(data.companies[i]));
                }
                scope.searchKey = data.keySearch;
                scope.pagination = data.pagination;
                scope.searchingString = $location.search()[data.keySearch];

                function params(settings){
                    this.key   = settings.key;
                    this.title = settings.title;
                }

                params.prototype.directionKey     = data.keyDirection;
                params.prototype.sortKey          = data.keySort;
                params.prototype.defaultDirection = data.defDirection;
                params.prototype.defaultSortKey   = data.defField;

                scope.headParams = [
                    new tableHeadCol(new params({key: "id", title: "id"})),
                    new tableHeadCol(new params({key: "name", title: "Company Name"})),
                    new tableHeadCol(new params({key: "logo", title: "Company Logo", isSortable: false})),
                    new tableHeadCol(new params({key: "action", title: "Actions", isSortable: false}))
                ];

            }).finally(function() {
                waitingScreen.hide();
            });

        }
    }
}]);
