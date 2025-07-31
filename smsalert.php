<?php
/* WHMCS SMS Addon with GNU/GPL Licence
 * SMS Alert - https://www.smsalert.co.in
 *
 *
 * 
 * Licence: GPLv3 (http://www.gnu.org/licenses/gpl-3.0.txt)
 * */ 
 //include("smsalert/vendor/autoload.php");
 require_once("smsclass.php");
 //require_once("smsalert/classes/smsalert.php"); 
 use SMSAlert\Lib\Smsalert\Smsalert;

$smsalert 	= new Smsalert();

 if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

function smsalert_config() 
{
    $configarray = array(
			"name"        => "SMS Alert",
			"description" => "WHMCS SMS Addon. You can see details from : https://www.smsalert.co.in",
			"version"     => "1.4",
			"author"      => "SMS Alert",
			"language"    => "english",
    );
    return $configarray;
}

function smsalert_activate() 
{
    $query = "CREATE TABLE IF NOT EXISTS `mod_SmsAlert_otp` (`id` int(11) NOT NULL AUTO_INCREMENT,`user_id` varchar(11) CHARACTER SET utf8 NOT NULL,`phone` varchar(20) CHARACTER SET utf8 NOT NULL,`verify` tinyint(1) DEFAULT 0,PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	mysql_query($query);
	
    $query = "CREATE TABLE IF NOT EXISTS `mod_SmsAlert_messages` (`id` int(11) NOT NULL AUTO_INCREMENT,`sender` varchar(40) NOT NULL,`to` varchar(15) DEFAULT NULL,`text` text,`phid` varchar(50) DEFAULT NULL,`status` varchar(100) DEFAULT NULL,`errors` text,`logs` text,`user` int(11) DEFAULT NULL,`datetime` datetime NOT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
	mysql_query($query);

    $query = "CREATE TABLE IF NOT EXISTS `mod_SmsAlert_settings` (`id` int(11) NOT NULL AUTO_INCREMENT,`api` varchar(40) CHARACTER SET utf8 NOT NULL,`apiparams` varchar(500) CHARACTER SET utf8 NOT NULL,`wantsmsfield` int(11) DEFAULT NULL,`gsmnumberfield` int(11) DEFAULT NULL,`resend_time` int(5) DEFAULT NULL,`dateformat` varchar(12) CHARACTER SET utf8 DEFAULT NULL,`version` varchar(6) CHARACTER SET utf8 DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	mysql_query($query);
	
    $query = "INSERT INTO `mod_SmsAlert_settings` (`api`, `apiparams`, `wantsmsfield`, `gsmnumberfield`, `resend_time`,`dateformat`, `version`) VALUES ('sms', '{\"senderid\":\"\",\"signature\":\"\",\"country_code\":\"\"}', 0, 0, 15,'%d.%m.%y','1.1.3');";
	mysql_query($query);

    $query = "CREATE TABLE IF NOT EXISTS `mod_SmsAlert_templates` (`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(50) CHARACTER SET utf8 NOT NULL,`type` enum('client','admin') CHARACTER SET utf8 NOT NULL,`admingsm` varchar(255) CHARACTER SET utf8 NOT NULL,`template` varchar(240) CHARACTER SET utf8 NOT NULL,`variables` varchar(500) CHARACTER SET utf8 NOT NULL,`active` tinyint(1) NOT NULL,`extra` varchar(3) CHARACTER SET utf8 NOT NULL,`description` text CHARACTER SET utf8,PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	mysql_query($query);
	
    //Creating hooks
	$class = new Sms();
    $class->checkHooks();
    return array('status'=>'success','description'=>'SMS Alert successfully activated :)');
}

function smsalert_deactivate() 
{
    $query = "DROP TABLE `mod_SmsAlert_templates`";
	mysql_query($query);
    $query = "DROP TABLE `mod_SmsAlert_settings`";
    mysql_query($query);
	$query = "DROP TABLE `mod_SmsAlert_otp`";
    mysql_query($query);
    $query = "DROP TABLE `mod_SmsAlert_messages`";
    mysql_query($query);

    return array('status'=>'success','description'=>'SMS Alert successfully deactivated :(');
}

