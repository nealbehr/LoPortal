/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminLenderInfo',
    ["redirect", "$http", "waitingScreen", "renderMessage", "getRoles", "$location", "$q", "sessionMessages",
        "$anchorScroll", "loadFile", "$timeout", "pictureObject", "USA_STATES",
        function (redirect, $http, waitingScreen, renderMessage, getRoles, $location, $q, sessionMessages,
                  $anchorScroll, loadFile, $timeout, pictureObject, USA_STATES)
        {
            return {
                restrict: 'EA',
                templateUrl: 'template/directive/admin-panel-lender-form.html',
                scope: {
                    lender: "=loLender"
                },
                link: function (scope, element, attrs, controllers) {

                    scope.container = angular.element('#lenderMessage');
                    scope.lenderPicture = {};
                    scope.hideErrors = true;
                    scope.usaStates = [];
                    scope.selectedDisclosure = angular.copy(USA_STATES, scope.usaStates);
                    scope.allStatesDisclosure = null;

                    scope.$watch('lender.id', function (newVal, oldVal) {
                        if (undefined != newVal && newVal == scope.lender.id) {
                            scope.title = "Edit Lender";
                            return;
                        }

                        scope.title = newVal ? 'Edit Lender' : 'Add Lender';
                    });

                    scope.$watch('lender', function (newVal, oldVal) {
                        if (newVal == undefined || !("id" in newVal)) {
                            return;
                        }

                        scope.initAllStatesDisclosure();
                        scope.updateStates();

                        scope.lenderPicture = new pictureObject(
                            angular.element('#lenderPhoto'),
                            {
                                container: $(".realtor-photo > img"), options: {
                                minCropBoxWidth: 100,
                                minCropBoxHeight: 100,
                                maxCropBoxWidth: 350,
                                maxCropBoxHeight: 100
                            }
                            },
                            scope.lender
                        );
                    });

                    scope.initAllStatesDisclosure = function () {
                        for (var i = 0; i < scope.lender.disclosures.length; i++) {
                            var disclosure = scope.lender.disclosures[i];
                            if (disclosure.state == 'US') {
                                scope.allStatesDisclosure = disclosure;
                                break;
                            }
                        }
                        if (scope.allStatesDisclosure == null) {
                            scope.allStatesDisclosure = scope.newDisclosure('US');
                            scope.lender.disclosures.push(scope.allStatesDisclosure);
                        }
                    };

                    scope.newDisclosure = function (state) {
                        return {
                            id: null,
                            state: state,
                            disclosure: '',
                            filled: false
                        }
                    };

                    scope.updateStates = function () {
                        for (var i = 0; i < scope.usaStates.length; i++) {
                            var state = scope.usaStates[i];
                            state.filled = false;
                            for (var j = 0; j < scope.lender.disclosures.length; j++) {
                                var disclosure = scope.lender.disclosures[j];
                                if (disclosure.state == state.code) {
                                    state.filled = true;
                                    break;
                                }
                            }
                        }
                    };

                    scope.showModal = function (e, state) {
                        e.preventDefault();
                        var found = false;
                        for (var i = 0; i < scope.lender.disclosures.length; i++) {
                            var disclosure = scope.lender.disclosures[i];
                            if (disclosure.state == state) {
                                scope.selectedDisclosure = disclosure;
                                scope.selectedDisclosure.filled = true;
                                found = true;
                                break;
                            }
                        }
                        if (!found) {
                            scope.selectedDisclosure = scope.newDisclosure(state);
                        }
                        $('#lender-state-disclosure').modal('show');
                    };

                    scope.confirmInModal = function (e) {
                        e.preventDefault();
                        if (scope.selectedDisclosure.id == null) {
                            scope.lender.disclosures.push(scope.selectedDisclosure);
                            scope.updateStates();
                        }
                        $('#lender-state-disclosure').modal('hide');
                    };

                    scope.deleteInModal = function (e) {
                        e.preventDefault();
                        var index = scope.lender.disclosures.indexOf(scope.selectedDisclosure);
                        if (index > -1) {
                            scope.lender.disclosures.splice(index, 1);
                        }
                        scope.updateStates();
                        $('#lender-state-disclosure').modal('hide');
                    };


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

                    scope.submit = function (formLender) {
                        if (!formLender.$valid) {
                            this.hideErrors = false;
                            this.gotoErrorMessage();
                            return false;
                        }
                        this.save();
                    };

                    scope.save = function () {

                        if (scope.lender.picture && scope.lender.picture.indexOf('http') !== 0) {
                            var isValid = scope.lenderPicture.validateNaturalSize(300, 300);
                            if (!isValid) {
                                renderMessage("Logos should be 300 px high and [300px - 1050] px wide", "danger", scope.container, scope);
                                return;
                            }
                            scope.lenderPicture.prepareFixedHeightImage(300);

                        } else if (scope.lender.id === null) {
                            renderMessage("Lender Logo is required", "danger", scope.container, scope);
                            return;
                        }

                        waitingScreen.show();

                        scope.lender.save()
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
                        scope.lender.delete().
                            then(function (data) {
                                sessionMessages.addSuccess("Lender was deleted.");
                                scope.lender.clear();
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
