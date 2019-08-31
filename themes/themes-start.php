<?php
/*
    themes-start.php 

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
require_once("config.inc");

$configName = "themes";
$rootfolder = dirname(__FILE__);
$configFile = "{$rootfolder}/ext/{$configName}.conf";
require_once("{$rootfolder}/ext/extension-lib.inc");

if (($configuration = ext_load_config($configFile)) === false) {
    exec("logger {$configName}-extension: configuration file {$configFile} not found, startup aborted!");
    exit;
}

$return_val = 0;
// create links to extension files
$return_val += mwexec("ln -sf {$rootfolder}/locale-{$configName} /usr/local/share/", true);
$return_val += mwexec("ln -sf {$rootfolder}/{$configName}-config.php /usr/local/www/{$configName}-config.php", true);
$return_val += mwexec("ln -sf {$rootfolder}/{$configName}-update_extension.php /usr/local/www/{$configName}-update_extension.php", true);
$return_val += mwexec("mkdir -p /usr/local/www/ext", true);
$return_val += mwexec("ln -sf {$rootfolder}/ext /usr/local/www/ext/{$configName}", true);

// check for product name and eventually rename translation files for new product name (XigmaNAS)
$domain = strtolower(get_product_name());
if ($domain <> "nas4free") $return_val += mwexec("find {$rootfolder}/locale-{$configName} -name nas4free.mo -execdir mv nas4free.mo {$domain}.mo \;", true);

// perform backups
if (!file_exists("/usr/local/www/css-ORIGINAL")) {
	mkdir("/usr/local/www/css-ORIGINAL");
	mwexec("cp /usr/local/www/css/*.css /usr/local/www/css-ORIGINAL/", true);
}
if (!file_exists("/usr/local/www/images-ORIGINAL")) {
	mkdir("/usr/local/www/images-ORIGINAL");
	mwexec("cp /usr/local/www/images/*.gif /usr/local/www/images-ORIGINAL/", true);
	mwexec("cp /usr/local/www/images/home.png /usr/local/www/images-ORIGINAL/", true);
}
if (!file_exists("/usr/local/www/css.php-ORIGINAL")) {
	mkdir("/usr/local/www/css.php-ORIGINAL");
	mwexec("cp /usr/local/www/fbegin.inc /usr/local/www/css.php-ORIGINAL/", true);
	mwexec("cp /usr/local/www/filechooser.php /usr/local/www/css.php-ORIGINAL/", true);
}

if (!file_exists("/usr/local/www/js/spinner.js-ORIGINAL")) {
	mwexec("cp /usr/local/www/js/spinner.js /usr/local/www/js/spinner.js-ORIGINAL", true);
}

# needed to patch sucessfully
mwexec("cp /usr/local/www/css.php-ORIGINAL/fbegin.inc /usr/local/www/", true);			
mwexec("cp /usr/local/www/js/spinner.js-ORIGINAL /usr/local/www/js/spinner.js", true);

# create .css.php script
			$setCssPhpScript = "{$configuration['rootfolder']}/base/setCssPhpScript.sh";
			$timeStamp = '<?php echo time();?>';
			$script = fopen($setCssPhpScript, "w");
			fwrite($script,
"#!/bin/sh
# WARNING: THIS IS AN AUTOMATICALLY CREATED SCRIPT, DO NOT CHANGE THE CONTENT!
sed -i '' 's/.css.php/.css?t={$timeStamp}/g' /usr/local/www/fbegin.inc
sed -i '' 's/spinner.js/spinner.js?t={$timeStamp}/g' /usr/local/www/fbegin.inc
sed -i '' 's/.css.php/.css?t={$timeStamp}/g' /usr/local/www/filechooser.php
# Spinner
sed -i '' 's/#4D4D4D/{$configuration['themes'][$configuration['currentTheme']]['tbBUTTONFACE']}/g' /usr/local/www/js/spinner.js
sed -i '' 's/white/{$configuration['themes'][$configuration['currentTheme']]['tbBACKGROUND']}/g' /usr/local/www/fbegin.inc
sync
");
			fclose($script);
			chmod($setCssPhpScript, 0755);
			mwexec($setCssPhpScript, true);

if ($return_val == 0) {
	if ($configuration['enable']) {
		mwexec("cp {$configuration['rootfolder']}/live/css/* /usr/local/www/css/", true);
		mwexec("cp {$configuration['rootfolder']}/base/images/{$configuration['themes'][$configuration['currentTheme']]['themeImages']}/* /usr/local/www/images/", true);
		mwexec("cp {$configuration['rootfolder']}/base/images/home-*.png /usr/local/www/images/", true);
		mwexec("cp {$configuration['rootfolder']}/base/images/home-{$configuration['themes'][$configuration['currentTheme']]['homeIcon']}.png /usr/local/www/images/home.png", true);
		flush();
	} 
	exec("logger {$configName}-extension: started successfully");
}
else exec("logger {$configName}-extension: error(s) during startup, failed with return value = {$return_val}");
?>