function smsalert_upgrade($vars) 
{
    $version = $vars['version'];
    switch($version){
        case "1":
        case "1.0.1":
            $sql = "ALTER TABLE `mod_SmsAlert_messages` ADD `errors` TEXT NULL AFTER `status` ;ALTER TABLE `mod_SmsAlert_templates` ADD `description` TEXT NULL ;ALTER TABLE `mod_SmsAlert_messages` ADD `logs` TEXT NULL AFTER `errors` ;";
            mysql_query($sql);
        case "1.1":
            $sql = "ALTER TABLE `mod_SmsAlert_settings` CHANGE `apiparams` `apiparams` VARCHAR( 500 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;";
            mysql_query($sql);
        case "1.1.1":
        case "1.1.2":
            $sql = "ALTER TABLE `mod_SmsAlert_settings` ADD `dateformat` VARCHAR(12) NULL AFTER `gsmnumberfield`;UPDATE `mod_SmsAlert_settings` SET dateformat = '%d.%m.%y';";
            mysql_query($sql);
        case "1.1.3":
        case "1.1.4":
            $sql = "ALTER TABLE `mod_SmsAlert_templates` CHANGE `name` `name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `type` `type` ENUM( 'client', 'admin' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `admingsm` `admingsm` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `template` `template` VARCHAR( 240 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `variables` `variables` VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `extra` `extra` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
            mysql_query($sql);
            $sql = "ALTER TABLE `mod_SmsAlert_settings` CHANGE `api` `api` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `apiparams` `apiparams` VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `dateformat` `dateformat` VARCHAR( 12 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `version` `version` VARCHAR( 6 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
            mysql_query($sql);
            $sql = "ALTER TABLE `mod_SmsAlert_messages` CHANGE `sender` `sender` VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,CHANGE `to` `to` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `text` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `phid` `phid` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `status` `status` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `errors` `errors` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,CHANGE `logs` `logs` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;";
            mysql_query($sql);

            $sql = "ALTER TABLE `mod_SmsAlert_templates` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            mysql_query($sql);
            $sql = "ALTER TABLE `mod_SmsAlert_settings` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            mysql_query($sql);
            $sql = "ALTER TABLE `mod_SmsAlert_messages` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            mysql_query($sql);
        case "1.1.5":
        case "1.1.6":
        case "1.1.7":
            break;

    }

    $class = new Sms();
    $class->checkHooks();
}

