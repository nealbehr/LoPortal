/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loUserInfo',
    ["redirect", "userService", "$http", "waitingScreen", "renderMessage", "getRoles", "getLenders", "$location", "$q",
        "sessionMessages", "$anchorScroll", "loadFile", "$timeout", "pictureObject",
        function (redirect, userService, $http, waitingScreen, renderMessage, getRoles, getLenders, $location, $q,
                  sessionMessages, $anchorScroll, loadFile, $timeout, pictureObject)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/user-info.html',
        scope: {
            officer: "=loOfficer"
        },
        link: function (scope, element, attrs, controllers) {
            scope.roles = [];
            scope.lenders = [];
            scope.selected = {};
            scope.selectedLender = {};
            scope.masterUserData = {};
            scope.user = {};
            scope.container = angular.element('#userProfileMessage');
            scope.userPicture = {};
            scope.hideErrors = true;
            scope.title = {
                header: 'Edit Profile',
                infoText: 'Change your information and photo here if needed. This will also automatically update '
                + 'your customized collateral.'
            };

            scope.$watch('officer', function (newVal, oldVal) {
                if (newVal == undefined) {
                    return;
                }
                scope.userPicture = new pictureObject(
                    angular.element('#userPhoto'),
                    {container: $(".realtor-photo > img"), options: {aspectRatio: 3 / 4, minContainerWidth: 100}},
                    scope.officer
                );
            });

            scope.itsMe = function () {
                return this.officer.id && this.user.id == this.officer.id;
            };

            scope.resetUserData = function () {
                angular.copy(scope.masterUserData, scope.user);
            };

            scope.cancel = function (e) {
                e.preventDefault();
                scope.resetUserData();
                history.back();
            };

            scope.autoComplete = function (event) {
                var element = $(event.target);

                element.autocomplete({
                    source: function (request, response) {
                        $http.get(
                            '/admin/salesdirector',
                            {
                                params: {
                                    'filterValue': element.val().toLowerCase(),
                                    'searchBy': 'name'
                                },
                                cache: true
                            }
                        ).then(function (resp) {
                                response($.map(resp.data.salesDirectors, function (item) {
                                    return {
                                        label: item.name,
                                        value: item.name,
                                        salesDirector: item
                                    };
                                }));
                            });
                    },
                    minLength: 0,
                    delay: 500,
                    select: function (event, ui) {
                        if (ui.item !== undefined) {
                            scope.officer.sales_director = ui.item.value;
                            scope.officer.sales_director_phone = ui.item.salesDirector.phone;
                            scope.officer.sales_director_email = ui.item.salesDirector.email;
                            scope.$apply();
                        }
                        return false;
                    }
                }).autocomplete('search', element.val().toLowerCase());
            };

            userService.get()
                .then(function (user) {
                    scope.masterUserData = angular.copy(user);
                    scope.user = user;

                    return scope.user.isAdmin() && scope.roles.length == 0 ? getRoles() : $q.when({});
                })
                .then(function (data) {
                    scope.roles = [];
                    for (var i in data) {
                        scope.roles.push({'title': i, key: data[i]});
                    }

                    if (scope.roles.length == 0) {
                        return;
                    }

                    if (!scope.selected.key) {
                        scope.selected = scope.roles[0];
                    }
                })
                .then(function () {
                    if (scope.user.isAdmin() && scope.lenders.length == 0) {
                        getLenders(true).then(function (data) {
                            scope.lenders = data;
                            if (scope.officer && !scope.officer.lender) {
                                scope.officer.lender = scope.lenders[0];
                            }
                        })

                    }
                })
            ;

            scope.$watch('officer', function (newVal) {
                scope.setSelected(newVal);
            });
            scope.$watch('roles', function (newVal) {
                scope.setSelected(newVal);
            });

            scope.isValidEmail = function (form) {
                if (!form.email) {
                    return;
                }

                return (form.$submitted || form.email.$touched) && (form.email.$error.email || form.email.$error.required);
            };

            scope.showErrors = function (e) {
                e.preventDefault();

                this.hideErrors = true;
            };

            scope.gotoErrorMessage = function () {
                $anchorScroll(scope.container.attr("id"));
            };

            scope.submit = function (formUser, $event) {
                if (!formUser.$valid) {
                    this.hideErrors = false;
                    this.gotoErrorMessage();
                    return false;
                }
                this.save();
            };

            scope.delete = function (e) {
                e.preventDefault();

                if (!confirm("Are you sure?")) {
                    return false;
                }

                waitingScreen.show();
                scope.officer.delete().
                    then(function (data) {
                        sessionMessages.addSuccess("User was deleted.");
                        scope.officer.clear();
                        history.back();
                    })
                    .catch(function (data) {
                        if ('message' in data) {
                            renderMessage(data.message, "danger", scope.container, scope);
                            scope.gotoErrorMessage();
                        }
                    })
                    .finally(function () {
                        waitingScreen.hide();
                    });
            };

            scope.save = function () {
                waitingScreen.show();
                scope.officer.roles = [scope.selected.key];

                if (scope.officer.picture) {
                    scope.userPicture.prepareImage(800, 400, 600, 300);
                }

                scope.officer.save()
                    .then(function (data) {
                        if (scope.user.id == scope.officer.id) {
                            scope.user.fill(scope.officer.getFields4Save());
                        }
                        sessionMessages.addSuccess("Successfully saved.");
                        history.back();
                    })
                    .catch(function (data) {
                        var errors = "";
                        if ("message" in data) {
                            errors = data.message + " ";
                        }

                        if ("form_errors" in data) {
                            errors += data.form_errors.join(" ");
                        }

                        renderMessage(errors, "danger", scope.container, scope);
                        scope.gotoErrorMessage();
                    })
                    .finally(function () {
                        waitingScreen.hide();
                    }
                );
            };

            scope.setSelected = function (newVal) {
                if (!newVal || !scope.officer || !scope.officer.roles || scope.roles.length < 1) {
                    return;
                }

                var officerRole;
                for (var i in scope.officer.roles) {
                    officerRole = scope.officer.roles[i];
                    break;
                }

                for (var i in scope.roles) {
                    if (scope.roles[i].key == officerRole) {
                        scope.selected = scope.roles[i];
                        break;
                    }
                }
            }

        }
    }
}]);
