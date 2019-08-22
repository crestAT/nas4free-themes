<?php
/* 
    themes-install.php
    
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
$version = "v1.0";														// extension version
$appName = "Themes";
$configName = strtolower($appName);

require_once("config.inc");

$install_dir = dirname(__FILE__);                           			// get directory where the installer script resides
$version_striped = str_replace(".", "", $version);
$verify_hostname = "--no-verify-hostname";

// install extension
global $input_errors;
global $savemsg;

// fetch release archive
$return_val = mwexec("fetch {$verify_hostname} -vo {$install_dir}/master.zip 'https://github.com/crestAT/nas4free-{$configName}/releases/download/{$version}/{$configName}-{$version_striped}.zip'", false);
if ($return_val == 0) {
    $return_val = mwexec("LC_ALL=en_US.UTF-8 tar -xf {$install_dir}/master.zip -C {$install_dir} --exclude='.git*' --strip-components 2", true);
    if ($return_val == 0) {
        exec("rm {$install_dir}/master.zip");
        exec("chmod -R 775 {$install_dir}");
        require_once("{$install_dir}/ext/extension-lib.inc");
        $configFile = "{$install_dir}/ext/{$configName}.conf";
        if (is_file("{$install_dir}/version.txt")) $file_version = exec("cat {$install_dir}/version.txt");
        else $file_version = "n/a";
        $savemsg = sprintf(gettext("Update to version %s completed!"), $file_version);
    } else { 
        $input_errors[] = sprintf(gettext("Archive file %s not found, installation aborted!"), "master.zip corrupt /"); 
        return;
    }
} else { 
    $input_errors[] = sprintf(gettext("Archive file %s not found, installation aborted!"), "{$configName}-{$version_striped}.zip"); 
    return;
}

// install / update application
if (($configuration = ext_load_config($configFile)) === false) {
    $configuration = array();             // new installation
    $new_installation = true;
}
$configuration['appname'] = $appName;
$configuration['version'] = exec("cat {$install_dir}/version.txt");
$configuration['rootfolder'] = $install_dir;
$configuration['postinit'] = "/usr/local/bin/php-cgi -f {$install_dir}/{$configName}-start.php";
$configuration['shutdown'] = "/usr/local/bin/php-cgi -f {$install_dir}/{$configName}-stop.php";

// remove start/stop commands and existing old rc format entries
ext_remove_rc_commands($configName);
$configuration['rc_uuid_start'] = $configuration['postinit'];
$configuration['rc_uuid_stop'] = $configuration['shutdown'];
ext_create_rc_commands($appName, $configuration['rc_uuid_start'], $configuration['rc_uuid_stop']);
ext_save_config($configFile, $configuration);

#if ($new_installation) {
	echo "\nInstallation completed, use WebGUI | Extensions | {$appName} to configure the application!\n";
#}
require_once("{$configuration['rootfolder']}/{$configName}-start.php");					// initialize extension
?>