function smsalert_output($vars)
{
    $modulelink = $vars['modulelink'];
	$version    = $vars['version'];
	$LANG       = $vars['_lang'];
	putenv("TZ=Asia/Dhaka");

    $class = new Sms();
	$tab   = $_GET['tab'];
    echo '<div id="SMSAlert_plugin_container">
    <style>
	#count{margin: 0px 5px;}
	.right{float:right;}
    .contentarea{
        background: #f5f5f5 !important;
    }
    #clienttabs *{
    margin: inherit;
    padding: inherit;
    border: inherit;
    color: inherit;
    background: inherit;
    background-color: inherit;
    }
    #SMSAlert_plugin_container textarea{
        border: 1px solid #cccccc !important;
        padding: 5px !important;
    }
    #SMSAlert_plugin_container .internalDiv {
        text-align: left !important;;
        background:#fff !important;;
        margin: 0px !important;;
        padding: 5px !important;;
        border: 1px solid #ddd !important;
    }
    #SMSAlert_plugin_container .button {
        width: 140px !important;
        height: 43px !important;
        color: #666 !important;
        padding: 10px !important;
        margin-left: 31% !important;
        margin-top: 10px !important;
    }
    #SMSAlert_plugin_container input[type="checkbox"] { border-radius: 0px !important;}
	@media (max-width: 768px) {
    #clienttabs ul li {padding:0 7.5px !important;}
    }
	@media (min-width: 768px) {
    .internalDiv.settings {display:flex;}
	.settings table{width:60%}
    }
    #clienttabs{position: relative; z-index: 99;}
     #clienttabs ul li {
        display: inline-block;
        margin-right: 3px;
        border: 1px solid #ddd;
        border-bottom:0px;
        padding: 12px;
        margin-bottom: -1px;
     }
     #clienttabs ul a {
     border: 0px;;
     }
     #clienttabs ul {
        float:left;
        margin-bottom:0px;
     }
     #clienttabs{
      float:left;
     }
     .tabselected{
        background-color:#fff !important;
     }
     table.form td.fieldarea{
        background-color:white !important;
     }
     table.form td {
        padding: 3px 15px !important;
     }

     table.form {
     padding-top: 20px !important;
     }
     .field-icon {
		margin-left: 400px;
    margin-top: -22px;
    position: absolute;
		}
	.support{
		border-radius: 90px;
		border: 1px solid gray;
		margin-top: 20px;
		text-align: center;
	}
    </style>
	<span id="responsemsg"></span>
    <div id="clienttabs">
        <ul>
            <li class="' . (($tab == "settings" || (@$_GET['type'] == "" && $tab == ""))?"tabselected":"tab") . '"><a href="addonmodules.php?module=smsalert&tab=settings" title="Settings"><span class="hidden-xs">'.$LANG['settings'].'</span><span class="visible-xs"><i class="fa fa-cog"></i></span></a></li>
            <li class="' . ((@$_GET['type'] == "client")?"tabselected":"tab") . '"><a href="addonmodules.php?module=smsalert&tab=templates&type=client" title="Client Templates"><span class="hidden-xs">'.$LANG['clientsmstemplates'].'</span><span class="visible-xs"><i class="fa fa-user"></i></span></a></li>
            <li class="' . ((@$_GET['type'] == "admin")?"tabselected":"tab") . '"><a href="addonmodules.php?module=smsalert&tab=templates&type=admin" title="Admin Templates"><span class="hidden-xs">'.$LANG['adminsmstemplates'].'</span><span class="visible-xs"><i class="fa fa-user"></i></span></a></li>
            <li class="' . (($tab == "sendbulk")?"tabselected":"tab") . '"><a href="addonmodules.php?module=smsalert&tab=sendbulk" title="Send SMS"><span class="hidden-xs">'.$LANG['sendsms'].'</span><span class="visible-xs"><i class="fa fa-envelope"></i></span></a></li>
            <li class="' . (($tab == "messages")?"tabselected":"tab") . '"><a href="addonmodules.php?module=smsalert&amp;tab=messages" title="Sent Messages"><span class="hidden-xs">'.$LANG['messages'].'</span><span class="visible-xs"><i class="fa fa-table"></i></span></a></li>
			<li class="' . (($tab == "advanced_settings")?"tabselected":"tab") . '"><a href="addonmodules.php?module=smsalert&amp;tab=advanced_settings" title="Advanced Settings"><span class="hidden-xs">'.$LANG['advanced'].'</span><span class="visible-xs"><i class="fa fa-cog"></i></span></a></li>
        </ul>
    </div>
    <div style="clear:both;"></div>
    ';
    if (!isset($tab) || $tab == "settings")
    {
        /* UPDATE SETTINGS */
        if ($_POST['params']) {
			$update = array(
                "api"            => $_POST['api'],
                "apiparams"      => htmlspecialchars_decode(json_encode($_POST['params'])),
                'wantsmsfield'   => $_POST['wantsmsfield'],
                'gsmnumberfield' => $_POST['gsmnumberfield'],
                'dateformat'     => $settings['dateformat']
            );
            update_query("mod_SmsAlert_settings", $update, "");
        }
        /* UPDATE SETTINGS */

		if ($_POST['logout']) {
             $query = "TRUNCATE TABLE `mod_SmsAlert_settings`";
	         mysql_query($query);
			 $query = "INSERT INTO `mod_SmsAlert_settings` (`api`, `apiparams`, `wantsmsfield`, `gsmnumberfield`, `resend_time`,`dateformat`, `version`) VALUES ('sms', '{\"senderid\":\"\",\"signature\":\"\",\"country_code\":\"\"}', 0, 0, 15,'%d.%m.%y','1.1.3');";
	         mysql_query($query);
        }
		$getAdminUsername = full_query("SELECT `username`,`firstname`,`lastname`,`email` FROM `tbladmins` WHERE `id`='{$_SESSION['adminid']}'");
        $getAdminUsername = mysql_fetch_assoc($getAdminUsername);

        $apiparams  = $class->getParams();
		$verify_btn = '';
		$disabled   = '';
		$hideclass  = '';
		$option     = '';
		$smsalert 	= new Smsalert();
		$cntry_list = $smsalert->getCountries();
		if($cntry_list['status']=='success')
		{
			foreach($cntry_list['description'] as $country)
			{
				if ($apiparams['country_code'] == $country['Country']['c_code']) {
					$selected_attr = " selected=\"selected\"";
				}
				else if($apiparams['country_code']=='')
				{
					$selected_attr = " selected=\"selected\"";
				}	
				else{
					$selected_attr = "";
				}
				$option.='<option pattern="'.$country['Country']['pattern'].'" value="'.$country['Country']['c_code'].'" '.$selected_attr.'>'.$country['Country']['name'].'</option>';
			}
			$option.='<option pattern="/^(\+)?(country_code)?0?\d+$/" value="" '.$selected_attr.'>Global</option>';
			
		}
		
		if($apiparams['senderid']!='')
		{
			$hideclass      = "hide";
			$disabled       = 'readonly=true';
			$senderid_field = '<select class="sel form-control" name="params[senderid]" id="selectSenderid" disabled><option value="'.$apiparams['senderid'].'">'.$apiparams['senderid'].'</option></select>';
			$country_field='<select class="sel form-control" name="params[country_code]" id="selectCountryCode" disabled>'.$option.'</select>';
		}
		else
		{
			$senderid_field = '<select class="sel form-control" name="params[senderid]" id="selectSenderid" disabled><option value="">--select senderid--</option></select>';
			
			$country_field = '<select class="sel form-control" name="params[country_code]" id="selectCountryCode" disabled>'.$option.'</select>';
			
			$verify_btn    = '<tr class="verify_btn">
						<td class="fieldlabel" width="30%"></td>
						<td class="fieldarea">
						<input type="button" value="verify and continue" class="btn btn-sm btn-info" onclick="verifyLoginForm()">
				Don\'t have an account on smsAlert? <a href="//www.smsalert.co.in/?name='.$getAdminUsername['firstname'].' '.$getAdminUsername['lastname'].'&email='.$getAdminUsername['email'].'&username='.$getAdminUsername['username'].'#registerbox" target="blank" style="    color: #47a6ec;">Signup Here For Free</a>
                            </td>
                        </tr>';
		}
	
        echo '
        <script type="text/javascript">
            $(document).ready(function(){
                $("#api").change(function(){
                    $("#form").submit();
                });
            });
        </script>
        <form action="" method="post" id="smsalertform">
        <input type="hidden" name="action" value="save" />
            <div class="internalDiv settings">
                <table class="form" border="0" cellspacing="2" cellpadding="3" style="margin:0px;border: 0px;">
                    <tbody>
					<tr>
                                <input type="hidden"  value="sms" name="api" id="api"/>
								<input type="hidden"  value="'.$apiparams['pattern'].'" name="params[pattern]" id="pattern"/>
                        </tr>
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['username'].'</a></td>
                            <td class="fieldarea"><input type="text" name="params[username]" size="40" value="' . $apiparams['username'] . '" id="inputUsername" class="form-control" '.$disabled.'></td>
                        </tr>
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['password'].'</a></td>
                            <td class="fieldarea"><input type="password" name="params[password]" size="40" value="' . $apiparams['password'] . '" id="inputPassword" class="form-control" '.$disabled.'> <span toggle="#inputPassword" class="fa fa-fw fa-eye field-icon toggle-password"></span></td>
                        </tr>
						'.$verify_btn.'
						
						<tr>
                            <td class="fieldlabel" width="30%">'.$LANG['senderid'].'</td>
                            <td class="fieldarea">'.$senderid_field.'</td>
                        </tr>
						<tr>
                            <td class="fieldlabel" width="30%">'.$LANG['country_code'].'</td>
                            <td class="fieldarea">'.$country_field.'</td>
                        </tr>
                   <tr><td class="fieldlabel" width="30%">
              <button type="button" id="save_details_smsalert" class="'.$hideclass.' save_btn btn btn-sm btn-primary" onclick="saveLoginForm()" disabled/>'.$LANG['save'].'</button></td>
				
        </form><td class="fieldarea">
        ';
		if($apiparams['senderid']!='')
		{
			echo '<form action="" method="post" id="form">
			<input type="hidden" name="logout" value="save" /><button class="btn btn-sm btn-danger" type="submit" name="Logout" >Logout</button></form>';
		}
		echo ' </td></tr></tbody></table><div class="clientssummarybox"><img src="https://www.smsalert.co.in/logo/www.smsalert.co.in.png" style="border-bottom:1px solid gray">
		<div class="support">
		<p><b>Need Support</b></p>
        <p>'.$LANG['supportc2'].'</p>
        <p>'.$LANG['supportc3'].'</p>
		</div>
		</div>
		</div>';
		
    }
    elseif ($tab == "templates")
    {
        if ($_POST['submit']) {
            $where = array("type" => array("sqltype" => "LIKE", "value" => $_GET['type']));
            $result = select_query("mod_SmsAlert_templates", "*", $where);
            while ($data = mysql_fetch_array($result)) {
                if ($_POST[$data['id'] . '_active'] == "on") {
                    $tmp_active = 1;
                } else {
                    $tmp_active = 0;
                }
                $update = array(
                    "template" => $_POST[$data['id'] . '_template'],
                    "active"   => $tmp_active
                );
                if(isset($_POST[$data['id'] . '_extra'])){
                    $update['extra']= trim($_POST[$data['id'] . '_extra']);
                }
                if(isset($_POST[$data['id'] . '_admingsm'])){
                    $update['admingsm'] = $_POST[$data['id'] . '_admingsm'];
                    $update['admingsm'] = str_replace(" ","",$update['admingsm']);
                }
                update_query("mod_SmsAlert_templates", $update, "id = " . $data['id']);
            }
        }

        echo '<form action="" method="post">
        <input type="hidden" name="action" value="save" />
            <div class="internalDiv">
                <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3" style="margin:0px;border: 0px;">
                    <tbody>';
        $where = array("type" => array("sqltype" => "LIKE", "value" => $_GET['type']));
        $result = select_query("mod_SmsAlert_templates", "*", $where);

        while ($data = mysql_fetch_array($result)) {
            if ($data['active'] == 1) {
                $active   = 'checked = "checked"';
				$disabled = '';
            } else {
                $active   = '';
				$disabled = 'readonly=true';
            }
            $desc       = json_decode($data['description']);
            if(isset($desc->$LANG['lang'])){
                $name   = $desc->$LANG['lang'];
            }else{
                $name   = $data['name'];
            }
			
			$tokenarray = explode(",",$data['variables']);
			    ?>
            <tr>
                <td class="fieldlabel"  style="float:right;"><?php echo $LANG['parameter']; ?></td>
                <td class="<?php echo ''.$data['id'].'_link' ?>">
				<?php
				foreach($tokenarray as $token)
				{
					echo '<a href="javascript:void(0)" id="'.$token.'" class="font12 left setalink" data-token="'.$token.'" onclick="insertToken(this)">'.ucfirst(preg_replace("/[{}]/", "", $token)).'</a> | ';
				}
				
				?></td>
            </tr>
           <?php
            echo '
                <tr>
                    <td class="fieldlabel" width="30%"><input type="checkbox" value="on" id="' . $data['id'] . '_checkbox" name="' . $data['id'] . '_active" ' . $active . ' onchange="togglecheckbox(this)"><label for="' . $data['id'] . '_checkbox" >' . $name . '</label></td>
                    <td class="fieldarea">
                        <textarea name="' . $data['id'] . '_template" class="form-control" '.$disabled.' id="'.$data['id'].'_check">' . $data['template'] . '</textarea>
                    </td>
                </tr>';
        
            if(!empty($data['extra'])){
                echo '
                <tr>
                    <td class="fieldlabel" width="30%">'.$LANG['ekstra'].'</td>
                    <td class="fieldarea">
                        <input type="text" name="'.$data['id'].'_extra" value="'.$data['extra'].'">
                    </td>
                </tr>
                ';
            }
            if($_GET['type'] == "admin"){
                echo '
                <tr>
                    <td class="fieldlabel" width="30%">'.$LANG['admingsm'].'</td>
                    <td class="fieldarea">
                        <input type="text" class="extraField form-control" name="'.$data['id'].'_admingsm" placeholder="e.g. 91810XXX,9191XXXXX" value="'.$data['admingsm'].'">
                    </td>
                </tr>
                ';
            }
            echo '<tr>
                <td colspan="2"><hr></td>
            </tr>';
        }
        echo '
        </tbody>
                </table>
            <p style="text-align:center"><input type="submit" name="submit" class="btn btn-primary save_btn" value="Save Changes"></p>
            </div>
        </form>';

    }
    elseif ($tab == "messages")
    {
        if(!empty($_GET['deletesms'])){
            $smsid = (int) $_GET['deletesms'];
            $sql   = "DELETE FROM mod_SmsAlert_messages WHERE id = '$smsid'";
            mysql_query($sql);
        }
        echo  '<div class="internalDiv" style="padding:20px !important;"><table width="100%" class="datatable" border="0" cellspacing="1" cellpadding="3" style="margin: 0px; border: 0px;">
        <thead>
            <tr>
                <th>#</th>
                <th>'.$LANG['client'].'</th>
                <th>'.$LANG['gsmnumber'].'</th>
                <th width="50%" >'.$LANG['message'].'</th>
                <th>'.$LANG['datetime'].'</th>
                <th>'.$LANG['status'].'</th>
                <th width="20"></th>
            </tr>
        </thead>
        <tbody>
        ';

        // Getting pagination values.
        $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit  = (isset($_GET['limit']) && $_GET['limit']<=50) ? (int)$_GET['limit'] : 10;
        $start  = ($page > 1) ? ($page*$limit)-$limit : 0;
        $order  = isset($_GET['order']) ? $_GET['order'] : 'DESC';
		
        /* Getting messages order by date desc */
        $sql    = "SELECT `m`.*,`user`.`firstname`,`user`.`lastname`
        FROM `mod_SmsAlert_messages` as `m`
        JOIN `tblclients` as `user` ON `m`.`user` = `user`.`id`
        ORDER BY `m`.`datetime` {$order} limit {$start},{$limit}";
        $result = mysql_query($sql);
        $i = 0;

        //Getting total records
        $total  = "SELECT count(id) as toplam FROM `mod_SmsAlert_messages`";
        $sonuc  = mysql_query($total);
        $sonuc  = mysql_fetch_array($sonuc);
        $toplam = $sonuc['toplam'];

        //Page calculation
        $sayfa  = ceil($toplam/$limit);
        while ($data = mysql_fetch_array($result)) {
            if($data['phid']){
                $status = $class->getReport($data['phid']);
                mysql_query("UPDATE mod_SmsAlert_messages SET status = '$status' WHERE id = ".$data['id']);
            }else{
                $status = $data['status'];
            }

            $i++;
            echo  '<tr>
            <td>'.$data['id'].'</td>
            <td><a href="clientssummary.php?userid='.$data['user'].'">'.$data['firstname'].' '.$data['lastname'].'</a></td>
            <td>'.$data['to'].'</td>
            <td>'.$data['text'].'</td>
            <td>'.$data['datetime'].'</td>
            <td>'.$status.'</td>
            <td><a href="addonmodules.php?module=smsalert&tab=messages&deletesms='.$data['id'].'" title="'.$LANG['delete'].'"><img src="images/delete.gif" width="16" height="16" border="0" alt="Delete"></a></td></tr>';
        }
        echo '</tbody></table>';  
        $list="";
        for($a=1;$a<=$sayfa;$a++)
        {
            $selected = ($page==$a) ? 'selected="selected"' : '';
            $list.="<option value='addonmodules.php?module=smsalert&tab=messages&page={$a}&limit={$limit}&order={$order}' {$selected}>{$a}</option>";
        }
        echo "<select  onchange=\"this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);\">{$list}</select></div>";

    }
    elseif($tab=="sendbulk")
    {
        $apiparams = $class->getParams();
        if(!empty($_POST['client'])){
            $userinf     = explode("_",$_POST['client']);
            $userid      = $userinf[0];
            $gsmnumber   = $userinf[1];
            $replacefrom = array("{firstname}","{lastname}");
            $replaceto   = array($userinf[2],$userinf[3]);
            $message     = str_replace($replacefrom,$replaceto,$_POST['message']);
            $class->setGsmnumber($gsmnumber);
            $class->setMessage($message);
            $class->setUserid($userid);
            $result      = $class->send();
            if($result == false){
                $responseToShow =  $class->getErrors();
            }else{
                $responseToShow =  $LANG['smssent'].' '.$gsmnumber;
            }

            if($_POST["debug"] == "ON"){
                $debug = 1;
            }
        }

        $userSql = "SELECT `a`.`id`,`a`.`firstname`, `a`.`lastname`, `a`.`country`, `a`.`phonenumber` as `gsmnumber`
        FROM `tblclients` as `a` order by `a`.`firstname`";
        $clients = '';
        $result = mysql_query($userSql);
        while ($data = mysql_fetch_array($result)) {
            $clients .= '<option value="'.$data['id'].'_'.$data['gsmnumber'].'_'.$data['firstname'].'_'.$data['lastname'].'_'.$data['country'].'">'.$data['firstname'].' '.$data['lastname'].' (#'.$data['id'].')</option>';
        }
        echo  '<script src="https://www.smsalert.co.in/js/sms-char-length.js"></script>';
        echo '
        <script>
        jQuery.fn.filterByText = function(textbox, selectSingleMatch) {
          return this.each(function() {
            var select = this;
            var options = [];
            $(select).find("option").each(function() {
              options.push({value: $(this).val(), text: $(this).text()});
            });
            $(select).data("options", options);
            $(textbox).bind("change keyup", function() {
              var options = $(select).empty().scrollTop(0).data("options");
              var search = $.trim($(this).val());
              var regex = new RegExp(search,"gi");

              $.each(options, function(i) {
                var option = options[i];
                if(option.text.match(regex) !== null) {
                  $(select).append(
                     $("<option>").text(option.text).val(option.value)
                  );
                }
              });
              if (selectSingleMatch === true && 
                  $(select).children().length === 1) {
                $(select).children().get(0).selected = true;
              }
            });
          });
        };
        $(function() {
          $("#clientdrop").filterByText($("#textbox"), true);
        });  
        </script>';
        echo '<form action="" method="post">
        <input type="hidden" name="action" value="save" />
            <div class="internalDiv" >'.$responseToShow.'
                <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3" style="margin:0px;border: 0px;">
                    <tbody>
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['client'].'</td>
                            <td class="fieldarea">
                                <input id="textbox" type="text" placeholder="Type client name" style="width:100% !important;padding:5px" class="form-control"><br>
                                <select name="client" class="form-control" multiple id="clientdrop" style="padding:5px">
                                    <option value="">'.$LANG['selectclient'].'</option>
                                    ' . $clients . '
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="fieldlabel" width="30%">'.$LANG['message'].'</td>
                            <td class="fieldarea">
                               <textarea rows="5" name="message" style="padding:5px" class="form-control textarea_message" id="SmsSmscontent"></textarea><div class="smscounter right"><span id="smscount">1</span> SMS </div><div class="right" id="character">Character(s),</div>
                               <div id="counttxt" class="right">
                               <div id="count">0</div></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="fieldlabel" width="30%">Parameters :</td>
                            <td class="fieldarea">
                                {firstname},{lastname}<br>
								
                            </td>
                        </tr>
						<tr>
						<td class="fieldlabel" width="30%"></td>
						<td class="fieldarea"><button type="submit" class="save_btn btn btn-sm btn-primary">'.$LANG['send'].'</button></td>
						</tr>
                    </tbody>
                </table>
            
            </div>
        </form>';


    }
	elseif ($tab == "advanced_settings")
    {
        if ($_POST['submit']) {
            $update = array(
                'resend_time' => $_POST['resend_timer']
            );
            update_query("mod_SmsAlert_settings", $update, "");
        }
		$settings   = $class->getSettings();
		$resend_time = !empty($settings['resend_time'])?$settings['resend_time']:15;
        echo '<form action="" method="post">
        <input type="hidden" name="action" value="save" />
            <div class="internalDiv">
                <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3" style="margin:0px;border: 0px;">
                    <tbody><tr>
                    <td class="fieldlabel" width="30%">OTP Re-send Timer (in seconds)</td>
                    <td class="fieldarea">
                        <input type="number" name="resend_timer" class="form-control input-250" id="resend_timer" value="'.$resend_time.'">
                    </td>
                </tr>';
            echo '<tr>
                <td colspan="2"><hr></td>
            </tr>';
        echo '
        </tbody>
                </table>
            <p style="text-align:center"><input type="submit" name="submit" class="btn btn-primary save_btn" value="Save Changes"></p>
            </div>
        </form>';

    }
    $credit =  $class->getBalance();
    if($credit){
        echo '
            <div style="text-align: left;background-color: whiteSmoke;margin: 0px;padding: 5px; border: 1px solid #ddd;">
            <b>'.$LANG['credit'].':</b> '.$credit.'
            </div>';
    }
	echo $LANG['lisans'];
    echo '</div>';
