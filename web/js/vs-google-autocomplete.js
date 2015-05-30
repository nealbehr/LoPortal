(function () {
    "use strict";

    var googleAutoCompleteModule = angular.module('googleAutoCompleteModule', []);

    googleAutoCompleteModule.factory('vsGooglePlaceUtility', function () {
        function isGooglePlace(place) {
            if (!place)
                return false;
            return !!place.place_id;
        }

        function isContainTypes(place, types) {
            var placeTypes,
                placeType,
                type;
            if (!isGooglePlace(place))
                return false;
            placeTypes = place.types;
            for (var i = 0; i < types.length; i++) {
                type = types[i];
                for (var j = 0; j < placeTypes.length; j++) {
                    placeType = placeTypes[j];
                    if (placeType === type) {
                        return true;
                    }
                }
            }
            return false;
        }

        function getAddrComponent(place, componentTemplate) {
            var result;
            if (!isGooglePlace(place))
                return;
            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                if (componentTemplate[addressType]) {
                    result = place.address_components[i][componentTemplate[addressType]];
                    return result;
                }
            }
            return;
        }

        function getPlaceId(place) {
            if (!isGooglePlace(place))
                return;
            return place.place_id;
        }

        function getStreetNumber(place) {
            var COMPONENT_TEMPLATE = { street_number: 'short_name' },
                streetNumber = getAddrComponent(place, COMPONENT_TEMPLATE);
            return streetNumber;
        }

        function getStreet(place) {
            var COMPONENT_TEMPLATE = { route: 'long_name' },
                street = getAddrComponent(place, COMPONENT_TEMPLATE);
            return street;
        }

        function getCity(place) {
            var COMPONENT_TEMPLATE = { locality: 'long_name' },
                city = getAddrComponent(place, COMPONENT_TEMPLATE);
            return city;
        }

        function getState(place) {
            var COMPONENT_TEMPLATE = { administrative_area_level_1: 'short_name' },
                state = getAddrComponent(place, COMPONENT_TEMPLATE);
            return state;
        }

        function getCountryShort(place) {
            var COMPONENT_TEMPLATE = { country: 'short_name' },
                countryShort = getAddrComponent(place, COMPONENT_TEMPLATE);
            return countryShort;
        }

        function getPostalCode(place) {
            var COMPONENT_TEMPLATE = { postal_code: 'long_name' },
                postal_code = getAddrComponent(place, COMPONENT_TEMPLATE);
            return postal_code;
        }

        function getCountry(place) {
            var COMPONENT_TEMPLATE = { country: 'long_name' },
                country = getAddrComponent(place, COMPONENT_TEMPLATE);
            return country;
        }

        function isGeometryExist(place) {
            return angular.isObject(place) && angular.isObject(place.geometry);
        }

        function getLatitude(place) {
            if (!isGeometryExist(place)) return;
            return place.geometry.location.A;
        }

        function getLongitude(place) {
            if (!isGeometryExist(place)) return;
            return place.geometry.location.F;
        }

        return {
            isGooglePlace: isGooglePlace,
            isContainTypes: isContainTypes,
            getPlaceId: getPlaceId,
            getStreetNumber: getStreetNumber,
            getStreet: getStreet,
            getCity: getCity,
            getState: getState,
            getCountryShort: getCountryShort,
            getCountry: getCountry,
            getPostalCode: getPostalCode,
            getLatitude: getLatitude,
            getLongitude: getLongitude
        };
    });

    googleAutoCompleteModule.directive('vsGoogleAutocomplete', ['vsGooglePlaceUtility', '$timeout', function (vsGooglePlaceUtility, $timeout) {
        return {
            restrict: 'A',
            require: ['vsGoogleAutocomplete', 'ngModel'],
            scope: {
                vsGoogleAutocomplete: '=',
                vsPlace: '=?',
                vsPlaceId: '=?',
                vsStreetNumber: '=?',
                vsStreet: '=?',
                vsCity: '=?',
                vsState: '=?',
                vsCountryShort: '=?',
                vsCountry: '=?',
                vsPostalCode: '=?',
                vsLatitude: '=?',
                vsLongitude: '=?'
            },
            controller: ['$scope', '$attrs', function ($scope, $attrs) {
                this.isolatedScope = $scope;

                /**
                 * Updates address components associated with scope model.
                 * @param {google.maps.places.PlaceResult} place PlaceResult object
                 */
                this.updatePlaceComponents = function (place) {
                    $scope.vsPlaceId = !!$attrs.vsPlaceId && place ? vsGooglePlaceUtility.getPlaceId(place) : undefined;
                    $scope.vsStreetNumber = !!$attrs.vsStreetNumber && place ? vsGooglePlaceUtility.getStreetNumber(place) : undefined;
                    $scope.vsStreet = !!$attrs.vsStreet && place ? vsGooglePlaceUtility.getStreet(place) : undefined;
                    $scope.vsCity = !!$attrs.vsCity && place ? vsGooglePlaceUtility.getCity(place) : undefined;
                    $scope.vsState = !!$attrs.vsState && place ? vsGooglePlaceUtility.getState(place) : undefined;
                    $scope.vsCountryShort = !!$attrs.vsCountryShort && place ? vsGooglePlaceUtility.getCountryShort(place) : undefined;
                    $scope.vsCountry = !!$attrs.vsCountry && place ? vsGooglePlaceUtility.getCountry(place) : undefined;
                    $scope.vsPostalCode = !!$attrs.vsPostalCode && place ? vsGooglePlaceUtility.getPostalCode(place) : undefined;
                    $scope.vsLatitude = !!$attrs.vsLatitude && place ? vsGooglePlaceUtility.getLatitude(place) : undefined;
                    $scope.vsLongitude = !!$attrs.vsLongitude && place ? vsGooglePlaceUtility.getLongitude(place) : undefined;
                };
            }],
            link: function (scope, element, attrs, ctrls) {

                // controllers
                var autocompleteCtrl = ctrls[0],
                    modelCtrl = ctrls[1];

                // google.maps.places.Autocomplete instance (support google.maps.places.AutocompleteOptions)
                var autocompleteOptions = scope.vsGoogleAutocomplete || {},
                    autocomplete = new google.maps.places.Autocomplete(element[0], autocompleteOptions);

                // google place object
                var place;

                // value for updating view
                var viewValue;

                // loading place by id for validation of already saved items
                if (scope.vsPlaceId !== undefined && scope.vsPlaceId !== null) {
                    console.log("vsPlaceId: " + scope.vsPlaceId);
                    var mapCanvas = document.getElementById('map-canvas');
                    var map = new google.maps.Map(mapCanvas, {
                        center: new google.maps.LatLng(-33.8665433, 151.1956316),
                        zoom: 15
                    });
                    var service = new google.maps.places.PlacesService(map);
                    var request = {
                        placeId: scope.vsPlaceId
                    };
                    service.getDetails(request, function (mapPlace, status) {
                        if (status == google.maps.places.PlacesServiceStatus.OK) {
                            place = mapPlace;
                            console.log("place: " + place.formatted_address);
                            viewValue = place.formatted_address || modelCtrl.$viewValue;
                            scope.$apply(function () {
                                scope.vsPlace = place;
                                autocompleteCtrl.updatePlaceComponents(place);
                                modelCtrl.$setViewValue(viewValue);
                                modelCtrl.$render();
                            });
                        }
                    });
                } else {
                    // reset undefined address
                    scope.vsPlace = null;
                }

                // updates view value and address components on place_changed google api event
                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    place = autocomplete.getPlace();
                    viewValue = place.formatted_address || modelCtrl.$viewValue;
                    scope.$apply(function () {
                        scope.vsPlace = place;
                        autocompleteCtrl.updatePlaceComponents(place);
                        modelCtrl.$setViewValue(viewValue);
                        modelCtrl.$render();
                    });
                });

                // updates view value on focusout
                element.on('blur', function (event) {
                    viewValue = (place && place.formatted_address) ? viewValue : modelCtrl.$viewValue;
                    $timeout(function () {
                        scope.$apply(function () {
                            modelCtrl.$setViewValue(viewValue);
                            modelCtrl.$render();
                        });
                    });
                });
                // prevent submitting form on enter
                google.maps.event.addDomListener(element[0], 'keydown', function (e) {
                    if (e.keyCode == 13) {
                        e.preventDefault();
                    }
                });
            }
        };
    }]);

    googleAutoCompleteModule.factory('vsEmbeddedValidatorsInjector', ['$injector', function ($injector) {
        var validatorsHash = [];

        /**
         * Class making embedded validator.
         * @constructor
         * @param {string} name - validator name.
         * @param {function(place)} validateMethod - function that will validate place.
         */
        function EmbeddedValidator(name, validateMethod) {
            this.name = name;
            this.validate = validateMethod;
        }

        function searchValidator(validatorName) {
            for (var i = 0; i < validatorsHash.length; i++) {
                if (validatorsHash[i].name === validatorName)
                    return validatorsHash[i];
            }
            return;
        }

        function getValidator(validatorName) {
            var validator = searchValidator(validatorName);
            if (!validator) {
                validator = new EmbeddedValidator(validatorName, $injector.get(validatorName));
                validatorsHash.push(validator);
            }
            return validator;
        }

        function getValidators(validatorsNamesList) {
            var validatorsList = [];
            for (var i = 0; i < validatorsNamesList.length; i++) {
                var validator = getValidator(validatorsNamesList[i]);
                validatorsList.push(validator);
            }
            return validatorsList;
        }

        return {
            get: getValidators
        };
    }]);

    googleAutoCompleteModule.service('vsValidatorFactory', ['vsEmbeddedValidatorsInjector', function (vsEmbeddedValidatorsInjector) {
        /**
         * Class making validator associated with vsGoogleAutocomplete controller.
         * @constructor
         * @param {Array.<string>} validatorsNamesList - List of embedded validator names.
         */
        function Validator(validatorsNamesList) {
            // add default embedded validator name
            validatorsNamesList.unshift('vsGooglePlace');

            this._embeddedValidators = vsEmbeddedValidatorsInjector.get(validatorsNamesList);
            this.error = {};
            this.valid = true;
        }

        /**
         * Runs all embedded validators and change the validity state.
         * @param {google.maps.places.PlaceResult} place - PlaceResult object.
         */
        Validator.prototype.validate = function (place) {
            var validationErrorKey, isValid;

            for (var i = 0; i < this._embeddedValidators.length; i++) {
                validationErrorKey = this._embeddedValidators[i].name;

                // runs embedded validator only if place is object
                if (angular.isObject(place)) {
                    isValid = this._embeddedValidators[i].validate(place);
                } else {
                    isValid = false;
                }
                this._setValidity(validationErrorKey, isValid);
            }
        };

        /**
         * Sets validity.
         * @param {string} validationErrorKey - Error name.
         * @param {boolean} isValid - Valid status.
         */
        Validator.prototype._setValidity = function (validationErrorKey, isValid) {
            // set error
            if (typeof isValid != 'boolean') {
                delete this.error[validationErrorKey];
            } else {
                if (!isValid) {
                    this.error[validationErrorKey] = true;
                } else {
                    delete this.error[validationErrorKey];
                }
            }
            // set validity
            if (this.error) {
                for (var e in this.error) {
                    this.valid = false;
                    return;
                }
            }
            this.valid = true;
        };

        this.create = function (validatorsNamesList) {
            return new Validator(validatorsNamesList);
        };
    }]);

    googleAutoCompleteModule.directive('vsAutocompleteValidator', ['vsValidatorFactory', function (vsValidatorFactory) {
        /**
         * Parse validator names from attribute.
         * @param {$compile.directive.Attributes} attrs Element attributes
         * @return {Array.<string>} Returns array of normalized validator names.
         */
        function parseValidatorNames(attrs) {
            var attrValue = attrs.vsAutocompleteValidator,
                validatorNames = (attrValue !== "") ? attrValue.trim().split(',') : [];

            // normalize validator names
            for (var i = 0; i < validatorNames.length; i++) {
                validatorNames[i] = attrs.$normalize(validatorNames[i]);
            }

            return validatorNames;
        }

        return {
            restrict: 'A',
            require: ['ngModel', 'vsGoogleAutocomplete'],
            link: function (scope, element, attrs, controllers) {
                // controllers
                var modelCtrl = controllers[0],
                    autocompleteCtrl = controllers[1];

                // validator
                var validatorNames = parseValidatorNames(attrs),
                    validator = vsValidatorFactory.create(validatorNames);

                // add validator for ngModel
                modelCtrl.$validators.vsAutocompleteValidator = function () {
                    return validator.valid;
                };

                // watch for updating place
                autocompleteCtrl.isolatedScope.$watch('vsPlace', function (place) {

                    // validate place
                    validator.validate(place);

                    // set addr components to undefined if place is invalid
                    if (!validator.valid) {
                        autocompleteCtrl.updatePlaceComponents(undefined);
                    }

                    // call modelCtrl.$validators.vsAutocompleteValidator
                    modelCtrl.$validate();
                });

                // publish autocomplete errors
                modelCtrl.vsAutocompleteErorr = validator.error;
            }
        };
    }]);

    //Validator - checks if place is valid Google address
    googleAutoCompleteModule.factory('vsGooglePlace', ['vsGooglePlaceUtility', function (vsGooglePlaceUtility) {
        function validate(place) {
            return vsGooglePlaceUtility.isGooglePlace(place);
        }

        return validate;
    }]);

    //Validator - checks if place is full street address (street number, street, ...)
    googleAutoCompleteModule.factory('vsStreetAddress', ['vsGooglePlaceUtility', function (vsGooglePlaceUtility) {
        var PLACE_TYPES = ["street_address", "premise"];

        function validate(place) {
            return vsGooglePlaceUtility.isContainTypes(place, PLACE_TYPES);
        }

        return validate;
    }]);
})();