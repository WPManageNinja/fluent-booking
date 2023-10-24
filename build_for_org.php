<?php

$fileLists = [
    'readme-org.txt',
    'index.php',
    'fluent-booking.php',
    'composer.json',
];

$renameFiles = [
    'readme-org.txt' => 'readme.txt'
];

$folderLists = [
    'app',
    'assets',
    'boot',
    'config',
    'language',
    'resources',
    'vendor'
];

$deleteFolders = [
    'app/Services/Libs/Rrule',
    'app/Services/Libs/PluginManager',
    'app/Services/Integrations/Calendars',
    'app/Services/Integrations/FluentCRM',
    'app/Services/Integrations/FluentForms',
    'app/Services/Integrations/PaymentMethods',
    'app/Services/Integrations/Twilio',
    'app/Services/Integrations/WebHook',
    'app/Services/Integrations/ZoomMeeting'
];

$deleteFiles = [
    'app/Services/Integrations/pro-integrations.php',
    'app/Http/Routes/pro_routes.php',
    'app/Http/Controllers/LicenseController.php',
    'app/Http/Controllers/WebhookController.php',
    'app/Http/Controllers/ZoomController.php',
    'app/Http/Controllers/PaymentMethodController.php',
    'app/Http/Controllers/CalendarIntegrationController.php',
];


function cpy($source, $dest)
{
    if (is_dir($source)) {
        $dir_handle = opendir($source);
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (is_dir($source . "/" . $file)) {
                    if (!is_dir($dest . "/" . $file)) {
                        mkdir($dest . "/" . $file);
                    }
                    cpy($source . "/" . $file, $dest . "/" . $file);
                } else {
                    copy($source . "/" . $file, $dest . "/" . $file);
                }
            }
        }
        closedir($dir_handle);
    } else {
        copy($source, $dest);
    }
}

$targetFolder = 'builds/org_version';

// delete the folder if exists
if (file_exists($targetFolder)) {
    $files = glob($targetFolder . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}

if (!file_exists($targetFolder)) {
    mkdir($targetFolder, 0755, true);
}

foreach ($fileLists as $file) {
    $source = __DIR__ . '/' . $file;
    $target = $targetFolder . '/' . $file;
    cpy($source, $target);
}

foreach ($renameFiles as $source => $target) {
    rename($targetFolder . '/' . $source, $targetFolder . '/' . $target);
}

foreach ($folderLists as $folder) {
    $source = __DIR__ . '/' . $folder;
    $target = $targetFolder . '/' . $folder;
    cpy($source, $target);
}

foreach ($deleteFolders as $folder) {
    $target = $targetFolder . '/' . $folder;
    rmdir($target);
}

// delete the $deleteFiles files now
foreach ($deleteFiles as $file) {
    $target = $targetFolder . '/' . $file;
    unlink($target);
}