?>
<script>
function saveLoginForm()
{ 
    var pattern = $('#selectCountryCode').find('option:selected').attr('pattern');
    $('#pattern').val(pattern);
	$('#smsalertform').submit();
	return true;
}
//function verify login form
	function verifyLoginForm()
	{
		   inputUsername = $('#inputUsername').val();
		   inputPassword = encodeURIComponent($('#inputPassword').val());
		   $('#username_status, #password_status').hide();
		   if(inputUsername===''){$('#username_status').show();}
		   if(inputPassword===''){$('#password_status').show();}
		  //senderid listing
		   if(inputUsername!='' && inputPassword!='')
		   {		
				url = "//www.smsalert.co.in/api/senderlist.json?user="+inputUsername+"&pwd="+inputPassword;
				senderopt = '';
				$.ajax({
                    url: url,
                    dataType: 'jsonp',
                    success: function(response){		   
						 if(response.status==='success')
						 {
							
							$(response.description).each(function( index, item) {
								  if(item.Senderid.approved ===true)
								  { 
										senderopt += '<option value="'+item.Senderid.sender+'">'+item.Senderid.sender+'</option>';
								  }
							 });
							
							 $('#responsemsg').html('<div class="alert alert-success"><i class="fa fa-check-circle">Verified Successfully. <button type="button" class="close" data-dismiss="alert">×</button></div>');
						 }						 
						 else
						 {
							 senderopt += '<option value="CVDEMO">CVDEMO</option>';
						 }
						  $('#selectSenderid').prop("disabled", false);
						  $('#selectCountryCode').prop("disabled", false);
						  $('#save_details_smsalert').prop("disabled", false);
						  $('#selectSenderid').html(senderopt);
						  $('.verify_btn').addClass('hide');
                    },
					error: function(xhr, ajaxOptions, thrownError) {
					   $('#responsemsg').html('<div class="alert alert-danger smsalert_alert"><i class="fa fa-check-circle"></i> Invalid Username/Password.<button type="button" class="close" data-dismiss="alert">×</button></div>');
		          }
                });
		   }
	}
	function togglecheckbox(obj)
	{
		var id = parseInt($(obj).attr('name').split('_')[0], 10);
	   	
		if($(obj).is(':checked'))
		{
			 $("#"+id+"_check").prop("readonly", false);
		}
		else
		{
			 $("#"+id+"_check").prop("readonly", true);
		}
	}
	
	
