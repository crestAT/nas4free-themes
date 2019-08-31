<?php
/*
    themes-stop.php

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

unlink_if_exists("/usr/local/share/locale-{$configName}");
unlink_if_exists("/usr/local/www/{$configName}-config.php");
unlink_if_exists("/usr/local/www/{$configName}-update_extension.php");
unlink_if_exists("/usr/local/www/ext/{$configName}");
mwexec("rmdir -p /usr/local/www/ext");
mwexec("cp /usr/local/www/css-ORIGINAL/* /usr/local/www/css/", true);
mwexec("cp /usr/local/www/images-ORIGINAL/* /usr/local/www/images/", true);
mwexec("cp /usr/local/www/css.php-ORIGINAL/fbegin.inc /usr/local/www/fbegin.inc", true);
mwexec("cp /usr/local/www/css.php-ORIGINAL/filechooser.php /usr/local/www/filechooser.php", true);
mwexec("cp /usr/local/www/js/spinner.js-ORIGINAL /usr/local/www/js/spinner.js", true);
mwexec("rm -R /usr/local/www/css-ORIGINAL");
mwexec("rm -R /usr/local/www/images-ORIGINAL");
mwexec("rm -R /usr/local/www/css.php-ORIGINAL");
mwexec("rm /usr/local/www/js/spinner.js-ORIGINAL");

exec("logger {$configName}-extension: stopped"); 
?>
