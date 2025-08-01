<?php
$hook = array(
    'hook'           => 'InvoiceCreated',
    'function'       => 'InvoiceCreated',
    'description'    => array(
        'english'    => 'After Invoice Creation'
    ),
    'type'           => 'client',
    'extra'          => '',
    'defaultmessage' => 'Hello {firstname} {lastname}, Your invoice with id {invoiceid} has been generated.Total amount is  {total}.The last day of payment is {duedate}.Kindly pay your bill before due date to use services without interruption.',
    'variables'      => '{firstname},{lastname},{date},{duedate},{total},{invoiceid}'
);
if(!function_exists('InvoiceCreated')){
    function InvoiceCreated($args){
        $class    = new Sms();
        $template = $class->getTemplateDetails(__FUNCTION__);
        if($template['active'] == 0){
            return null;
        }
        $settings = $class->getSettings();
        if(!$settings['api'] || !$settings['apiparams']){
            return null;
        }
        $result   = $class->getClientAndInvoiceDetailsBy($args['invoiceid']);
        $num_rows = mysql_num_rows($result);
        if($num_rows == 1){
            $UserInformation = mysql_fetch_assoc($result);
            $template['variables'] = str_replace(" ","",$template['variables']);
            $replacefrom     = explode(",",$template['variables']);
            $replaceto       = array($UserInformation['firstname'],$UserInformation['lastname'],$class->changeDateFormat($UserInformation['date']),$class->changeDateFormat($UserInformation['duedate']),$UserInformation['total'],$args['invoiceid']);
            $message         = str_replace($replacefrom,$replaceto,$template['template']);
            $class->setGsmnumber($UserInformation['gsmnumber']);
            $class->setMessage($message);
            $class->setUserid($UserInformation['userid']);
            $class->send();
        }
    }
}
return $hook;