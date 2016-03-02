(function(settings){
    'use strict';
    settings = settings || {};

    var helperService = angular.module('helperService', ['headColumnModule', 'pictureModule']);

    helperService.directive('validFile', function () {
        return {
            require: 'ngModel',
            restrict: 'E',
            link: function (scope, el, attrs, ngModel) {
                ngModel.$render = function () {
                    ngModel.$setViewValue(el.val());
                };

                el.bind('change', function () {
                    scope.$apply(function () {
                        ngModel.$render();
                    });
                });
            }
        };
    });

    helperService.directive('googleAddress', ['getInfoFromGeocoder', '$q', "parseGoogleAddressComponents", function(getInfoFromGeocoder, $q, parseGoogleAddressComponents) {
        return {
            require: 'ngModel',
            restrict: '',
            link: function(scope, elm, attrs, ctrl) {
                if (!ctrl) {
                    return;
                }

                ctrl.$asyncValidators.googleAddress = function(modelValue) {
                    var deferred = $q.defer();
                    getInfoFromGeocoder({address: modelValue})
                        .then(function(data){
                            if(undefined == data){
                                deferred.resolve("");
                            }
                            var result = parseGoogleAddressComponents(data[0].address_components);
                            for(var i in result){
                                if(result[i] == '' || result[i] == null){
                                    deferred.reject('Address is bad.');
                                    break;
                                }
                            }
                            deferred.resolve(data);

                            return true;
                        },
                        function(status){
                            deferred.reject(status);
                        }
                    );

                    return deferred.promise;
                };
            }
        };
    }]);

    helperService.directive('usaPhone', [function(){
        var phoneFormat = /^[0-9+\(\)#\.\s\/ext-]+$/;
        return {
            require: 'ngModel',
            restrict: '',
            link: function(scope, elm, attrs, ctrl) {
                if (!ctrl) {
                    return;
                }

                ctrl.$validators.usaPhoneFormat = function(modelValue, viewValue){
                    if (ctrl.$isEmpty(modelValue)) {
                        // consider empty models to be valid
                        return true;
                    }
                    return phoneFormat.test(viewValue);
                }

            }
        };
    }]);

    helperService.directive('compareTo', [function() {
        return {
            require: "ngModel",
            restrict: '',
            scope: {
                otherModelValue: "=compareTo"
            },
            link: function (scope, element, attributes, ngModel) {

                ngModel.$validators.compareTo = function (modelValue) {
                    return modelValue == scope.otherModelValue;
                };
            }
        }
    }]);

    helperService.directive('loNameValidator', [function(){
        var nameFormat = /^([A-Za-z0-9-_\s]+)$/;
        return {
            require: 'ngModel',
            restrict: '',
            link: function(scope, elm, attrs, ctrl) {
                if (!ctrl) {
                    return;
                }

                ctrl.$validators.loName = function(modelValue, viewValue){
                    if (ctrl.$isEmpty(modelValue)) {
                        // consider empty models to be valid
                        return true;
                    }
                    return nameFormat.test(viewValue);
                }

            }
        };
    }]);

    helperService.directive('loFilereader', ["$q", function($q) {
        var slice = Array.prototype.slice;

        return {
            restrict: 'A',
            require: '?ngModel',
            link: function(scope, element, attrs, ngModel) {
                if (!ngModel){
                    return;
                }

                ngModel.$render = function() {};

                element.bind('change', function(e) {
                    var element = e.target;

                    $q.all(slice.call(element.files, 0).map(readFile))
                        .then(function(values) {
                            if (element.multiple){
                                ngModel.$setViewValue(values)
                            }
                            else {
                                ngModel.$setViewValue(values.length ? values[0] : null);
                            }
                        });

                    function readFile(file) {
                        var deferred = $q.defer();

                        var reader = new FileReader();
                        reader.onload = function(e) {
                            scope.$parent.filename = file.name;
                            deferred.resolve(e.target.result);
                        };
                        reader.onerror = function(e) {
                            deferred.reject(e);
                        };
                        reader.readAsDataURL(file);

                        return deferred.promise;
                    }

                });
            }
        };
    }]);

    helperService.filter('fullName', function(){
        return function(user){
            return user.first_name + " " + user.last_name;
        }
    });

    helperService.filter('dashboardType', function(){
        return function(type){
            return type == 1? 'Listing Flyer': 'Property Approval';
        }
    });

    helperService.factory("redirect", ['$location', function($location){
        return function(path, redirectUrl){
            if(undefined != redirectUrl){
                $location.search('redirect_url', redirectUrl);
                $location.path(path);

                return;
            }

            var redirectUrl = $location.search().redirect_url;
            if(undefined != redirectUrl){
                $location.search('redirect_url', null);
                $location.path(redirectUrl);

                return;
            }

            $location.path(path);
        }
    }]);

    helperService.factory("Tab", ['$location', function($location){
        var tab = function(params){
            params = params || {}
            this.path;
            this.title;

            this.isActive = function(){
                return this.location.path() == this.path;
            };

            for(var i in params){
                this[i] = params[i];
            }
        };

        tab.prototype.location = $location;

        return tab;
    }]);

    helperService.factory("loadImages", ["$q", function($q){
        var deferred = $q.defer();
        function loadImages(images){
           var image = images.shift();
           if(image == undefined){
               deferred.resolve();
           }

           var bgImg = new Image();
           bgImg.onload = function(){
               loadImages(images);
           };

           bgImg.onabort = function(){
               loadImages(images);
               console.log(image + ' loading has been aborted.')
           };

           bgImg.onerror = function(){
               loadImages(images);
               console.log(image + ' error loading.')
           };

           bgImg.src = image;
        }

        return function(images){
            loadImages(images);

            return deferred.promise;
        };
    }]);

    helperService.factory("loadGoogleMapsApi", ["$q", function($q){
        return function(){
            var deferred = $q.defer();
            var initialize = function(){
                deferred.resolve();
            };

            if('google' in window){
                return $q.when();
            }

            window.initialize = initialize;

            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&libraries=places&callback=initialize&language=en';
            document.body.appendChild(script);

            return deferred.promise;
        }
    }]);

    helperService.factory("waitingScreen", [function(){
        var container = angular.element('#waiting');
        return {
            show: function(){
                container.show();
            },
            hide: function(){
                container.hide();
            }
        }

    }]);

    helperService.factory('parseGoogleAddressComponents', [function(){
        return function(data) {

            var result = {
                address: '',
                city:    null,
                state:   null,
                zip:     null
            };

            for (var i in data) {
                if ($.inArray('street_number', data[i].types) != -1) {
                    result.address = data[i].long_name+' '+result.address;
                    continue;
                }

                if ($.inArray('route', data[i].types) != -1) {
                    result.address += ' '+data[i].long_name;
                    continue;
                }

                if (result.address === '' && $.inArray('premise', data[i].types) != -1) {
                    result.address = data[i].long_name;
                    continue;
                }

                if ($.inArray('locality', data[i].types) != -1) {
                    result.city = data[i].long_name;
                    continue;
                }

                if ($.inArray('administrative_area_level_1', data[i].types) != -1) {
                    result.state = data[i].short_name;
                    continue;
                }

                if ($.inArray('postal_code', data[i].types) != -1) {
                    result.zip = data[i].long_name;
                    continue;
                }
            }

            return result;
        }
    }]);

    helperService.factory("getLenders", ['$q', '$http', function($q, $http){
        var lenders = [];

        return function(needReload){
            var deferred = $q.defer();
            var counter = lenders.length;
            if(counter != 0 && !needReload){
                return $q.when(lenders);
            }
            $http.get('/admin/json/lenders')
                .success(function(data) {
                    lenders = data;
                    deferred.resolve(data);
                })
                .error(function(data){
                    deferred.reject(data);
                }
            );

            return deferred.promise;
        }
    }]);

    helperService.factory("getRoles", ['$q', '$http', function($q, $http){
        var roles = {};

        return function(needReload){
            needReload = needReload || false;
            var deferred = $q.defer();

            var counter = 0;
            for(var i in roles){
                counter++;
                break;
            }

            if(counter != 0 && !needReload){
                return $q.when(roles);
            }

            $http.get('/admin/roles')
                .success(function(data){
                    roles = data;
                    deferred.resolve(data);
                })
                .error(function(data){
                    deferred.reject(data);
                }
            );

            return deferred.promise;
        }
    }]);

    helperService.factory("renderMessage", ['$compile', function($compile){
        return function(message, type, container, scope){
            var angularDomEl = angular.element('<div lo-message></div>')
                .attr({
                    'lo-body': message,
                    'lo-type': type
                });

            container.html($compile(angularDomEl)(scope));
        }
    }]);

    helperService.factory("loadFile", ['$q', function($q){
        return function(evt){
            var deferred = $q.defer();
            var file = evt.currentTarget.files[0];

            var reader = new FileReader();
            reader.onload = function (evt) {
                deferred.resolve(evt.target.result);
            };

            reader.onerror = function (evt) {
                deferred.reject(evt);
            };

            reader.readAsDataURL(file);

            return deferred.promise;
        }
    }]);

    helperService.factory("loadImage", ['$q', function($q){
        return function(url){
            var deferred = $q.defer();

            var bgImg = new Image();

            bgImg.onload = function(){
                deferred.resolve(bgImg);
            };

            bgImg.onerror = function(evt){
                deferred.reject(evt);
            };

            bgImg.src = url;

            return deferred.promise;
        }
    }]);

    helperService.factory("sessionMessages", ['$rootScope', function($rootScope) {
        return new function() {
            console.log('create new sessionMessage');
            var TYPE_DANGER  = "danger";
            var TYPE_SUCCESS = "success";
            var messages = [];

            this.addDanger = function(message){
                messages.push(this.createMessage(TYPE_DANGER, message));
                return this;
            };

            this.addSuccess = function(message){
                messages.push(this.createMessage(TYPE_SUCCESS, message));

                return this;
            };

            this.createMessage = function(type, body){
                var message = {};
//                message.type = type;
                message.body = body;
                message.isDanger = TYPE_DANGER == type;

                return message;
            };

            this.get = function(clear){
                try{
                    return messages;
                }finally{
                    if(clear != false){
                        messages = [];
                    }
                }
            };

            this.render = function(){
                $rootScope.$broadcast('renderSessionMesages');
            };
        };
    }]);

    helperService.filter('ucFirst', function(){
        return function(input){
            return input == undefined? input: input.replace(/\b[a-z]/, function(letter) {
                return letter.toUpperCase();
            });
        }
    });

    helperService.filter('fromMysqlDate', function(){
        return function(input){
            return undefined  != input && "date" in input
                ? new Date(input.date.replace(/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)(.)*/, '$2/$3/$1 $4:$5:$6'))
                : input;
        }
    });

    helperService.filter('defaultImage', function() {

        return function(input, defaultImage) {
            if("" != input && null !== input && input !== undefined) {
                return input;
            }
            return defaultImage;
        }
    });

    /**
     * Show progress bar
     */
    helperService.factory('progressBar', function() {
        var element = angular.element('#progress-bar-screen');

        return {
            progress   : 0,
            text       : null,
            setProgress: function(param) {
                this.progress = param;
                angular.element('.progress-bar', element).css('width', this.progress+'%').text(this.progress+'%');
                return this;
            },
            setText: function(param) {
                this.text = param;
                angular.element('.text', element).text(this.text);
                return this;
            },
            show: function() {
                this.setProgress(1);
                element.removeClass('hide');
            },
            hide: function() {
                this.setProgress(0);
                element.addClass('hide');
            }
        };
    });

    /**
     * Validate address
     */
    helperService.directive(
        'validateGoogleAddress',
        ['googleAddress', 'parseGoogleAddressComponents',
            function(googleAddress, parseGoogleAddressComponents)
    {
        return {
            require : 'ngModel',
            restrict: 'A',
            link    : function(scope, elm, attrs, ctrl) {
                if (!ctrl) {
                    return;
                }

                ctrl.$validators.address_components = function(address) {
                    var valid = true;
                    if (ctrl.$isEmpty(address)) {
                        // consider empty models to be valid
                        return valid;
                    }
                    googleAddress.stringIsValid(address).then(function(data) {
                        if (data.length > 0) {
                            scope.request.property.address = address;
                            scope.request.address.set(parseGoogleAddressComponents(data));
                            valid = googleAddress.objectIsValid(scope.request.address);
                        }
                        else {
                            scope.request.address.clear();
                            valid = false;
                        }
                        ctrl.$setValidity('address_components', valid);
                    });
                }
            }
        };
    }]);

    helperService.factory("getInfoFromGeocoder", ['$q', "loadGoogleMapsApi", function($q, loadGoogleMapsApi){
        return function(request){
            var deferred = $q.defer();

            loadGoogleMapsApi()
                .then(function(){
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode( request, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            deferred.resolve(results);
                        } else {
                            console.log("Geocode was not successful for the following reason: " + status);
                            deferred.reject(status == "ZERO_RESULTS"? "Invalid address": "Unknown Google maps error.");
                        }
                    });
                });

            return deferred.promise;
        }
    }]);

    /**
     * Tools for address validation
     */
    helperService.factory('googleAddress', ['$q', function($q) {

        var requiredFields = ['address', 'city', 'state', 'zip'];

        return {
            stringIsValid: function(address) {
                var geocoder = new google.maps.Geocoder(),
                    deferred = $q.defer();

                geocoder.geocode({ address: address }, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        for (var i in results) {
                            if (address.toLowerCase() === results[i].formatted_address.toLowerCase()) {
                                return deferred.resolve(results[i].address_components);
                            }
                        }
                    }

                    return deferred.resolve([]);
                });

                return deferred.promise;
            },
            objectIsValid: function(object) {
                if ('object' !== typeof object) {
                    return false;
                }

                for (var i in requiredFields) {
                    if (!object[requiredFields[i]]) {
                        return false;
                    }
                }

                return true;
            }
        };
    }]);

    /**
     * Count max-height for group divs
     */
    helperService.directive('setMaxHeight', ['$timeout', function($timeout) {
        var height = 0;

        return {
            restrict: 'A',
            link    : function(scope, element, attr) {
                $timeout(function() {
                    var elementHeight = angular.element(attr['setMaxHeight'], element).removeAttr('style').height();
                    if (height < elementHeight) {
                        height = elementHeight;
                    }

                    if (scope.$last === true) {
                        angular.element(attr['setMaxHeight']).height(height);
                    }
                });
            }
        }
    }]);

    /**
     * Set tooltip
     */
    helperService.directive('setTooltip', function() {
        return {
            restrict: 'A',
            link    : function(scope, element, attr) {
                angular.element(element).popover({trigger: 'hover'});
            }
        }
    });
})(settings);