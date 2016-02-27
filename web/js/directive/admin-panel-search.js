/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive('loAdminPanelSearch', ["$location", function($location){
    return {
        restrict   : 'EA',
        templateUrl: 'template/directive/admin-panel-search.html',
        scope      : {
            searchKey      : "=loSearchKey",
            searchingString: "=loSearchingString"
        },
        link: function(scope, element, attrs, controllers){
            scope.search = function(){
                var locationParams = $location.search();
                if(this.searchingString == ""){
                    delete locationParams[scope.searchKey];
                }else{
                    locationParams[scope.searchKey] = scope.searchingString;
                }

                $location.search(locationParams);
            }
        }
    }
}]);
