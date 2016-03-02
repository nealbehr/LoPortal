(function(settings){
    'use strict';
    settings = settings || {};

    var propertyApproval = angular.module('approvalModule', []);

    propertyApproval.service("createPropertyApproval", ["$q", "$http", "createPropertyApprovalBase", function($q, $http, createPropertyApprovalBase){
        return function(){
            var approval = new createPropertyApprovalBase();

            approval.save = function(){
                this.property.state = settings.queue.state.requested;
                return $http.post('/request/approval', this.getFields4Save());
            };

            return approval;
        }
    }]);

    propertyApproval.service("createAdminPropertyApproval", ["$q", "$http", "createPropertyApprovalBase", function($q, $http, createPropertyApprovalBase){
        return function(){
            var approval = new createPropertyApprovalBase();

            approval.get = function(id){
                if(this.id !== null){
                    return $q.when(this);
                }

                var deferred = $q.defer();
                $http.get('/admin/approval/' + id)
                    .success(function(data){
                        approval.id = id;
                        approval.fill(data);

                        deferred.resolve(approval)
                    })
                    .error(function(data){
                        deferred.reject(data);
                    })
                ;

                return deferred.promise;
            };

            approval.save = function(){
                return this.id? this.update(): this.add();
            };

            approval.update = function(){
                return $http.put('/admin/approval/' + this.id, this.getFields4Save())
            };

            approval.add = function(){
                throw new Error("ID not found");
            };

            return approval;
        }
    }]);

    propertyApproval.service("createPropertyApprovalBase", [function(){
        return function() {
            this.id       = null;
            this.property = {
                address  : '',
                apartment: null,
                state    : null,
                user_type: settings.queue.userType.seller
            };
            this.address  = {
                address  : null,
                city     : null,
                state    : null,
                zip      : null,
                apartment: null
            };

            this.fill = function(data){
                for(var i in data){
                    this[i] = data[i];
                }

                return this;
            };

            this.getFields4Save = function(){
                var result = {};
                for(var i in this){
                    if(typeof this[i] === "object" && this[i] !== null){
                        result[i] = this[i];
                    }
                }

                return result;
            };

            this.save = function(){
                throw new Error("Request save must be override");
            }
        }
    }]);
})(settings);
