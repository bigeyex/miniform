<?php
return array(
	'SESSION_AUTO_START'=>true,
    'URL_MODEL' => 2,
    'URL_HTML_SUFFIX'=>'',
    
    "LOAD_EXT_FILE"=>"htmlhelpers,auth,qndmodel",
    'LOAD_EXT_CONFIG' => 'db,content,credentials',
    'VAR_PAGE' => 'p',
    'MD5_SALT' => 'flwei^e417',
    
    'ADMIN_ROW_LIST' => 20,
    'PAGE_ROLLPAGE' => 10,
    'LIST_RECORD_PER_PAGE' => 20,
    'RECORD_PER_POST_WIDGET' => 5,
    'VAR_PAGE' => 'p',
    'OAUTH2_CODES_TABLE'=>'oauth_code',  
    'OAUTH2_CLIENTS_TABLE'=>'oauth_client',  
    'OAUTH2_TOKEN_TABLE'=>'oauth_token',  
    'OAUTH2_DB_DSN'=>'mysql://root:@localhost:3306/ngo20map',
    
    // app debug
    'APP_DEBUG' => true,
    'LOG_RECORD' => true,
    'SHOW_ERROR_MSG' => true,
    'OUTPUT_ENCODE' => false,
    'TMPL_CACHE_ON' => false,
    'HTML_CACHE_ON'=>false
);
?>