function insertToken(obj){
		var dataToken= $(obj).attr('data-token');
		var id = parseInt($(obj).parent('td').attr('class').split('_')[0], 10);
			insertAtCaret(dataToken,id);
		
}

function insertAtCaret(textFeildValue,id) 
   {
		var textObj = document.getElementById(""+id+"_check");
		if (document.all) {
			if (textObj.createTextRange && textObj.caretPos) {
				var caretPos  = textObj.caretPos;
				caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? textFeildValue + ' ' : textFeildValue;
			}
			else {
				textObj.value = textObj.value + textFeildValue;
			}
		}
		else {
			if (textObj.setSelectionRange) {
				var rangeStart = textObj.selectionStart;
				var rangeEnd   = textObj.selectionEnd;
				var tempStr1   = textObj.value.substring(0, rangeStart);
				var tempStr2   = textObj.value.substring(rangeEnd);
	
				textObj.value  = tempStr1 + textFeildValue + tempStr2;
			}
			else {
				alert("This version of Mozilla based browser does not support setSelectionRange");
			}
		}
	}
	$(".toggle-password").click(function() {

  $(this).toggleClass("fa-eye fa-eye-slash");
  var input = $($(this).attr("toggle"));
  if (input.attr("type") == "password") {
    input.attr("type", "text");
  } else {
    input.attr("type", "password");
  }
});
</script>
<?php
}
?>
