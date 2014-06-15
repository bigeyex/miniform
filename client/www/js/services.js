angular.module('starter.services', [])

/**
 * A simple example service that returns some data.
 */

.factory('Basket', function($rootScope, $http) {
    var service = {
        base_url : 'http://wangyu.scripts.mit.edu/miniform/MiniForm',
        project_id : 0,
        project_code : 0,
        project_name : '',
        error_message : '',
        user_name: '',
        baskets : [],
        questions : [],
        responses: [],
        response_count: {},
        
        get_questions : function(basket_id){
            result = [];
            for(var i in this.questions){
                if(this.questions[i].basket_id == basket_id){
                    result.push(this.questions[i]);            
                }
            }
            return result;
        },
         
        refresh_project_structure : function(){
            if(this.project_code == 0 && localStorage['project_code'] !== undefined){
                // load local storage
//                this._load_local_storage();
                this.load_project(localStorage['project_code']);
            }
        },
        
        load_project : function(project_code){
            var self = this;
            if(project_code === undefined && localStorage['project_code'] !== undefined){
                project_code = localStorage['project_code'];
                self.user_name = localStorage['user_name'];
            }
            else{
                localStorage['user_name'] = self.user_name;
            }
            $http.get(self.base_url+'/load_project/code/'+project_code+'?callback=JSON_CALLBACK').success(function(data){
                if(data.project !== undefined){
                    self.project_name = data.project.name;
                    self.project_code = data.project.code;
                    self.baskets = [];
                    self.questions = [];
                    for(var bi in data.baskets){
                        basket = data.baskets[bi];
                        self.baskets.push({'id':basket.id, 'name':basket.name});
                        for(var qi in basket.ask_question){
                            self.questions.push(basket.ask_question[qi]);
                        }
                    }
                    localStorage['project_code'] = self.project_code;
                    localStorage['local_data'] = JSON.stringify({
                        'project_name':self.project_name,
                        'project_code':self.project_code,
                        'baskets':self.baskets,
                        'questions':self.questions,
                    })
//                    if(localStorage['local_responses'] !== undefined){
//                        self.responses = JSON.parse(localStorage['local_responses']);
//                    }
                    $rootScope.$broadcast('project.update');
                }
                else{
                    this.error_message = 'Wrong code';
                }
            }).error(function(){
                self._load_local_storage();
            });
            if(localStorage['response_count'] !== undefined){
                self.response_count = JSON.parse(localStorage['response_count']);
            }
            
        },
        
        sync_responses: function(response){
              //TODO: sync responses
            var self = this;
            if(response !== undefined){
                self.responses.push(response);
                var basket_id = response[0]['basket_id'];
                if(basket_id !== undefined){
                    if(self.response_count[basket_id]===undefined){
                        self.response_count[basket_id] = 0;
                    }
                    self.response_count[basket_id]++;
                }
            }
            console.log(self.responses);
            $http.post(self.base_url+'/save_responses', {data: self.responses, user_name: self.user_name}).success(function(result){
                if(result.ret == 'ok'){
                    self.responses = [];
                }
                $rootScope.$broadcast('sync.complete');
            }).error(function(){
                // if offline, do nothing and store all response to next time.
                $rootScope.$broadcast('sync.error');
            });
            localStorage['local_responses'] = JSON.stringify(self.responses);
            localStorage['response_count'] = JSON.stringify(self.response_count);
            
        },
        
        _load_local_storage: function(){
            var self = this;
            if(localStorage['project_code'] !== undefined){
                local_data = JSON.parse(localStorage['local_data']);
                self.project_name = local_data.project_name;
                self.project_code = local_data.project_code;
                self.baskets = local_data.baskets;
                self.questions = local_data.questions;
                if(localStorage['local_responses'] !== undefined){
                    self.responses = JSON.parse(localStorage['local_responses']);
                }
                console.log('loaded from local');
                $rootScope.$broadcast('project.update');
            }
        }
        
        
    };
    return service;
})

.factory('Friends', function($rootScope) {
  // Might use a resource here that returns a JSON array

  // Some fake testing data
  var friends = [
    { id: 0, name: 'Scruff McGruff' },
    { id: 1, name: 'G.I. Joe' },
    { id: 2, name: 'Miss Frizzle' },
    { id: 3, name: 'Ash Ketchum' }
  ];

  return {
    all: function() {
        $rootScope.$broadcast('friends.update');
      return friends;
    },
    get: function(friendId) {
      // Simple index lookup
      return friends[friendId];
    }
  }
});
