<?php

$hook = array(

    'hook'           => 'UserChangePassword',

    'function'       => 'UserChangePassword',

    'description'    => array(

        'english'    => 'After client change password'

    ),

    'type'           => 'client',

    'extra'          => '',

    'variables'      => '{firstname},{lastname},{password}',

    'defaultmessage' => 'Hi {firstname} {lastname},password has been changed successfully.',

);



if(!function_exists('UserChangePassword')){
    function UserChangePassword($args){
		
        $class    = new Sms();

        $template = $class->getTemplateDetails(__FUNCTION__);

        if($template['active'] == 0){

            return null;

        }

        $settings = $class->getSettings();

        if(!$settings['api'] || !$settings['apiparams'] ){

            return null;

        }

        $result   = $class->getClientDetailsBy($args['userid']);

        $num_rows = mysql_num_rows($result);

        if($num_rows == 1){

            $UserInformation       = mysql_fetch_assoc($result);

            $template['variables'] = str_replace(" ","",$template['variables']);

            $replacefrom           = explode(",",$template['variables']);

            $replaceto             = array($UserInformation['firstname'],$UserInformation['lastname'],$args['password']);

            $message               = str_replace($replacefrom,$replaceto,$template['template']);

            $class->setGsmnumber($UserInformation['gsmnumber']);

            $class->setUserid($UserInformation['id']);

            $class->setMessage($message);

            $class->send();

        }

    }

}



return $hook;