<?php
$hook = array(
    'hook'           => 'AfterModuleSuspend',
    'function'       => 'AfterModuleSuspend',
    'description'    => array(
        'english'    => 'After Module Suspension'
    ),
    'type'           => 'client',
    'extra'          => '',
    'defaultmessage' => 'Hi {firstname} {lastname},The service for your account associated with the domain {domain} has been paused.Kindly contact us for more details.',
    'variables'      => '{firstname},{lastname},{domain}'
);

if(!function_exists('AfterModuleSuspend')){
    function AfterModuleSuspend($args){
        $type = $args['params']['producttype'];
        if($type == "hostingaccount"){
            $class    = new Sms();
            $template = $class->getTemplateDetails(__FUNCTION__);
            if($template['active'] == 0){
                return null;
            }
            $settings = $class->getSettings();
            if(!$settings['api'] || !$settings['apiparams'] ){
                return null;
            }
        }else{
            return null;
        }
        $result   = $class->getClientDetailsBy($args['params']['clientsdetails']['userid']);
        $num_rows = mysql_num_rows($result);
        if($num_rows == 1){
            $UserInformation       = mysql_fetch_assoc($result);
            $template['variables'] = str_replace(" ","",$template['variables']);
            $replacefrom           = explode(",",$template['variables']);
            $replaceto             = array($UserInformation['firstname'],$UserInformation['lastname'],$args['params']['domain']);
            $message               = str_replace($replacefrom,$replaceto,$template['template']);
            $class->setGsmnumber($UserInformation['gsmnumber']);
            $class->setUserid($args['params']['clientsdetails']['userid']);
            $class->setMessage($message);
            $class->send();
        }
    }
}
return $hook;