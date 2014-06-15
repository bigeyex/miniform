<?php

class MiniFormAction extends Action {
    // pages
    public function manage(){
        $this->need_login();
        $projects = O('ask_project')->withuser()->select(); 
        
        if(isset($_GET['id'])){
            $project_info = O('ask_project')->withuser()->find($_GET['id']);
        }
        else{
            $project_info = O('ask_project')->withuser()->find();
        }
        
        if(!$project_info){
            $this->display('no_project');
            return;
        }
        
        $baskets = O('ask_basket')->with('project_id', $project_info['id'])->fetch('ask_question', 'basket_id')->select();

        $this->assign('projects', $projects);
        $this->assign('this_project', $project_info);
        $this->assign('project_info', json_encode($project_info));
        $this->assign('baskets', json_encode($baskets));
        $this->display('manage');
    }
    
    public function view_data($id){
        $this->need_login();
        
        // the id in this context is basket id
        $basket_info = O('ask_basket')->find($id); 
        $questions = O('ask_question')->with('basket_id', $id)->select();
        $projects = O('ask_project')->withuser()->select(); 
        $project_info = O('ask_project')->withuser()->find($basket_info['project_id']);
        
        // fetch all responses
        $responses = O('ask_response')->with('basket_id', $id)->fetch('ask_answer', 'response_id')->select();
        for($i=0;$i<count($responses);$i++){
            $ask_answer = array();
            for($j=0;$j<count($responses[$i]['ask_answer']);$j++){
                $ask_answer[$responses[$i]['ask_answer'][$j]['question_id']] = $responses[$i]['ask_answer'][$j];
            }
            $responses[$i]['ask_answer'] = $ask_answer;
        }
        
        $this->assign('projects', $projects);
        $this->assign('this_project', $project_info);
        $this->assign('this_basket', $basket_info);
        $this->assign('responses', $responses);
        $this->assign('questions', $questions);
        $this->display('view_data');
    
    }

    public function new_project(){
        $name = $_POST['name'];
        do{
            $unique_id = substr(number_format(time() * rand(),0,'',''),0,8); 
            $existing_count = O('ask_project')->with('code', $unique_id)->count();
        } while($existing_count>0);
        $new_id = O('ask_project')->add(array('name'=>$name, 'code'=>$unique_id, 'user_id'=>user('id')));
        $this->redirect('MiniForm/manage', array('id'=>$new_id));
    }

    public function delete_project(){
        $this->need_to_be_owner('project', $_GET['id']);
        if(!isset($_GET['id'])) die('Error: Need to specify the project to be deleted');
        O('ask_project')->withuser()->with('id', $_GET['id'])->delete();
        
        $this->redirect('MiniForm/manage');
    }
    
    public function login_action(){
        $user = O('ask_user')->with('username', $_POST['username'])
                             ->with('password', md5(C('MD5_SALT').$_POST['password']))
                             ->find();
                             
        if($user){
            $_SESSION['login_user'] = $user;
            $this->redirect('MiniForm/manage');
        }
        else{
            flash('User ID and Password do not match');
            $this->redirect('MiniForm/index');
            return;
        }
    }
    
    public function new_user(){
        // check if user already exist
        $existing_count = O('ask_user')->with('username', $_POST['username'])->count();
        if($existing_count > 0){
            flash('User already exists');
            $this->redirect('MiniForm/index');
            return;
        }
        
        $new_id = O('ask_user')->add(array(
            'fullname' => $_POST['fullname'],
            'username' => $_POST['username'],
            'password' => md5(C('MD5_SALT') . $_POST['password']),
        ));
        
        if($new_id){
            $_SESSION['login_user'] = O('ask_user')->find($new_id);
            $this->redirect('MiniForm/manage');
        }
    }
    
    public function logout(){
        unset($_SESSION['login_user']);
        $this->redirect('index');
    }
    
    public function odk_xform($id){
        import('Common.stopwords',APP_PATH,'.php');
        C('TMPL_STRIP_SPACE', false);
        $stop_words = StopWords::get();
        
        $basket = O('ask_basket')->find($id); 
        $questions = O('ask_question')->with('basket_id', $id)->select();
        
        $basket['uidname'] = preg_replace('/[\s]+/', '', ucwords($basket['name']));
        
        // generate uid for each questions
        $uid_list = array();
        for($i=0;$i<count($questions);$i++){
            $word_list = explode(' ', preg_replace('/[^\w^\s]+/', '', $questions[$i]['name']));
            $result_uid = '';
            $word_length = 0;       // the word length for current uid
            for($j=0;$j<count($word_list);$j++){
                if($word_length>2 && !in_array($result_uid, $uid_list)){
                    break;
                }
                if(!in_array($word_list[$j], $stop_words)){
                    $result_uid.=ucwords($word_list[$j]);
                    $word_length++;
                }
            }
            // if all the words in question name is used, and still can't find an unique id
            $extra_number = 1;
            $org_uid = $result_uid;
            while(in_array($result_uid, $uid_list)){
                $result_uid = $org_uid . $extra_number;
                $extra_number++;
            }
            
            // now we have an unique id
            $uid_list[] = $result_uid;
            $questions[$i]['uid'] = $result_uid;
        }
        // END OF Uid generation code
        
        // generate the translation strings
        $translation = array();
        foreach($questions as $question){
            $translation[$question['uid']] = $question['name'];
            $options = explode("\n", $question['options']);
            foreach($options as $option){
                if(!empty($option)){
                    $tag = preg_replace('/[\s]+/', '', $option);
                    $translation[$option] = $tag;
                }
            }
        }
        // END of generating translation
        
        
        header('Content-Disposition: attachment;filename="form.xml"');
        $this->assign('basket', $basket);
        $this->assign('translation', $translation);
        $this->assign('questions', $questions);
        $this->display('odk_xform','','application/xml');
    }
    
