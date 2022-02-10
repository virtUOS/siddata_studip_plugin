<?php

$target = 'zip';
$addVersionSuffix = TRUE;
$suffix = '';


if ($addVersionSuffix && (($version = getVersionFromManifest()) != FALSE))
{
    $suffix = $suffix.'_'.$version;
    printInfo('Added VERSION Suffix: '.$suffix);
}
if (isset($_SERVER['argv'][1])) {
    $target = $_SERVER['argv'][1];
}

switch ($target) {
    case 'zip':
        zip($suffix);
        break;
    default:
        zip($suffix);
        break;
}

/**
 * Creates the Stud.IP plugin zip archive.
 * @param string $suffix
 */
function zip($suffix = '') {
    $archive = new ZipArchive();
    $archive->open('SiddataPlugin'.$suffix.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
    addDirectories($archive, array(
        'assets',
        'controllers',
        'lib',
        'migrations',
        'models',
        'templates',
        'utils',
        'views'
    ));
    $archive->addFile('README.md');
    $archive->addFile('plugin.manifest');
    $archive->addFile('SiddataPlugin.php');
    $archive->close();

    printSuccess('created the Stud.IP plugin zip archive');
}

/**
 * Recursively adds a directory tree to a zip archive.
 *
 * @param ZipArchive $archive           The zip archive
 * @param string     $directory         The directory to add
 * @param string     $ignoredFilesRegex Regular expression that matches
 *                                      files which should be ignored
 */
function addDirectory(ZipArchive $archive, $directory, $ignoredFilesRegex = '') {
    $archive->addEmptyDir($directory);

    foreach (glob($directory.'/*') as $file) {
        if (is_dir($file)) {
            addDirectory($archive, $file, $ignoredFilesRegex);
        } else {
            if ($ignoredFilesRegex === '' || !preg_match($ignoredFilesRegex, $file)) {
                $archive->addFile($file);
            } else {
                printError('ignore '.$file);
            }
        }
    }
}

/**
 * Recursively adds directory trees to a zip archive.
 *
 * @param ZipArchive $archive           The zip archive
 * @param array      $directories       The directories to add
 * @param string     $ignoredFilesRegex Regular expression that matches
 *                                      files which should be ignored
 */
function addDirectories(ZipArchive $archive, array $directories, $ignoredFilesRegex = '') {
    foreach ($directories as $directory) {
        addDirectory($archive, $directory, $ignoredFilesRegex);
    }
}

/**
 * Read a file in which the version is mentioned in the for 'version=*'.
 *
 * @param string $fileName The Manifest-File to read
 * @return string
 */
function getVersionFromManifest($fileName = './plugin.manifest') {

    $fileData=file_get_contents($fileName,'r');
    if (FALSE === $fileData) {
        return FALSE;
    }
    preg_match("/(?:version=|version =)\s*((?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?)/i", $fileData, $matches);
    return trim($matches[1]);
}

/**
 * Prints a success message to the standard output stream of the console.
 *
 * @param string $message The message to print
 */
function printSuccess($message)
{
    echo "\033[32m".$message."\033[39m".PHP_EOL;
}

/**
 * Prints an info message to the standard output stream of the console.
 *
 * @param string $message The message to print
 */
function printInfo($message)
{
    echo $message.PHP_EOL;
}

/**
 * Prints an error message to the standard output stream of the console.
 *
 * @param string $message The message to print
 */
function printError($message)
{
    file_put_contents('php://stderr', "\033[31m".$message."\033[39m".PHP_EOL);
}

