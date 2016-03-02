(function(settings){
    'use strict';
    settings = settings || {};

    var pictureService = angular.module('pictureModule', []);

    pictureService.factory("pictureObject", ["loadFile", "$timeout", function(loadFile, $timeout){
        return function(inputFile, imageSettings, inObjectForImage, setterName) {

            var setterName = setterName || 'setPicture';

            if (!(setterName in inObjectForImage)) {
                throw new Error(setterName+' not found.');
            }

            var objectForImage = inObjectForImage,
                self           = this;

            this.setObjectImage = function(newImageObject){
                objectForImage = newImageObject;
            };

            this.choosePhoto = function(){
                inputFile.click();
            };

            inputFile.on('change',function(e) {
                loadFile(e).then(function(base64) {
                    objectForImage[setterName](base64);
                    self.cropperInit(imageSettings);
                });
            });

            this.getBetween = function(number, max, min){
                if(number > max){
                    return max;
                }

                return number < min? min: number;
            };

            this.cropperDestroy = function() {
                imageSettings.container.cropper("destroy");
            };

            this.cropperInit = function(imageInfo){
                $timeout(function(){
                    imageInfo.container.cropper('destroy');
                    imageInfo.container.cropper(imageInfo.options);
                });
            };

            this.validateNaturalSize = function(widthMin, heightMin) {

                var imageData = imageSettings.container.cropper("getImageData");
                return (imageData.naturalWidth >= widthMin && imageData.naturalHeight >= heightMin);
            };

            this.prepareFixedHeightImage = function(height){
                var info = imageSettings.container.cropper("getCropBoxData");
                if(!("width" in info)){
                    return null;
                }

                var result = imageSettings.container.cropper("getCroppedCanvas",
                    {
                        "height": height
                    })
                    .toDataURL("image/jpeg");
                if (result !== null) {
                    objectForImage[setterName](result);
                }
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

                if (result !== null) {
                    objectForImage[setterName](result);
                }
            }
        }
    }]);

})(settings);