angular.module('starter.controllers', [])

.controller('EnterCodeCtrl', function($scope, $location, $state, Basket) {
    $scope.loading = false;
    $scope.load_project = function(project_code){
        Basket.user_name = this.user_name;
        Basket.load_project(project_code);
        $scope.$on('project.update', function(){
            if($location.path() != '/enter-code') return;
             $state.transitionTo('miniforms');
        });
        $scope.$on('entercode.wrong', function(){
            $scope.error_msg = "Wrong Code";
        });
        $scope.$on('entercode.nonetwork', function(){
            $scope.error_msg = "No Network Connection";
        });
    }
})

.controller('MiniFormsCtrl', function($scope, Basket) {
    Basket.refresh_project_structure();
    $scope.$on('project.update', function(){
          $scope.baskets = Basket.baskets;
    });
    $scope.baskets = Basket.baskets;
    $scope.reponse_count = function (basket_id){
        var count;
        if(Basket.response_count[basket_id] === undefined){
            count = 0;
        }
        else{
            count = Basket.response_count[basket_id];
        }
        return count;
    };
    $scope.sync = function(){
        Basket.sync_responses();
        Basket.load_project();
        $scope.syncing = true;
    };
    $scope.$on('sync.complete', function(){
        $scope.syncing = false;
        $scope.sync_msg = 'sync complete';
    });
    $scope.$on('sync.error', function(){
        $scope.sync_msg = 'No Network Connection';
    });
    $scope.get_unsynced_number = function(){
        return Basket.responses.length;
    };
})

.controller('QuestionsCtrl', function($scope, $stateParams, Basket, $state) {
    $scope.questions = Basket.get_questions($stateParams.basketId);
    $scope.submit_data = function(){
        Basket.sync_responses($scope.questions);
        $state.transitionTo('miniforms');
//        console.log($scope.questions);
    };
    $scope.set_location = function(question){
        question.msg="Getting Location...";
        navigator.geolocation.getCurrentPosition(function(position){
            question.latData = position.coords.latitude;
            question.lngData = position.coords.longitude;
            question.msg='';
        });
    };
    $scope.get_picture = function(question){
        var options =   {
            quality: 50,
            destinationType: Camera.DestinationType.DATA_URL,
            sourceType: 1,      // 0:Photo Library, 1=Camera, 2=Saved Photo Album
            encodingType: 0     // 0=JPG 1=PNG
        }
        navigator.camera.getPicture(function(imageData) {   // on success
            question.imageData = "data:image/jpeg;base64," +imageData;
        },function(){
            // when user cancelled the upload
        },options); 
    };
    
    $scope.get_options = function(question){
        var options = question.options.split('\n');
        var result = [];
        for(var i in options){
            if(/^\s*$/.test(options[i]) === false){
                result.push(options[i]);
            }
        }
        return result;
    };
})

.controller('FriendDetailCtrl', function($scope, $stateParams, Friends) {
  $scope.friend = Friends.get($stateParams.friendId);
})

.controller('AccountCtrl', function($scope) {
});
