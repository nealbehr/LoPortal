/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminRealtyCompanyInfo',
    ["redirect", "$http", "waitingScreen", "renderMessage", "getRoles", "$location", "$q", "sessionMessages",
        "$anchorScroll", "loadFile", "$timeout", "pictureObject",
        function (redirect, $http, waitingScreen, renderMessage, getRoles, $location, $q, sessionMessages,
                  $anchorScroll, loadFile, $timeout, pictureObject)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/admin-panel-realty-form.html',
        scope: {
            company: "=loCompany"
        },
        link: function (scope, element, attrs, controllers) {

            scope.container = angular.element('#companyMessage');
            scope.realtyLogo = {};
            scope.hideErrors = true;

            scope.$watch('company.id', function (newVal, oldVal) {
                if (undefined != newVal && newVal == scope.company.id) {
                    scope.title = "Edit Realty Company";
                    return;
                }

                scope.title = newVal ? 'Edit Realty Company' : 'Add Realty Company';
            });

            scope.$watch('company', function (newVal, oldVal) {
                if (newVal == undefined || !("id" in newVal)) {
                    return;
                }

                scope.realtyLogo = new pictureObject(
                    angular.element('#realtyLogo'),
                    {
                        container: $(".realty-logo-photo > img"), options: {
                        minCropBoxWidth: 100,
                        minCropBoxHeight: 100,
                        maxCropBoxWidth: 350,
                        maxCropBoxHeight: 100
                    }
                    },
                    scope.company
                );
            });

            scope.cancel = function (e) {
                e.preventDefault();
                history.back();
            };

            scope.showErrors = function (e) {
                e.preventDefault();
                this.hideErrors = true;
            };

            scope.gotoErrorMessage = function () {
                $anchorScroll(scope.container.attr("id"));
            };

            scope.submit = function (formRealty) {
                if (!formRealty.$valid) {
                    this.hideErrors = false;
                    this.gotoErrorMessage();
                    return false;
                }
                this.save();
            };

            scope.save = function () {

                if (scope.company.logo && scope.company.logo.indexOf('http') !== 0) {
                    var isValid = scope.realtyLogo.validateNaturalSize(300, 300);
                    if (!isValid) {
                        renderMessage("Logos should be 300 px high and [300px - 1050] px wide", "danger", scope.container, scope);
                        return;
                    }
                    scope.realtyLogo.prepareFixedHeightImage(300);

                } else if (scope.company.id === null) {
                    renderMessage("Realty Logo is required", "danger", scope.container, scope);
                    return;
                }

                waitingScreen.show();
                scope.company.save()
                    .then(function (data) {
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

            scope.delete = function (e) {
                e.preventDefault();

                if (!confirm("Are you sure?")) {
                    return false;
                }

                waitingScreen.show();
                scope.company.delete().
                    then(function (data) {
                        sessionMessages.addSuccess("Realty Company was deleted.");
                        scope.company.clear();
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
            }
        }
    }
}]);
