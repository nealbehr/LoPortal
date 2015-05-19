(function(settings){
    'use strict';
    settings = settings || {};

    var pictureService = angular.module('pictureModule', []);

    pictureService.factory("pictureObject", ["loadFile", "$timeout", function(loadFile, $timeout){
        return function(inputFile, imageSettings, inObjectForImage){
            if(!("setPicture" in inObjectForImage)){
                throw new Error("setPicture not found.");
            }

            var objectForImage = inObjectForImage;

            var self = this;

            this.setObjectImage = function(newImageObject){
                objectForImage = newImageObject;
            };

            this.choosePhoto = function(){
                inputFile.click();
            };

            inputFile.on('change',function(e){
                loadFile(e)
                    .then(function(base64){
                        objectForImage.setPicture(base64);
                        self.cropperInit(imageSettings);
                    })
                ;
            });

            this.getBetween = function(number, max, min){
                if(number > max){
                    return max;
                }

                return number < min? min: number;
            };

            this.cropperInit = function(imageInfo){
                $timeout(function(){
                    imageInfo.container.cropper('destroy');
                    imageInfo.container.cropper(imageInfo.options);
                });
            };

            this.prepareImage = function(heightMax, heightMin, widthMax, widthMin){
                var info = imageSettings.container.cropper("getCropBoxData");
                if(!("width" in info)){
                    return null;
                }

                var result = imageSettings.container.cropper("getCroppedCanvas",
                    { "width": this.getBetween(info.width, widthMax, widthMin),
                        "height": this.getBetween(info.height, heightMax, heightMin)
                    })
                    .toDataURL("image/jpeg");
                if(result !== null){
                    objectForImage.setPicture(result);
                }
            }
        }
    }]);

})(settings);