    // json apis
    
    public function get_answers(){
        //TODO: get answers
        $responses = O('ask_response')->with('basket_id', $id)->fetch('ask_answer', 'response_id')->select();
        print_r($responses);die();
    }
    
    public function rename_project(){
        $this->need_to_be_owner('project', $_POST['id']);
        $result = O('ask_project')->withuser()->with('id', $_POST['id'])->save(array('name'=>$_POST['name']));
        if($result){
            echo 'ok';
        }
    }
    
    public function new_basket(){
        $this->need_to_be_owner('project', $_POST['project_id']);
        $new_id = O('ask_basket')->add(array('name'=>$_POST['name'], 'project_id'=>$_POST['project_id']));
        echo $new_id;
    }
    
    public function rename_basket(){
        $this->need_to_be_owner('basket', $_POST['id']);
        $new_id = O('ask_basket')->with('id', $_POST['id'])->save(array('name'=>$_POST['name']));
        if($new_id){
            echo 'ok';
        }
    }
    
    public function delete_basket(){
        $this->need_to_be_owner('basket', $_POST['id']);
        O('ask_basket')->with('id', $_POST['id'])->delete();
        echo 'ok';
    }
    
    public function add_question(){
        $this->need_to_be_owner('basket', $_POST['basket_id']);
        $new_id = O('ask_question')->add(array('name'=>$_POST['name'], 'basket_id'=>$_POST['basket_id'], 'questionType'=>$_POST['type']));
        echo $new_id;
    }
    
    public function save_question(){
        $this->need_to_be_owner('question', $_POST['id']);
        $new_id = O('ask_question')->with('id', $_POST['id'])->save(array('name'=>$_POST['name'], 'questionType'=>$_POST['type']));
        echo $new_id;
    }
    
    public function delete_question(){
        $this->need_to_be_owner('question', $_POST['id']);
        O('ask_question')->with('id', $_POST['id'])->delete();
        echo 'ok';
    }
    
    public function save_options(){
        $this->need_to_be_owner('question', $_POST['id']);
        O('ask_question')->save(array('id'=>$_POST['id'], 'options'=>$_POST['options']));
        echo 'ok';
    }
    
    // client apis
    public function load_project($code){
        $project = O('ask_project')->with('code', $code)->find();
        if(!$project) return json_encode(array('error'=>'Code Incorrect'));
        $baskets = O('ask_basket')->with('project_id', $project['id'])->fetch('ask_question', 'basket_id')->select();
        header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        header('Content-type: application/javascript');
//        echo $_GET['callback'].'('.json_encode(array('project'=>$project, 'baskets'=>$baskets)).');';
        echo json_encode(array('project'=>$project, 'baskets'=>$baskets));
    }
    
    public function save_responses(){
       header('Content-type: application/javascript');
       header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
       header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
//       $decoded_data = json_decode($_GET['data'], true);
       $decoded_data = $_POST['data'];
       if(is_array($decoded_data)){
            $user_name = $_POST['user_name'];
            foreach($decoded_data as $response){
                if(is_array($response) && count($response)>0){
                    $response_id = O('ask_response')->add(array('basket_id'=>$response[0]['basket_id']));
                    foreach($response as $answer){
                        $answer_record = array(
                            'userName' => $user_name,
                            'response_id' => $response_id,
                            'question_id' => $answer['id']
                        );
                        switch($answer['questionType']){
                            case 'yesno':
                            case 'number':
                                $answer_record['numberData'] = $answer['numberData'];
                                break;
                            case 'text':
                            case 'options':
                                $answer_record['textData'] = $answer['textData'];
                                break;
                            case 'location':
                                $answer_record['lngData'] = $answer['lngData'];
                                $answer_record['latData'] = $answer['latData'];
                                break;
                            case 'image':
                                $answer_record['imageData'] = $answer['imageData'];
                        }
                        O('ask_answer')->add($answer_record);
                    }
                }   // if response is valid
            }   // foreach response
//            echo $_GET['callback'].'({"ret":"ok"});';
            echo '{"ret":"ok"}';
       }    // if data is valid
       else{
//           echo $_GET['callback'].'({"ret":"error"});';
             echo '{"ret":"error"}';
       }
    }
        
    public function upload_photo(){
    
    }
        
    
    private function need_to_be_owner($type, $id){
        $this->need_login();
        if(!is_numeric($id))die('Please contact administrator for this error');
        switch($type){
            case 'project':
                $cnt = O()->query('select count(id) cnt from ask_project where ask_project.id='.intval($id).' and user_id='.user('id'));
                break;
            case 'basket':
                $cnt = O()->query('select count(id) cnt from ask_basket where ask_basket.id='.intval($id).' and ask_basket.project_id in (select id from ask_project where user_id='.user(id).')');
                break;
            case 'question':
                $cnt = O()->query('select count(id) cnt from ask_question where ask_question.id='.intval($id).' and ask_question.basket_id in (select id from ask_basket where ask_basket.project_id in (select id from ask_project where user_id='.user('id').'))');
                break;
        }
        
        if(intval($cnt[0]['cnt']) == 0){
            flash('Sorry, you cannot edit this item');
            $this->redirect('Miniform/index');
            die();
        }
    }
    
    private function need_login(){
        if(!user()){
            flash('Please Log In First');
            $this->redirect('Miniform/index');
            die();
        }
    }
}   