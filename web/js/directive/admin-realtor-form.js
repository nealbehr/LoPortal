/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminRealtorForm',
    ['waitingScreen', 'renderMessage', 'sessionMessages', '$anchorScroll', 'pictureObject', '$http',
        function (waitingScreen, renderMessage, sessionMessages, $anchorScroll, pictureObject, $http)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/admin-realtor-form.html',
        scope: {realtor: '=loRealtor'},
        link: function (scope, element, attrs, controllers) {
            scope.container = angular.element('#realtorMessage');
            scope.realtorPicture = {};
            scope.realtyLogo = {};
            scope.realtyCompanies = [];
            scope.hideErrors = true;

            scope.$watch('realtor.id', function (newVal, oldVal) {
                scope.title = newVal ? 'Edit Realtor' : 'Add Realtor';
            });

            scope.$watch('realtor', function (newVal, oldVal) {
                if (newVal == undefined || !('id' in newVal)) {
                    return;
                }
                scope.realtorPicture = new pictureObject(
                    angular.element('#realtorImage'),
                    {
                        container: $('.realtor.realtor-photo > img'),
                        options: {aspectRatio: 3 / 4, minContainerWidth: 100}
                    },
                    scope.realtor,
                    'setPhoto'
                );

                scope.realtyLogo = new pictureObject(
                    angular.element('#realtyLogo'),
                    {
                        container: $('#realtyLogoImage'),
                        options: {
                            minCropBoxWidth: 100,
                            minCropBoxHeight: 100,
                            maxCropBoxWidth: 350,
                            maxCropBoxHeight: 100
                        }
                    },
                    scope.realtor,
                    'setRealtyLogo'
                );
            });

            scope.showErrors = function (e) {
                e.preventDefault();
                this.hideErrors = true;
            };

            scope.gotoErrorMessage = function () {
                $anchorScroll(scope.container.attr('id'));
            };

            scope.submit = function (formSalesDirector, $event) {
                if (scope.realtorPicture && scope.realtor.photo !== null) {
                    scope.realtorPicture.prepareImage(800, 400, 600, 300);
                }
                if (!formSalesDirector.$valid) {
                    this.hideErrors = false;
                    this.gotoErrorMessage();
                    return false;
                }
                this.save();
            };

            scope.save = function () {
                waitingScreen.show();

                scope.realtor.save().then(function (data) {
                    sessionMessages.addSuccess('Successfully saved.');
                    history.back();
                }).catch(function (data) {
                    var errors = '';
                    if ('message' in data) {
                        errors += data.message + ' ';
                    }

                    if ('form_errors' in data) {
                        errors += data.form_errors.join(' ');
                    }

                    renderMessage(errors, 'danger', scope.container, scope);
                    scope.gotoErrorMessage();
                }).finally(function () {
                    waitingScreen.hide();
                });
            };

            scope.cancel = function (e) {
                e.preventDefault();
                history.back();
            };

            scope.delete = function (e) {
                e.preventDefault();
                if (!confirm('Are you sure?')) {
                    return false;
                }

                waitingScreen.show();
                scope.realtor.delete().then(function () {
                    sessionMessages.addSuccess('Sales director was deleted.');
                    scope.realtor.clear();
                    history.back();
                }).catch(function (data) {
                    renderMessage(data.message, 'danger', scope.container, scope);
                }).finally(function () {
                    waitingScreen.hide();
                });
            };

            $http.get('/admin/realty/all').success(function (data) {
                if (scope.realtor
                    && scope.realtor.hasOwnProperty('realty_company_id')
                    && scope.realtor.realty_company_id !== null
                ) {
                    scope.realtor.realty_company_id += '';
                }
                scope.realtyCompanies = data;
            });
        }
    }
}
]);
