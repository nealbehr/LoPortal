/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminUsers',
    ['$http', 'getRoles', 'getLenders', '$location', 'tableHeadCol', 'waitingScreen', 'createUser', 'renderMessage',
        '$q',
        function ($http, getRoles, getLenders, $location, tableHeadCol, waitingScreen, createUser, renderMessage, $q)
{
    return {
        restrict   : 'EA',
        templateUrl: 'template/directive/admin-panel-users.html',
        link       : function (scope, element, attrs, controllers) {
            scope.pagination = {};
            scope.users = [];
            scope.roles = {};
            scope.lenders = [];
            scope.searchingString;
            scope.isLoaded = false;
            scope.searchKey;
            scope.container = angular.element("#userMessage");
            scope.syncLog;

            scope.syncWithBase = function (e) {
                e.preventDefault();

                waitingScreen.show();
                $http.get('/admin/user-sync').success(function (data) {
                    scope.syncLog = data;

                    // Update user list
                    scope.getUsers().then(function (data) {
                        scope.users = [];
                        for (var i in data.users) {
                            scope.users.push(createUser().fill(data.users[i]));
                        }
                    });

                }).finally(function () {
                    waitingScreen.hide();
                });
            };

            scope.getUsers = function () {
                var deferred = $q.defer();
                waitingScreen.show();
                $http.get('/admin/user', {
                    params: $location.search()
                }).success(function (data) {
                    return deferred.resolve(data);
                }).finally(function () {
                    waitingScreen.hide();
                });

                return deferred.promise;
            };

            getRoles()
                .then(function (data) {
                    scope.roles = data;
                })
                .then(function () {
                    scope.lenders = getLenders();
                })
                .then(function () {
                    return scope.getUsers();
                })
                .then(function (data) {
                    scope.pagination = data.pagination;
                    scope.users = [];
                    for (var i in data.users) {
                        scope.users.push(createUser().fill(data.users[i]));
                    }
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
                        new tableHeadCol(new params({key: "id", title: "id"})),
                        new tableHeadCol(new params({key: "first_name", title: "First<br>Name"})),
                        new tableHeadCol(new params({key: "last_name", title: "Last<br>Name"})),
                        new tableHeadCol(new params({key: "email", title: "Email", isSortable: true})),
                        new tableHeadCol(new params({key: "password", title: "Password", isSortable: false})),
                        new tableHeadCol(new params({key: "role", title: "Role", isSortable: false})),
                        new tableHeadCol(new params({key: "title", title: "Title", isSortable: false})),
                        new tableHeadCol(new params({
                            key: "phone",
                            title: "Primary<br>Phone",
                            isSortable: false
                        })),
                        new tableHeadCol(new params({
                            key: "mobile",
                            title: "Mobile<br>Phone",
                            isSortable: false
                        })),
                        new tableHeadCol(new params({key: "created_at", title: "Created", isSortable: true})),
                        new tableHeadCol(new params({key: "action", title: "Actions", isSortable: false}))
                    ];
                })
                .finally(function () {
                    scope.isLoaded = true;
                })
            ;

            scope.delete = function (e, key, user) {
                e.preventDefault();
                if (!confirm("Are you sure?")) {
                    return false;
                }

                waitingScreen.show();


                user.delete().then(function () {
                    renderMessage("User was deleted.", "success", scope.container, scope);
                    scope.users.splice(key, 1);
                })
                    .finally(function () {
                        waitingScreen.hide();
                    })
                ;
            };

            scope.resetPassword = function (e, user) {
                e.preventDefault();
                waitingScreen.show();

                user.resetPassword().then(function () {
                    renderMessage("Password has been reset.", "success", scope.container, scope);
                })
                    .catch(function (data) {
                        var message = "message" in data ? data.message : data;
                        renderMessage(message, "danger", scope.container, scope);
                    })
                    .finally(function () {
                        waitingScreen.hide();
                    })
                ;
            }
        }
    }
}]);
