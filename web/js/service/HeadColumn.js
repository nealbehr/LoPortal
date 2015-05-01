(function(settings){
    'use strict';
    settings = settings || {};

    var headColumn = angular.module('headColumnModule', []);

    headColumn.factory('tableHeadColSample', ['tableHeadColBase', function(tableHeadColBase){
        function headCol(params){
            var self = new tableHeadColBase(params);

            self.sort = function(){
                if(this.key == this.getScope()[this.getSortKey()]){
                    this.getScope()[this.getDirectionKey()] = !this.getScope()[this.getDirectionKey()];
                    return;
                }

                this.getScope()[this.getSortKey()] = this.key;
                this.getScope()[this.getDirectionKey()] = false;
            }

            self.isSortedUp = function(){
                return this.key == this.getScope()[this.getSortKey()] && this.getScope()[this.getDirectionKey()] == true;
            }

            self.isSortedDown = function(){
                return this.key == this.getScope()[this.getSortKey()] && this.getScope()[this.getDirectionKey()] == false;
            }


            self.getScope = function(){
                if(!("scope" in this)){
                    throw new Error('Property scope have not set.');
                }

                return this.scope;
            }

            return self;
        }

        return headCol;
    }]);

    headColumn.factory('tableHeadCol', ['tableHeadColBase', '$location', function(tableHeadColBase, $location){
        function headCol(params){
            params = params || {}
            if(!('isSortable' in params)){
                params.isSortable = true;
            }

            var self = new tableHeadColBase(params);

            self.sort = function(){
                if(!this.isSortable){
                    return false;
                }

                var newLocationParams = {}

                newLocationParams[this.getDirectionKey()] = this.getDirection();

                newLocationParams[this.getSortKey()] = this.key;

                this.location.search(newLocationParams);
            }

            self.getLocationParams = function(){
                return this.location.search();
            }

            self.getDirection = function(){
                if(this.getLocationParams()[this.getSortKey()] == undefined && this.key == this.getDefaultSortKey() || (this.getLocationParams()[this.getSortKey()] == this.key && this.getLocationParams()[this.getDirectionKey()] == undefined)){
                    console.log(1)
                    return "asc"
                }else if(this.getLocationParams()[this.getSortKey()] != this.key){
                    console.log(2)
                    return this.getDefaultDirection();
                }else{
                    console.log(3)
                    return this.getLocationParams()[this.getDirectionKey()] != undefined && this.getLocationParams()[this.getDirectionKey()] == "asc" ? "desc" : "asc";
                }
            }

            self.isSortedUp = function(){
                return this.isSortedDirection('desc');
            }

            self.isSortedDown = function(){
                return this.isSortedDirection('asc');
            }

            self.isSortedDirection = function(direction){
                if(!this.isCurrentlySorted()){
                    return false;
                }

                return (this.getLocationParams()[this.getDirectionKey()] || this.getDefaultDirection()).toLowerCase() == direction;
            }

            self.isCurrentlySorted = function(){
                return (this.getLocationParams()[this.getSortKey()] || this.getDefaultSortKey()) == this.key;
            }

            self.location = $location;

            return self;
        }

        return headCol;
    }]);

    headColumn.factory('tableHeadColBase', ['$sce', function($sce){
        function headCol(params){
            params = params || {}
            this.key;
            this.title;

            this.sort = function(){
                throw new Error("This method must be overridden.");
            }


            this.getDefaultDirection = function(){
                if(!("defaultDirection" in params)){
                    throw new Error('Property defaultDirection have not set.');
                }

                return params.defaultDirection;
            }

            this.getDefaultSortKey = function(){
                if(!("defaultSortKey" in params)){
                    throw new Error('Property defaultSortKey have not set.');
                }

                return params.defaultSortKey;
            }

            this.getDirectionKey = function(){
                if(!("directionKey" in params)){
                    throw new Error('Property directionKey have not set.');
                }

                return params.directionKey;
            }

            this.getSortKey = function(){
                if(!("sortKey" in params)){
                    throw new Error('Property sortKey have not set.');
                }

                return params.sortKey;
            }

            //Init
            for(var i in params){
                this[i] = params[i];
            }

            this.title = this.sce.trustAsHtml(this.title);
        }

        headCol.prototype.sce      = $sce;

        return headCol;
    }]);
})(settings);