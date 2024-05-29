<?php
// set only to run from command line
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line!');
}

// get args from command line
$nodeBuild = in_array('--node-build', $argv);

$isProBuild = in_array('--pro-build', $argv);

if($nodeBuild) {
    // Build Commands
    echo "Building fluentCrmContact Script\n";
    exec("cd resources/fluentCrmContact;npx mix --production", $out1, $err1);
    if($err1) {
        print_r($err1);
    }
    echo implode("\n", $out1); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo "\nBuilding FluentFormEditor Script\n";
    exec("cd resources/FluentFormEditor;npx mix --production", $out2, $err2);
    if($err2) {
        print_r($err2);
    }
    echo implode("\n", $out2); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    echo "\nBuilding Main App Frontend\n";
    $ret = exec("npx mix --mix-config=public-webpack.mix.js --production", $out3, $err3);
    if($err3) {
        print_r($err3);
    }
    echo implode("\n", $out3); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

    echo "\nBuilding Main App\n";
    $ret = exec("npx mix --production", $out3, $err4);
    if($err4) {
        print_r($err4);
    }
    echo implode("\n", $out3); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

define('WP_USE_THEMES', false);
require(__DIR__ . '/../../../wp-blog-header.php');

if($isProBuild) {
    $fileLists = [
        'readme.txt',
        'index.php',
        'fluent-booking-pro.php',
        'composer.json',
    ];
    $renameFiles = [];
    $deleteFolders = [];
    $deleteFiles = [
        'app/ComposerScript.php',
    ];
    $targetFolder = 'builds/fluent-booking-pro';
} else {
    $fileLists = [
        'readme-org.txt',
        'index.php',
        'fluent-booking.php',
        'composer.json',
    ];
    $renameFiles = [
        'readme-org.txt'     => 'readme.txt'
    ];

    $deleteFolders = [
        'app/Services/Integrations',
        'app/Services/Libs/RRule',
        'app/Services/PluginManager'
    ];

    $deleteFiles = [
        'app/Models/Webhook.php',
        'app/Models/Order.php',
        'app/Models/OrderItems.php',
        'app/Models/Transactions.php',
        'app/Services/OrderHelper.php',
        'app/Http/Routes/pro_routes.php',
        'app/Hooks/includes.php',
        'app/Hooks/Handlers/GlobalPaymentHandler.php',
        'app/Hooks/Handlers/GlobalNotificationHandler.php',
        'app/Http/Controllers/LicenseController.php',
        'app/Http/Controllers/WebhookController.php',
        'app/Http/Controllers/ZoomController.php',
        'app/Http/Controllers/TwilioController.php',
        'app/Http/Controllers/PaymentMethodController.php',
        'app/Http/Controllers/IntegrationController.php',
        'app/Http/Controllers/IntegrationSettingsController.php',
        'app/Http/Controllers/IntegrationManagerController.php',
        'app/Http/Controllers/CalendarIntegrationController.php',
        'language/fluent-booking-pro.pot',
        'app/ComposerScript.php',
        'database/Migrations/BookingOrdersMigrator.php',
        'database/Migrations/BookingTransactionsMigrator.php',
        'database/Migrations/OrdersItemsMigrator.php',
    ];
    $targetFolder = 'builds/fluent-booking';
}

$folderLists = [
    'app',
    'assets',
    'boot',
    'config',
    'language',
    'vendor',
    'database'
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

    if(!$result) {
        echo 'ERROR on Delete: '.$fileOrFolder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

function copyFileOrFolder($src, $dest)
{
    if (is_file($src)) {
        copy($src, $dest);
        return;
    }

    $result = copy_dir($src, $dest);

    if(is_wp_error($result)) {
        echo 'ERROR: '.$result->get_error_message(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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

foreach ($deleteFolders as $folder) {
    $target = $targetFolder . '/' . $folder;
    deleteFileOrFolder($target);
}

// delete the $deleteFiles files now
foreach ($deleteFiles as $file) {
    $target = $targetFolder . '/' . $file;
    deleteFileOrFolder($target);
}

// Rename Required Files
foreach ($renameFiles as $source => $target) {
    rename($targetFolder . '/' . $source, $targetFolder . '/' . $target);
}

if($isProBuild) {
    echo "\nPro Build Completed";
    return;
}

// Let's replace the text domains
function replace_text_domain($directory_path) {
    $directory_iterator = new RecursiveDirectoryIterator($directory_path);
    $iterator = new RecursiveIteratorIterator($directory_iterator);

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        $modified_content = $content;

        // Improved patterns to handle various cases of text domain usage
        $patterns = [
            "/('|\")fluent-booking-pro('|\")/",
        ];

        foreach ($patterns as $pattern) {
            $modified_content = preg_replace($pattern, "'fluent-booking'", $modified_content);
        }

        if ($content !== $modified_content) {
            file_put_contents($file->getPathname(), $modified_content);
            echo "Modified: " . $file->getPathname() . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        } else {
            echo "No modification needed: " . $file->getPathname() . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }
}
replace_text_domain($targetFolder.'/app');

// Modify the migration table
function remove_migrate_payment_tables($file_path) {
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);

        // Removing the migratePaymentTables method
        $content = preg_replace('/public static function migratePaymentTables\(\)\s*{[^}]*}\s*/s', '', $content);

        // Removing the self::migratePaymentTables(); call and tidying up
        $content = preg_replace('/\s*self::migratePaymentTables\(\);/', '', $content);

        // Removing unnecessary whitespace
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        // Correcting the last line indentation
        $content = preg_replace('/}\s*}$/', "}\n}", $content);

        // Saving the updated content back to the file
        file_put_contents($file_path, $content);
        echo "Migration File Updated\n";
    } else {
        echo "The specified file does not exist.\n";
    }
}
// remove_migrate_payment_tables($targetFolder.'/database/DBMigrator.php');


function update_configuration_file($file_path) {
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);

        // Replace 'Fluent Booking Pro' with 'Fluent Booking'
        $content = str_replace('Fluent Booking Pro', 'Fluent Booking', $content);

        // Replace 'fluent-booking-pro' with 'fluent-booking'
        $content = str_replace('fluent-booking-pro', 'fluent-booking', $content);

        // Saving the updated content back to the file
        file_put_contents($file_path, $content);
        echo "Config File Updated.";
    } else {
        echo "The specified file does not exist.";
    }
}
update_configuration_file($targetFolder.'/config/app.php');

echo "\nFree Version Build Completed";
