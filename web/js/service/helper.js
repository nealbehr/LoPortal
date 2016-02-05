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
            link: function(scope, element, attrs, controllers) {

                scope.isDanger = function(){
                    return scope.loType == 'danger';
                };

                scope.$watch('loType', function (newValue) {
                    scope.loType = newValue;
                });

                scope.$watch('loBody', function (newValue) {
                    scope.loBody = newValue;
                });

            }
        }
    }]);

    helperService.directive(
        'loNavbarHead',
        ['$http', '$cookieStore', 'redirect', 'TOKEN_KEY', 'userService', 'waitingScreen', 'Tab',
            function($http, $cookieStore, redirect, TOKEN_KEY, userService, waitingScreen, Tab) {
        return {
            restrict: 'EA',
            templateUrl: '/partials/navbar.head',
            link: function(scope, element, attrs, controllers){
                scope.user         = {};
                scope.isUserLoaded = false;
                scope.headerTabs   = [
                    new Tab({path: '/dashboard/collateral', title: 'Custom Collateral'}),
                    new Tab({path: '/resources', title: 'Program Resources'}),
                    new Tab({path: '/calculators', title: 'Calculators'}),
                    new Tab({path: '/', title: 'New Property Prequalification'}),
                    new Tab({path: '/flyer/new', title: 'New Listing Flyer'}),
                    new Tab({path: '/dashboard/requests', title: 'Requests Queue'})
                ];

                userService.get().then(function(user) {
                    scope.user         = user;
                    scope.isUserLoaded = true;
                    scope.headerTabs.push(new Tab({path: '/user/'+user.id+'/edit', title: 'Edit profile'}));
                    if (user.isAdmin()) {
                        scope.headerTabs.push(new Tab({path: '/admin', title: 'Admin panel'}));
                    }
                });

                angular.element('.dropdown-toggle').click(function(e){
                    var target = $(e.target);
                    if(target.is('a')){
                        e.stopPropagation();
                    }
                });

                scope.logout = function(e) {
                    e.preventDefault();

                    waitingScreen.show();

                    $http.delete('/logout').success(function(data) {
                        $cookieStore.remove(TOKEN_KEY);
                        userService.get().then(function(user){
                            user.clear();
                            redirect('/login');
                        });
                    }).finally(function(){
                        scope.isUserLoaded = false;
                        waitingScreen.hide();
                    });

                    return false;
                }
            }
        }
    }]);

    helperService.directive(
        'loUserInfo',
        ["redirect", "userService", "$http", "waitingScreen", "renderMessage", "getRoles", "getLenders", "$location", "$q", "sessionMessages", "$anchorScroll", "loadFile", "$timeout", "pictureObject",
            function(redirect, userService, $http, waitingScreen, renderMessage, getRoles, getLenders, $location, $q, sessionMessages, $anchorScroll, loadFile, $timeout, pictureObject)
        {
        return { restrict: 'EA',
            templateUrl: '/partials/user.form',
            scope: {
                officer:   "=loOfficer"
            },
            link: function(scope, element, attrs, controllers){
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
                    header  : 'Edit Profile',
                    infoText: 'Change your information and photo here if needed. This will also automatically update '
                        +'your customized collateral.'
                };

                scope.$watch('officer', function(newVal, oldVal){
                    if(newVal == undefined){
                        return;
                    }
                    scope.userPicture = new pictureObject(
                        angular.element('#userPhoto'),
                        {container: $(".realtor-photo > img"), options: {aspectRatio: 3 / 4, minContainerWidth: 100}},
                        scope.officer
                    );
                });

                scope.itsMe = function(){
                    return this.officer.id && this.user.id == this.officer.id;
                };

                scope.resetUserData = function() {
                    angular.copy(scope.masterUserData, scope.user);
                };

                scope.cancel = function(e) {
                    e.preventDefault();
                    scope.resetUserData();
                    history.back();
                };

                scope.autoComplete = function(event) {
                    var element = $(event.target);

                    element.autocomplete({
                        source: function(request, response) {
                            $http.get(
                                '/admin/salesdirector',
                                {
                                    params: {
                                        'filterValue': element.val().toLowerCase(),
                                        'searchBy'   : 'name'
                                    },
                                    cache : true
                                }
                            ).then(function(resp) {
                                response($.map(resp.data.salesDirectors, function(item) {
                                    return {
                                        label        : item.name,
                                        value        : item.name,
                                        salesDirector: item
                                    };
                                }));
                            });
                        },
                        minLength: 0,
                        delay: 500,
                        select: function(event, ui) {
                            if (ui.item !== undefined) {
                                scope.officer.sales_director       = ui.item.value;
                                scope.officer.sales_director_phone = ui.item.salesDirector.phone;
                                scope.officer.sales_director_email = ui.item.salesDirector.email;
                                scope.$apply();
                            }
                            return false;
                        }
                    }).autocomplete('search', element.val().toLowerCase());
                };

                userService.get()
                    .then(function(user){
                        scope.masterUserData = angular.copy(user);
                        scope.user           = user;

                        return scope.user.isAdmin() && scope.roles.length == 0 ? getRoles() : $q.when({});
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
                    .then(function(){
                        if(scope.user.isAdmin() && scope.lenders.length == 0) {
                            getLenders(true).then(function(data) {
                                scope.lenders = data;
                                if(scope.officer && !scope.officer.lender) {
                                    scope.officer.lender =  scope.lenders[0];
                                }
                            })

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
                };

                scope.showErrors = function(e){
                    e.preventDefault();

                    this.hideErrors = true;
                };

                scope.gotoErrorMessage = function(){
                    $anchorScroll(scope.container.attr("id"));
                };

                scope.submit = function(formUser, $event) {
                    if(!formUser.$valid) {
                        this.hideErrors = false;
                        this.gotoErrorMessage();
                        return false;
                    }
                    this.save();
                };

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
                };

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
                };

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
                };
                scope.isApprovedFlyer= function(item){
                    return item.request_type == this.requestType.flyer && item.state == settings.queue.state.approved
                };
                scope.canCancel = function(item){
                    return item.state == settings.queue.state.requested || item.state == settings.queue.state.draft;
                };
                scope.isComplete = function(item){
                    return item.state == settings.queue.state.draft;
                };
                scope.isDeclined = function(item){
                    return item.state == settings.queue.state.declined;
                };

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

    helperService.directive('loMessageContainer', ['sessionMessages', '$compile', function(sessionMessages){
        return {
            restrict: 'EA',
            templateUrl: '/partials/session.messages',
            link: function(scope, elm, attrs, ctrl) {
                scope.$on('renderSessionMesages', function () {
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

    helperService.directive(
        'loRequestFlyerEdit', 
        ['$location', 'createRequestFlyer', '$routeParams', 'parseGoogleAddressComponents', 'loadFile', '$timeout', 
            'redirect', 'waitingScreen', 'getInfoFromGeocoder', 'loadImage', '$q', '$rootScope', 'sessionMessages', 
            'pictureObject', 'createFromPropertyApproval', 'loadGoogleMapsApi', 'createDraftRequestFlyer', 
            '$anchorScroll', 'renderMessage', 'createProfileUser', 'createDraftFromPropertyApproval', '$http', 
            function($location, createRequestFlyer, $routeParams, parseGoogleAddressComponents, loadFile, $timeout, 
                     redirect, waitingScreen, getInfoFromGeocoder, loadImage, $q, $rootScope, sessionMessages, 
                     pictureObject, createFromPropertyApproval, loadGoogleMapsApi, createDraftRequestFlyer, 
                     $anchorScroll, renderMessage, createProfileUser, createDraftFromPropertyApproval, $http) {
        return {
            restrict: 'EA',
            templateUrl: '/partials/request.flyer.form',
            scope: {
                request: "=loRequest",
                titles: "=loTitles",
                officer: '=loOfficer',
                user: '=loUser'

            },
            link: function(scope, element, attrs, controllers){
                scope.states = settings.queue.state;
                scope.realtorPicture = {};
                scope.propertyPicture = {};
                scope.realtyLogo = {};
                scope.oldRequest = {};
                scope.hideErrors = true;
                scope.container = angular.element("#errors");
                scope.realtorSelect = 'omit';

                // Select realtor
                scope.realtorSelect  = 'omit';
                scope.realtorOptions = [
                    { value: 'omit', name: 'Omit realtor information', type: 'Options' },
                    { value: 'add', name: 'Add realtor', type: 'Options' }
                ];
                waitingScreen.show();
                $http.get('/request/flyer/realtors').then(function(response) {
                    scope.realtorOptions = scope.realtorOptions.concat(
                        $.map(response.data.realtors, function(item) {
                            return {
                                name : item.first_name+' '+item.last_name,
                                value: item.id,
                                type : 'Select from existing realtors'
                        };
                    }));
                    waitingScreen.hide();
                });
                scope.setRealtorData = function() {
                    scope.request.realtor_id = (isNaN(scope.realtorSelect)) ? null : scope.realtorSelect;
                    scope.request.property.omit_realtor_info = (scope.realtorSelect === 'omit') ? '1' : '0';
                };

                scope.$watch('request', function(newVal){
                    if(undefined == newVal || !("id" in newVal)){
                        return;
                    }

                    if (newVal.realtor_id !== null && !isNaN(newVal.realtor_id)) {
                        scope.realtorSelect = newVal.realtor_id;
                    }

                    scope.realtorPicture = new pictureObject(
                        angular.element("#realtorImage"),
                        {container: $(".realtor.realtor-photo > img"), options: {aspectRatio: 3 / 4, minContainerWidth: 100}},
                        scope.request.realtor
                    );

                    scope.propertyPicture = new pictureObject(
                        angular.element("#propertyImage"),
                        {container: $(".property-photo > img"), options: {aspectRatio: 3 / 2}},
                        scope.request.property
                    );

                    scope.realtyLogo = new pictureObject(
                        angular.element('#realtyLogo'),
                        {container: $("#realtyLogoImage"), options: {
                            minCropBoxWidth: 100,
                            minCropBoxHeight: 100,
                            maxCropBoxWidth: 350,
                            maxCropBoxHeight: 100
                        }},
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

                scope.cancel = function(e){
                    e.preventDefault();

                    history.back();
                };

                scope.saveDraftOrApproved = function(e, form) {
                    e.preventDefault();

                    if (!form.$valid) {
                        this.gotoErrorMessage();
                        return false;
                    }

                    if (scope.request.property.omit_realtor_info === '1' && !confirm('Did you mean to omit realtor?')) {
                        return false;
                    }

                    if(scope.request.property.state != settings.queue.state.approved) {
                        scope.request.property.state = settings.queue.state.draft;
                    }

                    scope.requestDraft = this.request instanceof createFromPropertyApproval
                                                        ? (new createDraftFromPropertyApproval())
                                                        : (new createDraftRequestFlyer());

                    scope.requestDraft.fill(scope.request.getFields4Save());
                    scope.realtorPicture.setObjectImage(scope.requestDraft.realtor);
                    scope.propertyPicture.setObjectImage(scope.requestDraft.property);

                    scope.requestDraft.afterSave(function(){
                        sessionMessages.addSuccess("Successfully saved.");
                        scope.oldRequest = angular.copy(scope.request);
                        if($rootScope.historyGet().indexOf('/request/success') != -1){
                            redirect('/');
                        }else{
                            history.back();
                        }
                    });

                    this.saveRequest(scope.requestDraft);
                };

                scope.showErrors = function(e){
                    e.preventDefault();

                    this.hideErrors = true;
                };

                scope.gotoErrorMessage = function() {
                    scope.hideErrors = false;
                    $anchorScroll(scope.container.attr("id"));
                };

                scope.save = function(form) {
                    if (!form.$valid) {
                        this.gotoErrorMessage();
                        return false;
                    }

                    if (scope.request.property.omit_realtor_info === '1' && !confirm('Did you mean to omit realtor?')) {
                        return false;
                    }

                    scope.request.afterSave(function() {
                        scope.oldRequest = angular.copy(scope.request);
                        $rootScope.$broadcast('requestFlyerSaved', scope.request);
                    });

                    this.saveRequest(scope.request);
                };

                scope.saveRequest = function(request){
                    waitingScreen.show();
                    scope.propertyPicture.prepareImage(2000, 649, 3000, 974);
                    if (request.realtor.hasOwnProperty('photo') && request.realtor.photo !== null) {
                        scope.realtorPicture.prepareImage(800, 400, 600, 300);
                    }


                    request.save()
                        .catch(function(e){
                            var messages = [];
                            messages.push('message' in e? e.message: "We have some problems. Please try later.");

                            for(var i in e){
                                if(e[i].constructor === Array){
                                    for(var j in e[i]){
                                        if(e[i][j] != ""){
                                            messages.push(e[i][j]);
                                        }
                                    }
                                }
                            }

                            if(messages.length > 0){
                                renderMessage(messages.join(" "), "danger", scope.container, scope);
                                scope.gotoErrorMessage();
                            }

                            scope.realtorPicture.setObjectImage(scope.request.realtor);
                            scope.propertyPicture.setObjectImage(scope.request.property);
                        })
                        .finally(function(){
                            waitingScreen.hide();
                        })
                    ;
                };

                scope.isAddressReadOnly = function() {

                    if(typeof this.request.property === 'object' && this.request.property.state == settings.queue.state.approved) {
                        if(angular.isFunction(scope.user.isAdmin)) {
                            return !scope.user.isAdmin();
                        }
                        return true;
                    }
                    return this.request instanceof createFromPropertyApproval;
                };

                scope.clearAddress = function(e){
                    if(e.keyCode != 13){
                        this.request.address.clear();
                    }
                };

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
        ['addressValidation', 'parseGoogleAddressComponents',
            function(addressValidation, parseGoogleAddressComponents)
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
                    addressValidation.stringIsValid(address).then(function(data) {
                        if (data.length > 0) {
                            scope.request.property.address = address;
                            scope.request.address.set(parseGoogleAddressComponents(data));
                            valid = addressValidation.arrayIsValid(scope.request.address);
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

    /**
     * Tools for address validation
     */
    helperService.factory('addressValidation', ['$q', function($q) {

        var requiredFields = ['address', 'city', 'state', 'zip'];

        return {
            stringIsValid: function(address) {
                var geocoder = new google.maps.Geocoder(),
                    deferred = $q.defer();

                geocoder.geocode({ address: address }, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        for (var i in results) {
                            if (address === results[i].formatted_address) {
                                return deferred.resolve(results[i].address_components);
                            }
                        }
                    }

                    return deferred.resolve([]);
                });

                return deferred.promise;
            },
            arrayIsValid: function(array) {
                if ('object' === typeof(array)) {
                    return false;
                }

                for (var i in requiredFields) {
                    if (!array[requiredFields[i]]) {
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
                if (attr['setMaxHeight']) {
                    $timeout(function() {
                        var elementHeight = angular.element(element).find(attr['setMaxHeight']).height();
                        if (height < elementHeight) {
                            height = elementHeight;
                        }

                        if (scope.$last === true) {
                            angular.element(attr['setMaxHeight']).height(height);
                        }
                    });
                }
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