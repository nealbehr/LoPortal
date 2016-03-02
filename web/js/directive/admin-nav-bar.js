/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive('loAdminNavBar', ['$location', 'Tab', function ($location, Tab) {
    return {
        restrict   : 'EA',
        templateUrl: 'template/directive/admin-nav-bar.html',
        link       : function (scope, element, attrs, controllers) {
            scope.tabs = [
                new Tab({
                    path: '/admin',
                    title: 'User Management'
                    // Hide add user button
                    // button_text: 'Add User',
                    // button_href: '/admin/user/new'
                }),
                new Tab({path: '/admin/queue', title: "Request Management"}),
                new Tab({
                    path: '/admin/lender',
                    title: "Lender",
                    button_text: "Add Lender",
                    button_href: "/admin/lender/new"
                }),
                new Tab({
                    path: '/admin/collateral',
                    title: 'Collateral',
                    button_text: 'Add Template',
                    button_href: '/admin/collateral/new'
                }),
                new Tab({
                    path: '/admin/realtor',
                    title: 'Realtor'
                }),
                new Tab({
                    path: '/admin/realty',
                    title: "Realty Company",
                    button_text: "Add Company",
                    button_href: "/admin/realty/new"
                }),
                new Tab({
                    path: '/admin/salesdirector',
                    title: 'Sales Director',
                    button_text: 'Add Sales Director',
                    button_href: '/admin/salesdirector/new'
                })
            ]
        }
    }
}]);
