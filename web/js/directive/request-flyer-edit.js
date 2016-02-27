/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loRequestFlyerEdit',
    ['$location', 'createRequestFlyer', '$routeParams', 'parseGoogleAddressComponents', 'loadFile', '$timeout',
        'redirect', 'waitingScreen', 'getInfoFromGeocoder', 'loadImage', '$q', '$rootScope', 'sessionMessages',
        'pictureObject', 'createFromPropertyApproval', 'loadGoogleMapsApi', 'createDraftRequestFlyer', '$anchorScroll',
        'renderMessage', 'createProfileUser', 'createDraftFromPropertyApproval', '$http',
        function ($location, createRequestFlyer, $routeParams, parseGoogleAddressComponents, loadFile, $timeout,
                  redirect, waitingScreen, getInfoFromGeocoder, loadImage, $q, $rootScope, sessionMessages,
                  pictureObject, createFromPropertyApproval, loadGoogleMapsApi, createDraftRequestFlyer, $anchorScroll,
                  renderMessage, createProfileUser, createDraftFromPropertyApproval, $http)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/request-flyer-form.html',
        scope: {
            request: "=loRequest",
            titles: "=loTitles",
            officer: '=loOfficer',
            user: '=loUser'
        },
        link: function (scope, element, attrs, controllers) {
            scope.states = settings.queue.state;
            scope.realtorPicture = {};
            scope.propertyPicture = {};
            scope.realtyLogo = {};
            scope.oldRequest = {};
            scope.hideErrors = true;
            scope.container = angular.element("#errors");
            scope.realtorSelect = 'omit';

            // Select realtor
            scope.realtorSelect = 'omit';
            scope.realtorOptions = [
                {value: 'omit', name: 'Omit realtor information', type: 'Options'},
                {value: 'add', name: 'Add realtor', type: 'Options'}
            ];
            waitingScreen.show();
            $http.get('/request/flyer/realtors').then(function (response) {
                scope.realtorOptions = scope.realtorOptions.concat(
                    $.map(response.data.realtors, function (item) {
                        return {
                            name: item.first_name + ' ' + item.last_name,
                            value: item.id,
                            type: 'Select from existing realtors'
                        };
                    }));
                waitingScreen.hide();
            });
            scope.setRealtorData = function () {
                scope.request.realtor_id = (isNaN(scope.realtorSelect)) ? null : scope.realtorSelect;
                scope.request.property.omit_realtor_info = (scope.realtorSelect === 'omit') ? '1' : '0';
            };

            scope.$watch('request', function (newVal) {
                if (undefined == newVal || !("id" in newVal)) {
                    return;
                }

                if (newVal.realtor_id !== null && !isNaN(newVal.realtor_id)) {
                    scope.realtorSelect = newVal.realtor_id;
                }

                scope.realtorPicture = new pictureObject(
                    angular.element("#realtorImage"),
                    {
                        container: $(".realtor.realtor-photo > img"),
                        options: {aspectRatio: 3 / 4, minContainerWidth: 100}
                    },
                    scope.request.realtor
                );

                scope.propertyPicture = new pictureObject(
                    angular.element("#propertyImage"),
                    {container: $(".property-photo > img"), options: {aspectRatio: 3 / 2}},
                    scope.request.property
                );

                scope.realtyLogo = new pictureObject(
                    angular.element('#realtyLogo'),
                    {
                        container: $("#realtyLogoImage"), options: {
                        minCropBoxWidth: 100,
                        minCropBoxHeight: 100,
                        maxCropBoxWidth: 350,
                        maxCropBoxHeight: 100
                    }
                    },
                    scope.request.realtor,
                    'setRealtyLogo'
                );

                scope.oldRequest = angular.copy(scope.request);

                scope.$on('$locationChangeStart', function (event, next, current) {
                    if (!angular.equals(scope.oldRequest, scope.request)) {
                        var answer = confirm("Are you sure you want to leave without saving changes?");
                        if (!answer) {
                            event.preventDefault();
                        }
                    }
                });
            });

            $('[data-toggle="tooltip"]').tooltip();

            scope.cancel = function (e) {
                e.preventDefault();

                history.back();
            };

            scope.saveDraftOrApproved = function (e, form) {
                e.preventDefault();

                if (!form.$valid) {
                    this.gotoErrorMessage();
                    return false;
                }

                if (scope.request.property.omit_realtor_info === '1' && !confirm('Did you mean to omit realtor?')) {
                    return false;
                }

                if (scope.request.property.state != settings.queue.state.approved) {
                    scope.request.property.state = settings.queue.state.draft;
                }

                scope.requestDraft = this.request instanceof createFromPropertyApproval
                    ? (new createDraftFromPropertyApproval())
                    : (new createDraftRequestFlyer());

                scope.requestDraft.fill(scope.request.getFields4Save());
                scope.realtorPicture.setObjectImage(scope.requestDraft.realtor);
                scope.propertyPicture.setObjectImage(scope.requestDraft.property);

                scope.requestDraft.afterSave(function () {
                    sessionMessages.addSuccess("Successfully saved.");
                    scope.oldRequest = angular.copy(scope.request);
                    if ($rootScope.historyGet().indexOf('/request/success') != -1) {
                        redirect('/');
                    } else {
                        history.back();
                    }
                });

                this.saveRequest(scope.requestDraft);
            };

            scope.showErrors = function (e) {
                e.preventDefault();

                this.hideErrors = true;
            };

            scope.gotoErrorMessage = function () {
                scope.hideErrors = false;
                $anchorScroll(scope.container.attr("id"));
            };

            scope.save = function (form) {
                if (!form.$valid) {
                    this.gotoErrorMessage();
                    return false;
                }

                if (scope.request.property.omit_realtor_info === '1' && !confirm('Did you mean to omit realtor?')) {
                    return false;
                }

                scope.request.afterSave(function () {
                    scope.oldRequest = angular.copy(scope.request);
                    $rootScope.$broadcast('requestFlyerSaved', scope.request);
                });

                this.saveRequest(scope.request);
            };

            scope.saveRequest = function (request) {
                waitingScreen.show();
                scope.propertyPicture.prepareImage(2000, 649, 3000, 974);
                if (request.realtor.hasOwnProperty('photo') && request.realtor.photo !== null) {
                    scope.realtorPicture.prepareImage(800, 400, 600, 300);
                }


                request.save()
                    .catch(function (e) {
                        var messages = [];
                        messages.push('message' in e ? e.message : "We have some problems. Please try later.");

                        for (var i in e) {
                            if (e[i].constructor === Array) {
                                for (var j in e[i]) {
                                    if (e[i][j] != "") {
                                        messages.push(e[i][j]);
                                    }
                                }
                            }
                        }

                        if (messages.length > 0) {
                            renderMessage(messages.join(" "), "danger", scope.container, scope);
                            scope.gotoErrorMessage();
                        }

                        scope.realtorPicture.setObjectImage(scope.request.realtor);
                        scope.propertyPicture.setObjectImage(scope.request.property);
                    })
                    .finally(function () {
                        waitingScreen.hide();
                    })
                ;
            };

            scope.isAddressReadOnly = function () {

                if (typeof this.request.property === 'object' && this.request.property.state == settings.queue.state.approved) {
                    if (angular.isFunction(scope.user.isAdmin)) {
                        return !scope.user.isAdmin();
                    }
                    return true;
                }
                return this.request instanceof createFromPropertyApproval;
            };

            scope.clearAddress = function (e) {
                if (e.keyCode != 13) {
                    this.request.address.clear();
                }
            };

            loadGoogleMapsApi()
                .then(function () {
                    initialize();
                })
            ;

            var placeSearch, autocomplete;

            function initialize() {
                autocomplete = new google.maps.places.Autocomplete(
                    (document.getElementById('pac-input')),
                    {types: ['geocode']}
                );
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    fillInAddress();
                });
            }

            function fillInAddress() {
                var place = autocomplete.getPlace();
                scope.$apply(function () {
                    if ("address_components" in place) {
                        scope.request.address.set(parseGoogleAddressComponents(place.address_components));
                        scope.request.property.address = place.formatted_address;
                    } else {
                        scope.request.address.clear();
                    }
                });
            }
        }
    }
}]);
