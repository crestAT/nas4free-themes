<?php
/* 
    themes-config.php

    Copyright (c) 2017 - 2019 Andreas Schmidhuber
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:

    1. Redistributions of source code must retain the above copyright notice, this
       list of conditions and the following disclaimer.
    2. Redistributions in binary form must reproduce the above copyright notice,
       this list of conditions and the following disclaimer in the documentation
       and/or other materials provided with the distribution.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
    ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
    WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
    ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
    (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
    LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
    ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
    (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
    SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
require("auth.inc");
require("guiconfig.inc");

$appName = "Themes";
$configName = strtolower($appName);
$configFile = "ext/{$configName}/{$configName}.conf";
require_once("ext/{$configName}/extension-lib.inc");

// localization
$domain = strtolower(get_product_name());
$localeOSDirectory = "/usr/local/share/locale";
$localeExtDirectory = "/usr/local/share/locale-{$configName}";
bindtextdomain($domain, $localeExtDirectory);

// Dummy standard message gettext calls for xgettext retrieval!!!
$dummy = gettext("The changes have been applied successfully.");
$dummy = gettext("The configuration has been changed.<br />You must apply the changes in order for them to take effect.");
$dummy = gettext("The following input errors were detected");
$dummy = gettext("The attribute '%s' contains invalid characters.");
$dummy = gettext("The attribute '%s' is required.");

if (($configuration = ext_load_config($configFile)) === false) $input_errors[] = sprintf(gettext("Configuration file %s not found!"), "{$configName}.conf");
if (!isset($configuration['rootfolder']) && !is_dir($configuration['rootfolder'] )) $input_errors[] = gettext("Extension installed with fault");

$pgtitle = array(gettext("Extensions"), $configuration['appname']." ".$configuration['version'], gettext("Configuration"));

if (!isset($configuration) || !is_array($configuration)) $configuration = array();

// initialize variables --------------------------------------------------
$setThemeScript = "{$configuration['rootfolder']}/base/setTheme.sh";
// -----------------------------------------------------------------------

// -----------------------------------------------------------------------
class HTMLColorChooser extends HTMLEditBox2 {
	var $_color = '';
	function __construct($ctrlname,$title,$value,$description,$size = 40) {
		parent::__construct($ctrlname,$title,$value,$description,$size);
	}
	function SetColor($color) {
		$this->_color = $color;
	}
	function GetColor() {
		return $this->_color;
	}
	function ComposeInner(&$anchor) {
		//	helper variables
		$ctrlname = $this->GetCtrlName();
		$size = $this->GetSize();
		//	input element
		$attributes = [
			'type' => 'text',
			'id' => $ctrlname,
			'name' => $ctrlname,
			'class' => 'formfld inputColor',
			'value' => $this->GetValue(),
			'size' => $size
		];
		$this->GetAttributes($attributes);
		$anchor->insINPUT($attributes);
		//	color element
		$attributes = [
			'type' => 'color',
			'id' => $ctrlname . 'colorbtn',
			'name' => $ctrlname . 'colorbtn',
			'class' => 'formbtn',
			'value' => $this->GetValue(),
			'size' => $size
		];
		$this->GetAttributes($attributes);
		$anchor->insINPUT($attributes);
	}
}

function html_colorchooser($ctrlname,$title,$value,$desc,$required = false,$size = 30,$readonly = false) {
	$ctrl = new HTMLColorChooser($ctrlname,$title,$value,$desc,$size);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetColor($color);
	$ctrl->Compose()->render();
}
// -----------------------------------------------------------------------

if ($_POST) {
	if (isset($_POST['saveTheme']) && $_POST['saveTheme']) {
		$reqdfields = ['themeName','tbMAINCOLOR','tbNAVTEXT','tbNAVSELECT','tbPAGETITLE','tbBACKGROUND','tbTEXTCOLOR','tbFRAME','tbBUTTONFACE'];
		$reqdfieldsn = [gettext("Theme Name"),gettext("Navigation Main Color"),gettext("Navigation Text"),gettext("Navigation Selected Text"), gettext("Page Titel"),gettext("Page Background"),gettext("Page Text"),gettext("Frames"),gettext("Buttons")];
		do_input_validation($_POST, $reqdfields, $reqdfieldsn, $input_errors);

		if (empty($input_errors)) {
			if (empty($configuration['themes'])) $configuration['themes'] = [];
			$themeName = trim($_POST['themeName']);
			$configuration['themes'][$themeName]['tbMAINCOLOR'] = trim($_POST['tbMAINCOLOR']);
			$configuration['themes'][$themeName]['tbNAVTEXT'] = trim($_POST['tbNAVTEXT']);
			$configuration['themes'][$themeName]['tbNAVSELECT'] = trim($_POST['tbNAVSELECT']);
			$configuration['themes'][$themeName]['tbPAGETITLE'] = trim($_POST['tbPAGETITLE']);
			$configuration['themes'][$themeName]['tbBACKGROUND'] = trim($_POST['tbBACKGROUND']);
			$configuration['themes'][$themeName]['tbTEXTCOLOR'] = trim($_POST['tbTEXTCOLOR']);
			$configuration['themes'][$themeName]['tbFRAME'] = trim($_POST['tbFRAME']);
			$configuration['themes'][$themeName]['tbBUTTONFACE'] = trim($_POST['tbBUTTONFACE']);
			$configuration['themes'][$themeName]['themeImages'] = $_POST['themeImages'];
			ext_save_config($configFile, $configuration);
			$savemsg .= sprintf(gettext("The theme '%s' has been saved"), $_POST['themeName'])."<br />";
		}
	}

	if (isset($_POST['removeTheme']) && $_POST['removeTheme']) {
		if ($_POST['selectedTheme'] != "") {
			if ($_POST['selectedTheme'] == $configuration['currentTheme']) {
				$input_errors[] = sprintf(gettext("The theme '%s' is currently in use and cannot be removed!"), $_POST['selectedTheme'])."<br />";								
			} else {
				unset($configuration['themes'][$_POST['selectedTheme']]);
				ext_save_config($configFile, $configuration);
				$savemsg .= sprintf(gettext("The theme '%s' has been removed"), $_POST['selectedTheme'])."<br />";
			}
		}
	}

	if (isset($_POST['save']) && $_POST['save']) {
	    unset($input_errors);
        $configuration['enable'] = isset($_POST['enable']);
        $configuration['currentTheme'] = $_POST['currentTheme'];
		$savemsg .= get_std_save_message(ext_save_config($configFile, $configuration))."<br />";
        if (isset($_POST['enable'])) {
# create convert script
			$script = fopen($setThemeScript, "w");
			fwrite($script,
"#!/bin/sh
# WARNING: THIS IS AN AUTOMATICALLY CREATED SCRIPT, DO NOT CHANGE THE CONTENT!
cp {$configuration['rootfolder']}/base/css/* {$configuration['rootfolder']}/live/css/
sed -i '' 's/#tbMAINCOLOR/{$configuration['themes'][$configuration['currentTheme']]['tbMAINCOLOR']}/g' {$configuration['rootfolder']}/live/css/*.css
sed -i '' 's/#tbNAVTEXT/{$configuration['themes'][$configuration['currentTheme']]['tbNAVTEXT']}/g' {$configuration['rootfolder']}/live/css/*.css
sed -i '' 's/#tbNAVSELECT/{$configuration['themes'][$configuration['currentTheme']]['tbNAVSELECT']}/g' {$configuration['rootfolder']}/live/css/*.css
sed -i '' 's/#tbPAGETITLE/{$configuration['themes'][$configuration['currentTheme']]['tbPAGETITLE']}/g' {$configuration['rootfolder']}/live/css/*.css
sed -i '' 's/#tbTEXTCOLOR/{$configuration['themes'][$configuration['currentTheme']]['tbTEXTCOLOR']}/g' {$configuration['rootfolder']}/live/css/*.css
sed -i '' 's/#tbBUTTONFACE/{$configuration['themes'][$configuration['currentTheme']]['tbBUTTONFACE']}/g' {$configuration['rootfolder']}/live/css/*.css
sed -i '' 's/#tbBACKGROUND/{$configuration['themes'][$configuration['currentTheme']]['tbBACKGROUND']}/g' {$configuration['rootfolder']}/live/css/*.css
sed -i '' 's/#tbFRAME/{$configuration['themes'][$configuration['currentTheme']]['tbFRAME']}/g' {$configuration['rootfolder']}/live/css/*.css
sync
");
			fclose($script);
			chmod($setThemeScript, 0755);
			mwexec($setThemeScript, true);
			require_once("{$configuration['rootfolder']}/{$configName}-start.php");
			flush();
		} else {
			mwexec("cp /usr/local/www/css-ORIGINAL/* /usr/local/www/css/", true);
			mwexec("cp /usr/local/www/images-ORIGINAL/* /usr/local/www/images/", true);
			mwexec("cp /usr/local/www/css.php-ORIGINAL/fbegin.inc /usr/local/www/fbegin.inc", true);
			mwexec("cp /usr/local/www/css.php-ORIGINAL/filechooser.php /usr/local/www/filechooser.php", true);
		} 
	}
}

unset($themesArray);
$arrayKeys = array_keys($configuration['themes']);						// get saved themes (keys)
foreach($arrayKeys as $key) $themesArray[$key] = $key;					// create associative array from keys
asort($themesArray);
$themesEditArray = array_merge(array('' => ''), $themesArray);			// create array with blank option 0 for designer

foreach (glob("{$configuration['rootfolder']}/base/images/*") as $dirName) {
    $themesImagesArray[basename($dirName)] = basename($dirName);
}

// version checks for extension - just once per day
if (($message = ext_check_version("{$configuration['rootfolder']}/version_server.txt", "{$configName}", $configuration['version'], gettext("Maintenance"))) !== false) $savemsg .= $message;

bindtextdomain($domain, $localeOSDirectory);
include("fbegin.inc");
bindtextdomain($domain, $localeExtDirectory);
?>
<style>
input[type="color"] {
    height: 15px;
    margin-left: 3px;
    padding: 1px 2px;
    vertical-align: top;		
}
@-moz-document url-prefix() {
	input[type="color"] {
	    height: 20px;
	}
}
</style>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
    var endis = !(document.iform.enable.checked || enable_change);
	document.iform.currentTheme.disabled = endis;
}

$(document).ready(function(){
	$('input[type=color]').on('change', function() {					// set input with selected color
		var color = $(this).val();
		$(this).prev().val(color);
	});

	$('.inputColor').on('change', function() {							// set color button from edited color input
		var color = $(this).val();
		$(this).next().val(color);
	});

	$('#selectedTheme').on('change', function() {
		var selectedText = $("#selectedTheme option:selected").text();
		var configuration = <?php echo json_encode($configuration); ?>;
		var theme = configuration['themes'][selectedText]; 

		$('#themeName').val(selectedText);								// set theme name
		let key;
		for (key in theme) {											// traverse object and set ... 
		   $('#' + key).val(theme[key]);								// input and ...
		   $('#' + key + 'colorbtn').val(theme[key]);					// color button values
		}
	});
	
});
//-->
</script>
<form action="<?php echo $configName; ?>-config.php" method="post" name="iform" id="iform" onsubmit="spinner()">
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr><td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="<?php echo $configName; ?>-config.php"><span><?=gettext("Configuration");?></span></a></li>
				<li class="tabinact"><a href="<?php echo $configName; ?>-update_extension.php"><span><?=gettext("Maintenance");?></span></a></li>
			</ul>
		</td></tr>
	    <tr><td class="tabcont">
	        <?php if (!empty($input_errors)) print_input_errors($input_errors);?>
	        <?php if (!empty($savemsg)) print_info_box($savemsg);?>
	
			<!-- Status -->
	        <table width="100%" border="0" cellpadding="6" cellspacing="0">
	            <?php 
	            	html_titleline_checkbox("enable", gettext("Themes"), $configuration['enable'], gettext("Enable"), "enable_change(false)");
	            	html_text("installation_directory", gettext("Installation Directory"), sprintf(gettext("The extension is installed in %s"), $configuration['rootfolder']));
					html_combobox("currentTheme", gettext("Theme"), $configuration['currentTheme'], $themesArray, sprintf(gettext("Choose a theme and press %s to activate"), gettext("Save")), true, false);
				?>
			</table>
	        <div id="submit">
				<input id="save" name="save" type="submit" class="formbtn" value="<?=gettext("Save");?>"/>
	        </div>
		</td></tr>
	</table>
	<br />
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
	    <tr><td class="tabcont">
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
	            <?php
					html_titleline(gettext("Theme Designer"));
					html_combobox("selectedTheme", gettext("Theme"), "", $themesEditArray, gettext("Choose a theme to modify or remove"), true, false);
					html_inputbox("themeName",gettext("Theme Name"),"",gettext("Enter new or modify theme name"),true);
					html_colorchooser("tbMAINCOLOR",gettext("Navigation Main Color"),"",gettext("Menu/Footer/Tabs/Bars background color"),true);
					html_colorchooser("tbNAVTEXT",gettext("Navigation Text"),"",gettext("Menu/Footer/Tabs/Bars text color"),true);
					html_colorchooser("tbNAVSELECT",gettext("Navigation Selected Text"),"",gettext("Menu/Tabs/Bars selected text color"),true);
					html_colorchooser("tbPAGETITLE",gettext("Page Titel"),"",gettext("Page Titel text color"),true);
					html_colorchooser("tbBACKGROUND",gettext("Page Background"),"",gettext("Standard background color"),true);
					html_colorchooser("tbTEXTCOLOR",gettext("Page Text"),"",gettext("Standard text color"),true);
					html_colorchooser("tbFRAME",gettext("Frames"),"",gettext("Page frame color"),true);
					html_colorchooser("tbBUTTONFACE",gettext("Buttons"),"",gettext("Button color"),true);
					html_combobox("themeImages", gettext("Device Size Bars"), "", $themesImagesArray, gettext("Choose a bar type to use for Status > System and Swap Devices"), true, false);
				?>
			</table>
	        <div id="remarks">
				<?php html_remark("note", gettext("Note"),
					sprintf(gettext("For color definitions one can use hexadecimal color values (format: #rrggbb, eg #FFFFFF) or color names (eg white or lightgray) 
					in the input fields or the color pickers.<br />Please be aware that the color pickers can reflect only hexadecimal 
					colors but not color names!<br />For color names and further information please check the documentation at %s.")."</a>.", 
					"<a href='https://www.w3schools.com/colors/colors_names.asp' target='_blank'>w3schools.com")); ?>
	        </div>
	        <div id="submit">
				<input id="saveTheme" name="saveTheme" type="submit" class="formbtn" value="<?=gettext("Save Theme");?>"/>
				<input id="removeTheme" name="removeTheme" type="submit" class="formbtn" value="<?=gettext("Remove Theme");?>"
					onclick="return confirm('<?=gettext("Do you really want to remove the theme?");?>')" />
				<input id="reset" name="reset" type="reset" class="formbtn" value="<?=gettext("Reset Input Fields");?>"/>
	        </div>
		</td></tr>
	</table>
	<?php include("formend.inc");?>
</form>
<script type="text/javascript">
<!--
enable_change(false);
//-->
</script>
<?php include("fend.inc");?>
