<?php
/*
    themes-update_extension.php
    
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

$configName = "themes";
$configFile = "ext/{$configName}/{$configName}.conf";
require_once("ext/{$configName}/extension-lib.inc");

$domain = strtolower(get_product_name());
$localeOSDirectory = "/usr/local/share/locale";
$localeExtDirectory = "/usr/local/share/locale-{$configName}";
bindtextdomain($domain, $localeExtDirectory);

if (($configuration = ext_load_config($configFile)) === false) $input_errors[] = sprintf(gettext("Configuration file %s not found!"), "{$configName}.conf");
if (!isset($configuration['rootfolder']) && !is_dir($configuration['rootfolder'] )) $input_errors[] = gettext("Extension installed with fault");

$pgtitle = array(gettext("Extensions"), gettext($configuration['appname'])." ".$configuration['version'], gettext("Maintenance"));

if (is_file("{$configuration['rootfolder']}/oneload")) { require_once("{$configuration['rootfolder']}/oneload"); }

$return_val = mwexec("fetch -o {$configuration['rootfolder']}/version_server.txt https://raw.github.com/crestAT/nas4free-{$configName}/master/{$configName}/version.txt", false);
if ($return_val == 0) { 
    $server_version = exec("cat {$configuration['rootfolder']}/version_server.txt"); 
    if ($server_version != $configuration['version']) { $savemsg .= sprintf(gettext("New extension version %s available, push '%s' button to install the new version!"), $server_version, gettext("Update Extension")); }
    mwexec("fetch -o {$configuration['rootfolder']}/release_notes.txt https://raw.github.com/crestAT/nas4free-{$configName}/master/{$configName}/release_notes.txt", false);
}
else { $server_version = gettext("Unable to retrieve version from server!"); }

if (isset($_POST['ext_remove']) && $_POST['ext_remove']) {
// remove start/stop commands
	ext_remove_rc_commands($configName);
// remove extension pages/links
    require_once("{$configuration['rootfolder']}/{$configName}-stop.php");
	header("Location:index.php");
}

if (isset($_POST['ext_cleanup']) && $_POST['ext_cleanup']) {
// remove start/stop commands
	ext_remove_rc_commands($configName);
// remove extension pages/links
    require_once("{$configuration['rootfolder']}/{$configName}-stop.php");
    mwexec("rm -R {$configuration['rootfolder']}");
	header("Location:index.php");
}

if (isset($_POST['ext_update']) && $_POST['ext_update']) {
// download installer & install
    $return_val = mwexec("fetch -vo {$configuration['rootfolder']}/{$configName}-install.php 'https://raw.github.com/crestAT/nas4free-{$configName}/master/{$configName}/{$configName}-install.php'", false);
    if ($return_val == 0) {
    	/* special treatment to restore modifications and user defined themes */
    	$replacement = $configuration;									// save config for later use
        
		require_once("{$configuration['rootfolder']}/{$configName}-install.php");
		
    	/* special treatment to restore modifications and user defined themes */
    	$configuration['enable'] = $replacement['enable'];
    	$configuration['currentTheme'] = $replacement['currentTheme'];
		$configuration['themes'] = array_replace_recursive($configuration['themes'], $replacement['themes']);
		ext_save_config($configFile, $configuration);

        $version = exec("cat {$configuration['rootfolder']}/version.txt");
        $savemsg = sprintf(gettext("Update to version %s completed!"), $version);
        header("Refresh:8");;
    }
    else { $input_errors[] = sprintf(gettext("Download of installation file %s failed, installation aborted!"), "{$configName}-install.php"); }
}

bindtextdomain($domain, $localeOSDirectory);
include("fbegin.inc");
bindtextdomain($domain, $localeExtDirectory);
?>
<form action="<?php echo $configName; ?>-update_extension.php" method="post" name="iform" id="iform" onsubmit="spinner()">
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="<?php echo $configName; ?>-config.php"><span><?=gettext("Configuration");?></span></a></li>
			<li class="tabact"><a href="<?php echo $configName; ?>-update_extension.php"><span><?=gettext("Maintenance");?></span></a></li>
		</ul>
	</td></tr>
	<tr><td class="tabcont">
        <?php if (!empty($input_errors)) print_input_errors($input_errors);?>
        <?php if (!empty($savemsg)) print_info_box($savemsg);?>
        <table width="100%" border="0" cellpadding="6" cellspacing="0">
            <?php 
				html_titleline(gettext("Extension Update"));
				html_text("ext_version_current", gettext("Installed version"), $configuration['version']);
				html_text("ext_version_server", gettext("Latest version"), $server_version);
				html_separator();
			?>
        </table>                                                                                                   
        <div id="update_remarks">
            <?php html_remark("note_remove", gettext("Note"), 
				gettext("Removing the extension from the server will leave the installation and data folders untouched.")."<br />".
				gettext("Uninstalling will remove the extension and delete all extension folders including the data folders from the system."));
			?>
            <br />
            <input id="ext_update" name="ext_update" type="submit" class="formbtn" value="<?=gettext("Update");?>" 
				onclick="return confirm('<?=gettext("The selected operation will be completed. Please do not click any other buttons!");?>')" />
            <input id="ext_remove" name="ext_remove" type="submit" class="formbtn" value="<?=gettext("Remove");?>" 
				onclick="return confirm('<?=gettext("Do you really want to remove the extension from the system?");?>')" />
            <input id="ext_cleanup" name="ext_cleanup" type="submit" class="formbtn" value="<?=gettext("Uninstall");?>" 
				onclick="return confirm('<?=gettext("Do you really want to remove the extension and delete all extension folders from the system?");?>')" />
        </div>
        <table width="100%" border="0" cellpadding="6" cellspacing="0">
			<?php 
				html_separator();
				html_separator();
				html_titleline(gettext("Release Notes"));
			?>
			<tr><td>
				<textarea style="width: 100%;" id="content" name="content" class="listcontent" cols="1" rows="25" readonly="readonly"><?php unset($lines); exec("/bin/cat {$configuration['rootfolder']}/release_notes.txt", $lines); foreach ($lines as $line) { echo $line."\n"; }?></textarea>
			</td></tr>
        </table>
        <?php include("formend.inc");?>
    </td></tr>
</table>
</form>
<?php include("fend.inc");?>
