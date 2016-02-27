/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminCollateralForm',
    ['waitingScreen', 'renderMessage', 'sessionMessages', '$anchorScroll', '$http', 'loadFile', '$q', 'USA_STATES',
        function (waitingScreen, renderMessage, sessionMessages, $anchorScroll, $http, loadFile, $q, USA_STATES)
{
    return {
        restrict   : 'EA',
        templateUrl: 'template/directive/admin-collateral-form.html',
        scope: {
            template: '=loTemplate'
        },
        link: function (scope, element, attrs, controllers) {
            // Variables
            scope.message = angular.element('#message-box');
            scope.hideErrors = true;
            scope.formats = [];
            scope.categories = [];
            scope.states = USA_STATES;

            scope.$watch('template.id', function (newVal, oldVal) {
                scope.title = newVal ? 'Edit Template' : 'Add Template';
            });

            // Get options
            var categories = $http.get('/request/template/categories', {cache: true}),
                formats    = $http.get('/request/template/formats', {cache: true});
            $q.all([categories, formats]).then(function (response) {
                scope.categories = response[0].data;
                if (undefined !== scope.categories[0] && scope.categories[0].hasOwnProperty('id')) {
                    scope.template.category_id = scope.categories[0].id;
                }

                scope.formats = response[1].data;
                if (undefined !== scope.formats[0] && scope.formats[0].hasOwnProperty('id')) {
                    scope.template.format_id = scope.formats[0].id;
                }
            }).then(function () {
                // Get lenders
                $http.get('/admin/json/lenders', {cache: true}).success(function (data) {
                    scope.lenders = data;
                });
            });

            angular.element('#file-input').on('change', function (e) {
                loadFile(e).then(function (base64) {
                    scope.template.setFile(base64);
                });
            });

            scope.submit = function (form) {
                if (!form.$valid) {
                    scope.hideErrors = false;
                    $anchorScroll(scope.message.attr('id'));
                    return false;
                }

                waitingScreen.show();
                scope.template.save().then(function () {
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

                    renderMessage(errors, 'danger', scope.message, scope);
                    scope.hideErrors = false;
                    $anchorScroll(scope.message.attr('id'));
                }).finally(function () {
                    waitingScreen.hide();
                });
            };

            scope.cancel = function (e) {
                e.preventDefault();
                history.back();
            };

            scope.showErrors = function (e) {
                e.preventDefault();
                scope.hideErrors = true;
            };

            scope.delete = function (e) {
                e.preventDefault();
                if (!confirm('Are you sure?')) {
                    return false;
                }

                scope.template.delete();
                scope.template.clear();
                sessionMessages.addSuccess('Template was deleted');
                history.back();
            }
        }
    }
}]);
