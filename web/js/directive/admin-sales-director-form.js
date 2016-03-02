/**
 * Created by Eugene Lysenko on 2/27/16.
 */
angular.module('loApp').directive(
    'loAdminSalesDirectorForm',
    ['waitingScreen', 'renderMessage', 'sessionMessages', '$anchorScroll',
        function (waitingScreen, renderMessage, sessionMessages, $anchorScroll)
{
    return {
        restrict: 'EA',
        templateUrl: 'template/directive/admin-sales-director-form.html',
        scope: {salesDirector: '=loSalesDirector'},
        link: function (scope, element, attrs, controllers) {
            scope.container = angular.element('#salesDirectorMessage');
            scope.hideErrors = true;

            scope.$watch('salesDirector.id', function (newVal, oldVal) {
                scope.title = newVal ? 'Edit Sales Director' : 'Add Sales Director';
            });

            scope.isValidEmail = function (form) {
                if (!form.email) {
                    return;
                }

                return (form.$submitted || form.email.$touched)
                    && (form.email.$error.email || form.email.$error.required);
            };

            scope.showErrors = function (e) {
                e.preventDefault();
                this.hideErrors = true;
            };

            scope.gotoErrorMessage = function () {
                $anchorScroll(scope.container.attr('id'));
            };

            scope.submit = function (formSalesDirector, $event) {
                if (!formSalesDirector.$valid) {
                    this.hideErrors = false;
                    this.gotoErrorMessage();
                    return false;
                }
                this.save();
            };

            scope.save = function () {
                waitingScreen.show();

                scope.salesDirector.save().then(function (data) {
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
                scope.salesDirector.delete().then(function () {
                    sessionMessages.addSuccess('Sales director was deleted.');
                    scope.salesDirector.clear();
                    history.back();
                }).catch(function (data) {
                    renderMessage(data.message, 'danger', scope.container, scope);
                }).finally(function () {
                    waitingScreen.hide();
                });
            };
        }
    }
}
]);
