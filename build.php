<?php
// set only to run from command line
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line!');
}

// get args from command line
$nodeBuild = in_array('--node-build', $argv);

if ($nodeBuild) {
    // Build Commands
    echo "Building fluentCrmContact Script\n";
    exec("cd resources/fluentCrmContact;npx mix --production", $out1, $err1);
    if ($err1) {
        print_r($err1);
    }
    echo implode("\n", $out1); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo "\nBuilding FluentFormEditor Script\n";
    exec("cd resources/FluentFormEditor;npx mix --production", $out2, $err2);
    if ($err2) {
        print_r($err2);
    }
    echo implode("\n", $out2); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    echo "\nBuilding Main App Frontend\n";
    $ret = exec("npx mix --mix-config=public-webpack.mix.js --production", $out3, $err3);
    if ($err3) {
        print_r($err3);
    }
    echo implode("\n", $out3); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    echo "\nBuilding Main App\n";
    $ret = exec("npx mix --production", $out3, $err4);
    if ($err4) {
        print_r($err4);
    }
    echo implode("\n", $out3); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

define('WP_USE_THEMES', false);
require(__DIR__ . '/../../../wp-blog-header.php');


$fileLists = [
    'readme.txt',
    'index.php',
    'fluent-booking.php',
    'composer.json',
];
$renameFiles = [];

$targetFolder = 'builds/fluent-booking';

$folderLists = [
    'app',
    'assets',
    'boot',
    'config',
    'database',
    'language',
    'vendor',
];

global $wp_filesystem;
require_once(ABSPATH . '/wp-admin/includes/file.php');
WP_Filesystem();

function deleteFileOrFolder($fileOrFolder)
{
    echo 'Deleting: ' . $fileOrFolder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    // new line
    echo "\n";
    $fileSystemDirect = new WP_Filesystem_Direct(false);
    $result = $fileSystemDirect->rmdir($fileOrFolder, true);

    if (!$result) {
        echo 'ERROR on Delete: ' . $fileOrFolder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

function copyFileOrFolder($src, $dest)
{
    if (is_file($src)) {
        copy($src, $dest);
        return;
    }

    $result = copy_dir($src, $dest);

    if (is_wp_error($result)) {
        echo 'ERROR: ' . $result->get_error_message(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

// delete the folder if exists
if (file_exists($targetFolder)) {
    deleteFileOrFolder($targetFolder);
}

if (!file_exists($targetFolder)) {
    mkdir($targetFolder, 0755, true);
}

foreach ($fileLists as $file) {
    $source = __DIR__ . '/' . $file;
    $target = $targetFolder . '/' . $file;
    copyFileOrFolder($source, $target);
}

foreach ($folderLists as $folder) {
    $source = __DIR__ . '/' . $folder;
    $target = $targetFolder . '/' . $folder;
    echo $target . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    copyFileOrFolder($source, $target);
}

echo "\nFree Version Build Completed";
