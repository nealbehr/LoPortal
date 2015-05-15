(function(settings){
    'use strict';
    settings = settings || {};

    var helperService = angular.module('helperService', ['headColumnModule', 'pictureModule']);

    helperService.run(['$templateCache', function($templateCache){
        $templateCache.put('message.html', "<div class=\"alert fade in\" style=\"z-index: 5;\" role=\"alert\" ng-class=\"{'alert-danger': isDanger(), 'alert-success': !isDanger()}\">" +
                                               "<a href=\"#\" class=\"close\"  data-dismiss=\"alert\" aria-label=\"Close\">close</a>" +
                                               "[[ loBody ]]" +
                                           "</div>");
    }]);

    helperService.directive('loMessage', [function(){
        return { restrict: 'EA',
            templateUrl: 'message.html',
            scope: {
                'loType':        "@",
                'loBody':        "@"
            },
            link: function(scope, element, attrs, controllers){
                scope.isDanger = function(){
                    return scope.loType == 'danger';
                }

                scope.$watch('loType', function (newValue) {
                    scope.loType = newValue;
                });

                scope.$watch('loBody', function (newValue) {
                    scope.loBody = newValue;
                });

            }
        }
    }]);

    helperService.directive('loNavbarHead', ['$http', '$cookieStore', 'redirect', 'TOKEN_KEY', 'userService', function($http, $cookieStore, redirect, TOKEN_KEY, userService){
        return { restrict: 'EA',
            templateUrl: '/partials/navbar.head',
            link: function(scope, element, attrs, controllers){
                scope.user         = {}
                scope.isUserLoaded = false;
                userService.get().then(function(user){
                    scope.user         = user;
                    scope.isUserLoaded = true;
                });

                angular.element('.dropdown-toggle').click(function(e){
                    var target = $(e.target);
                    if(target.is('a')){
                        e.stopPropagation();
                    }
                });

                scope.logout = function(e){
                    e.preventDefault();
                    $http.delete('/logout')
                        .success(function(data){
                            $cookieStore.remove(TOKEN_KEY)
                            userService.get().then(function(user){
                                user.clear();
                                redirect('/login');
                            });
                        })
                        .finally(function(){

                        })
                    ;

                    return false;
                }
            }
        }
    }]);

    helperService.directive('loUserInfo', ["redirect", "userService", "$http", "waitingScreen", "renderMessage", "getRoles", "$location", "$q", "sessionMessages", "$anchorScroll", "loadFile", "$timeout", "pictureObject", function(redirect, userService, $http, waitingScreen, renderMessage, getRoles, $location, $q, sessionMessages, $anchorScroll, loadFile, $timeout, pictureObject){
        return { restrict: 'EA',
            templateUrl: '/partials/user.form',
            scope: {
                officer:   "=loOfficer"
            },
            link: function(scope, element, attrs, controllers){
                scope.roles = [];
                scope.selected = {};
                scope.user = {}
                scope.container = angular.element('#userProfileMessage');
                scope.userPicture;

                scope.$watch('officer.id', function(newVal, oldVal){
                    if(undefined != newVal && newVal == scope.user.id){
                        scope.title = "Edit Profile";
                        return;
                    }

                    scope.title = newVal? 'Edit User': 'Add User';
                });

                scope.$watch('officer', function(newVal, oldVal){
                    if(newVal == undefined || !("id" in newVal)){
                        return;
                    }

                    scope.userPicture = new pictureObject(
                        angular.element('#userPhoto'),
                        {container: $(".realtor-photo > img"), options: {aspectRatio: 3 / 4, minContainerWidth: 100}},
                        scope.officer
                    );
                });

                scope.cancel = function(e){
                    e.preventDefault();

                    history.back();
                }

                userService.get()
                    .then(function(user){
                        scope.user = user;
                        return scope.user.isAdmin() && scope.roles.length == 0
                            ? getRoles()
                            : $q.when({});
                    })
                    .then(function(data){
                        scope.roles = [];
                        for(var i in data){
                            scope.roles.push({'title': i, key: data[i]});
                        }

                        if(scope.roles.length == 0){
                            return;
                        }

                        if(!scope.selected.key){
                            scope.selected =  scope.roles[0];
                        }
                    })
                ;

                scope.$watch('officer', function(newVal){
                    scope.setSelected(newVal);
                });
                scope.$watch('roles', function(newVal){
                    scope.setSelected(newVal);
                });

                scope.isValidEmail = function(form){
                    if(!form.email){
                        return;
                    }

                    return (form.$submitted || form.email.$touched) && (form.email.$error.email || form.email.$error.required);
                }

                scope.gotoErrorMessage = function(){
                    $anchorScroll(scope.container.attr("id"));
                }

                scope.submit = function(formUser){
                    if(!formUser.$valid){
                        this.gotoErrorMessage();
                        return false;
                    }
                    this.save();
                }

                scope.delete = function(e){
                    e.preventDefault();

                    if(!confirm("Are you sure?")){
                        return false;
                    }

                    waitingScreen.show();
                    scope.officer.delete().
                        then(function(data){
                            sessionMessages.addSuccess("User was deleted.");
                            scope.officer.clear();
                            history.back();
                        })
                        .catch(function(data){
                            if('message' in data){
                                renderMessage(data.message, "danger", scope.container, scope);
                                scope.gotoErrorMessage();
                            }
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        });
                }

                scope.save = function(){
                    waitingScreen.show();
                    scope.officer.roles = [scope.selected.key];

                    if(scope.officer.picture){
                        scope.userPicture.prepareImage(800, 400, 600, 300);
                    }

                    scope.officer.save()
                        .then(function(data){
                            if(scope.user.id == scope.officer.id){
                                scope.user.fill(scope.officer.getFields4Save());
                            }
                            sessionMessages.addSuccess("Successfully saved.");
                            history.back();
                        })
                        .catch(function(data){
                            var errors = "";
                            if("message" in data){
                                errors = data.message + " ";
                            }

                            if("form_errors" in data){
                                errors += data.form_errors.join(" ");
                            }

                            renderMessage(errors, "danger", scope.container, scope);
                            scope.gotoErrorMessage();
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        }
                    );
                }

                scope.setSelected = function(newVal){
                    if(!newVal || !scope.officer || !scope.officer.roles || scope.roles.length < 1){
                        return;
                    }

                    var officerRole;
                    for(var i in scope.officer.roles){
                        officerRole = scope.officer.roles[i];
                        break;
                    }

                    for(var i in scope.roles){
                        if(scope.roles[i].key == officerRole){
                            scope.selected =  scope.roles[i];
                            break;
                        }
                    }
                }

            }
        }
    }]);

    helperService.directive('loDashboardRow', ['$timeout', 'tableHeadColSample', function($timeout, tableHeadColSample){
        return { restrict: 'EA',
            templateUrl: '/partials/dashboard.row',
            scope: {
                items: '=loItems',
                id:    '@loId',
                title: '@loTitle',
                isExpand: '=loIsExpand',
                state: "@loState"
            },
            link: function(scope, element, attrs, controllers){
                scope.requestType = settings.queue.type;
                function params (key, title){
                    this.key = key;
                    this.title = title;
                }
                params.prototype.directionKey     = 'reverse';
                params.prototype.scope     = scope;
                params.prototype.sortKey          = 'predicate';
                params.prototype.defaultDirection = true;
                params.prototype.defaultSortKey   = 'created_at';


                scope.headParams = [
                    new tableHeadColSample(new params("created_at.date", "Created")),
                    new tableHeadColSample(new params("address", "Property Address")),
                    new tableHeadColSample(new params("request_type", "Type"))
                ];

                scope.predicate = scope.headParams[0].key;
                scope.reverse = true;

                $timeout(function(){
//                    angular.element("#" + scope.id + " > table").tablesorter();
                });

                scope.$watch("isExpand", function(newValue){
                    if(newValue){
                        $('#' + scope.id).collapse('show');
                    }
                });


                scope.isApprovedProperty = function(item){
                    return item.request_type == this.requestType.propertyApproval && item.state == settings.queue.state.approved;
                }

                scope.hasPdf = function(item){
                    return item.request_type == this.requestType.flyer && item.flyer != null && item.flyer.pdf_link && item.state == settings.queue.state.approved
                }
                scope.canCancel = function(item){
                    return item.state == settings.queue.state.requested || item.state == settings.queue.state.draft;
                }
                scope.isComplete = function(item){
                    return item.state == settings.queue.state.draft;
                }
                scope.isNone = function(item){
                    return !(this.isApprovedProperty(item) || this.hasPdf(item) || this.canCancel(item) || this.isComplete(item));
                }
            }
        }
    }]);

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

    helperService.directive('dashboardCollateral', function () {
        return {
            restrict: 'EA',
            templateUrl: '/partials/dashboard.collateral.row',
            scope: {
                items: '=loItems'
            },
            link: function (scope, el, attrs, ngModel) {
                scope.$watch('items', function(newValue){
                    scope.items = newValue;
                });
            }
        };
    });

    helperService.directive('loDashboardHead', ['Tab', 'redirect', function(Tab, redirect){
        return {
            restrict: 'EA',
            templateUrl: '/partials/dashboard.head',
            link: function (scope, el, attrs, ngModel) {
                scope.tabs = [
                    new Tab({path: '/', title: "Requests Queue"}),
                    new Tab({path: '/dashboard/collateral', title: "Custom Collateral"})
                ];

                scope.createListingFlyerRequest = function(e){
                    e.preventDefault();
                    redirect("/flyer/new");
                }

                scope.createNewApproval = function(e){
                    e.preventDefault();
                    redirect('/request/approval');
                }
            }
        };
    }]);

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

    helperService.directive('validateGoogleAddress', ["parseGoogleAddressComponents", function(parseGoogleAddressComponents) {
        return {
            require: 'ngModel',
            restrict: '',
            link: function(scope, elm, attrs, ctrl) {
                if (!ctrl) {
                    return;
                }

                ctrl.$validators.address_components = function(modelValue){
                    if (ctrl.$isEmpty(modelValue)) {
                        // consider empty models to be valid
                        return true;
                    }

                    for(var i in scope.request.address){
                        if(scope.request.address[i] == '' || scope.request.address[i] == null){
                            return false;
                        }
                    }

                    return true;
                }
            }
        };
    }]);

    helperService.directive('usaPhone', [function(){
        var phoneFormat = /^\(?(\d{3})\)?[-\. ]?(\d{3})[-\. ]?(\d{4})$/;
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

    helperService.directive('loNameValidator', [function(){
        var nameFormat = /^([A-Za-z0-9_\s]+)$/;
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

    helperService.directive('loMessageContainer', ['sessionMessages', '$compile', function(sessionMessages){
        return {
            restrict: 'EA',
            templateUrl: '/partials/session.messages',
            link: function(scope, elm, attrs, ctrl) {
                scope.$on('showSessionMessage', function () {
                    console.log('showSessionMessage');
                    scope.messages = sessionMessages.get();
                });

                scope.messages = sessionMessages.get();
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
                };

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

    helperService.directive('loRequestFlyerEdit', ["$location", "createAdminRequestFlyer", "$routeParams", "parseGoogleAddressComponents", "loadFile", "$timeout", "redirect", "waitingScreen", "getInfoFromGeocoder", "loadImage", "$q", "$rootScope", "sessionMessages", "pictureObject", "createFromPropertyApproval", "loadGoogleMapsApi", "createDraftRequestFlyer", function($location, createAdminRequestFlyer, $routeParams, parseGoogleAddressComponents, loadFile, $timeout, redirect, waitingScreen, getInfoFromGeocoder, loadImage, $q, $rootScope, sessionMessages, pictureObject, createFromPropertyApproval, loadGoogleMapsApi, createDraftRequestFlyer){
        return {
            restrict: 'EA',
            templateUrl: '/partials/request.flyer.form',
            scope: {
                request: "=loRequest",
                titles: "=loTitles",
                user: '=loUser'
            },
            link: function(scope, element, attrs, controllers){
                scope.states = settings.queue.state;
                scope.realtorPicture;
                scope.propertyPicture;
                scope.oldRequest;

                scope.$watch('request', function(newVal){
                    if(undefined == newVal || !("id" in newVal)){
                        return;
                    }

                    scope.realtorPicture = new pictureObject(
                        angular.element("#realtorImage"),
                        {container: $(".realtor-photo > img"), options: {aspectRatio: 3 / 4, minContainerWidth: 100}},
                        scope.request.realtor
                    );

                    scope.propertyPicture = new pictureObject(
                        angular.element("#propertyImage"),
                        {container: $(".property-photo > img"), options: {aspectRatio: 3 / 2}},
                        scope.request.property
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

                scope.cancel = function(e){
                    e.preventDefault();

                    if(scope.request.property.state != settings.queue.state.draft){
                        history.back();
                        return;
                    }

                    scope.requestDraft = (new createDraftRequestFlyer()).fill(scope.request.getFields4Save());

                    waitingScreen.show();
                    scope.requestDraft.remove()
                        .success(function(){
                            scope.oldRequest = angular.copy(scope.request);
                            history.back();
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        })

                    ;
                }

                scope.saveDraft = function(e){
                    e.preventDefault();

                    scope.request.property.state = settings.queue.state.draft;

                    scope.requestDraft = (new createDraftRequestFlyer()).fill(scope.request.getFields4Save());
                    scope.realtorPicture.setObjectImage(scope.requestDraft.realtor);
                    scope.propertyPicture.setObjectImage(scope.requestDraft.property);

                    scope.requestDraft.afterSave(function(){
                        sessionMessages.addSuccess("Successfully saved.");
                        scope.oldRequest = angular.copy(scope.request);
                        history.back();
                    });

                    this.saveRequest(scope.requestDraft);
                }

                scope.save = function(){
                    scope.request.afterSave(function(){
                        scope.oldRequest = angular.copy(scope.request);
                        $rootScope.$broadcast('requestFlyerSaved');
                    });

                    this.saveRequest(scope.request);
                }

                scope.saveRequest = function(request){
                    waitingScreen.show();
                    scope.propertyPicture.prepareImage(2000, 649, 3000, 974);
                    scope.realtorPicture.prepareImage(800, 400, 600, 300);

                    request.save()
                        .catch(function(e){
                            scope.realtorPicture.setObjectImage(scope.request.realtor);
                            scope.propertyPicture.setObjectImage(scope.request.property);
                            alert("We have some problems. Please try later.");
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        })
                    ;
                }

                scope.isAddressReadOnly = function(){
                    return this.request instanceof createFromPropertyApproval;
                }

                scope.clearAddress = function(e){
                    if(e.keyCode != 13){
                        this.request.address.clear();
                    }
                }

                loadGoogleMapsApi()
                    .then(function(){
                        initialize();
                    })
                ;

                var placeSearch, autocomplete;
                function initialize() {
                    autocomplete = new google.maps.places.Autocomplete(
                        (document.getElementById('pac-input')),
                        { types: ['geocode'] }
                    );
                    google.maps.event.addListener(autocomplete, 'place_changed', function() {
                        fillInAddress();
                    });
                }

                function fillInAddress() {
                    var place = autocomplete.getPlace();
                    scope.$apply(function(){
                        if("address_components" in place){
                            scope.request.address.set(parseGoogleAddressComponents(place.address_components));
                            scope.request.property.address = place.formatted_address;
                        }else{
                            scope.request.address.clear();
                        }
                    });
                }
            }
        }
    }]);

    helperService.filter('avatar', function(){
        return function(input){
            return input == null ? "images/empty.png": input;
        }
    });

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
            }

            for(var i in params){
                this[i] = params[i];
            }
        }

        tab.prototype.location = $location;

        return tab;
    }]);

    helperService.factory("loadImages", ["$q", function($q){
        var deferred = $q.defer();
        function loadImages(images){
           var image = images.shift()
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
           }

           bgImg.onerror = function(){
               loadImages(images);
               console.log(image + ' error loading.')
           }

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
            script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&libraries=places&callback=initialize';
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

    helperService.factory("parseGoogleAddressComponents", [function(){
        return function(data){
            var result = {
                address: '',
                city:    null,
                state:   null,
                zip:     null
            }

            for(var i in data){
                if($.inArray("street_number", data[i].types) != -1){
                    result.address = data[i].long_name + ' ' + result.address;
                    continue;
                }

                if($.inArray("route", data[i].types) != -1){
                    result.address += ' ' + data[i].long_name;
                    continue;
                }

                if($.inArray("locality", data[i].types) != -1){
                    result.city = data[i].long_name;
                    continue;
                }

                if($.inArray("administrative_area_level_1", data[i].types) != -1){
                    result.state = data[i].short_name;
                    continue;
                }

                if($.inArray("postal_code", data[i].types) != -1){
                    result.zip = data[i].long_name;
                    continue;
                }
            }

            return result;
        }
    }]);

    helperService.factory("getRoles", ['$q', '$http', function($q, $http){
        var roles = {}

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
                })

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
            }

            bgImg.onerror = function(evt){
                deferred.reject(evt);
            }

            bgImg.src = url;

            return deferred.promise;
        }
    }]);

    helperService.factory("sessionMessages", [function(){
        return new function(){
            console.log('create new sessionMessage');
            var TYPE_DANGER  = "danger";
            var TYPE_SUCCESS = "success";
            var messages = [];

            this.addDanger = function(message){
                messages.push(this.createMessage(TYPE_DANGER, message));

                return this;
            }

            this.addSuccess = function(message){
                messages.push(this.createMessage(TYPE_SUCCESS, message));

                return this;
            }

            this.createMessage = function(type, body){
                var message = {}
//                message.type = type;
                message.body = body;
                message.isDanger = TYPE_DANGER == type;

                return message;
            }

            this.get = function(clear){
                try{
                    return messages;
                }finally{
                    if(clear != false){
                        messages = [];
                    }
                }
            }
        }
    }]);

    helperService.filter('ucFirst', function(){
        return function(input){
            return input == undefined? input: input.replace(/\b[a-z]/, function(letter) {
                return letter.toUpperCase();
            });
        }
    })

    helperService.filter('fromMysqlDate', function(){
        return function(input){
            return undefined  != input && "date" in input
                ? new Date(input.date.replace(/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)(.)*/, '$2/$3/$1 $4:$5:$6'))
                : input;
        }
    });

    helperService.filter('propertyImage', function(){
        return function(input){
            return "" != input && null != input
                ? input
                : '/images/empty-big.png';
        }
    });

    helperService.filter('realtorImage', function(){
        return function(input){
            return "" != input && null !== input && input !== undefined
                ? input
                : '/images/empty.png';
        }
    });

})(settings);