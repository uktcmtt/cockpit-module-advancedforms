(function($){

    App.module.controller("form", function($scope, $rootScope, $http){

        var id = $("[data-ng-controller='form']").data("id");


        if (id) {

            $http.post(App.route("/api/advancedforms/findOne"), {filter: {"_id":id}}, {responseType:"json"}).success(function(data){

                if (data && Object.keys(data).length) {
                    $scope.form = data;
                }

            }).error(App.module.callbacks.error.http);

        } else {

            $scope.form = {
                name: "",
                email: "",
                extensions: "jpg,jpeg,png,pdf,gif,xls,xlsx,docx,doc,txt,tiff,tif,mp3,mpeg,mpg,wav", 
                emailSubject: "",               
                entry: true,
                useCaptcha: false,
                noCaptchaLanguage: "",
                noCaptchaRequired: "",
                maxFileSizeMessage: "The maximum file size you can attach is 15MB",
                maxFileSize: 15,
                before: "",
                after: ""
            };
        }

        $scope.extensionsChanged = function() {
             $scope.form.extensions = $scope.form.extensions.replace(/[\. :-]+/g, "").toLowerCase();
        };

        $scope.save = function() {   

            $scope.form.extensions = $scope.form.extensions.replace(/(\, *$)/,"");        

            var form = angular.copy($scope.form);

            $http.post(App.route("/api/advancedforms/save"), {"form": form}).success(function(data){

                if (data && Object.keys(data).length) {
                    $scope.form = data;
                    App.notify(App.i18n.get("Form saved!"), "success");
                }

            }).error(App.module.callbacks.error.http);
        };

        // bind clobal command + save
        Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {
            if (e.preventDefault) {
                e.preventDefault();
            } else {
                e.returnValue = false; // ie
            }
            $scope.save();
            return false;
        });

    });

})(jQuery);