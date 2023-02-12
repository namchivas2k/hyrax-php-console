<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require 'vendor/autoload.php';
require_once('./vendor/pear/http_request2/HTTP/Request2.php');
define('VIDEO_FILE',  './video.mp4');

/**
 * cd ./hyrax-php-console && php index.php
 */

/** */
$vlxxID =  0;
if (array_search('-id', $argv)) {
    if (isset($argv[array_search('-id', $argv) + 1])) {
        $vlxxID = $argv[array_search('-id', $argv) + 1];
    }
}

if ($vlxxID <= 0) {
    echo "Invalid VLXX id";
    return;
}


printf('START ID: %d', $vlxxID);
// GET LINK
echo "\nGet link\n";
$link = getLink($vlxxID);

// $link = 'https://sample-videos.com/video123/mp4/720/big_buck_bunny_720p_2mb.mp4';
if ($link == "") {
    echo "Link is invalid";
    return;
}


echo "\nLink: " . substr($link, 0, 60) . "...\n";


if (array_search('-sk', $argv)) {
    echo "\nSKIP DOWNLOAD !\n";
} else {
    //Delete file if exists
    if (file_exists(VIDEO_FILE)) unlink(VIDEO_FILE);
    //Download
    require_once('./Download.php');
    downloadVideo($link);
}


//Upload
function curl_progress_callback($resource, $download_size, $downloaded, $upload_size, $uploaded)
{

    if ($upload_size > 0) {
        $length = (int)(($uploaded / $upload_size) * 100);
        printf("\rUploading: [%-100s] %d%% (%2d/%2d kb)", str_repeat("=", $length) . ">", $length, ($uploaded / 1024), $upload_size / 1024);
    }
}


function _mime_content_type($filename)
{
    $result = new finfo();

    if (is_resource($result) === true) {
        return $result->file($filename, FILEINFO_MIME_TYPE);
    }

    return false;
}


$file_path = VIDEO_FILE;
$file_name = 'vlxx_' . $vlxxID . '.mp4';
$cfile = new CURLFile($file_path, _mime_content_type($file_path), $file_name);
$post = array('file' => $cfile);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://up.hydrax.net/7a449dc98ca689fc19335da93157573a');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'curl_progress_callback');
curl_setopt($ch, CURLOPT_NOPROGRESS, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt($ch, CURLOPT_VERBOSE, true);
$result = curl_exec($ch);
curl_close($ch);



echo "\n\n";
print_r($result);

try {
    $result = json_decode($result);
    if ($result->status == true) {
        file_put_contents('data.txt', $vlxxID . "|" . $result->slug . "\n", FILE_APPEND | LOCK_EX);

        echo "\n\nSUCCESS !";
    }
} catch (\Throwable $th) {
    echo "ERROR !";
    print_r($result);
    echo $th->getMessage();
}



/**
 * Function get link
 */
function getLink($v_id)
{
    $mRes = '';
    $request = new HTTP_Request2();
    $request->setUrl('https://vlxx.info/ajax.php');
    $request->setMethod(HTTP_Request2::METHOD_POST);
    $request->addPostParameter('vlxx_server', 1);
    $request->addPostParameter('server', 1);
    $request->addPostParameter('id', $v_id);
    $request->setConfig(array('follow_redirects' => TRUE));
    try {
        $response = $request->send();
        if ($response->getStatus() == 200) {
            preg_match('/\[\{(.*)\}\]/', $response->getBody(), $matches);
            if (is_array($matches) && count($matches) > 0) {
                $result = $matches[0];
                $result = str_replace("\\", '', $result);
                $result = json_decode($result);

                if (is_array($result) && count($result) > 0) {
                    if (count($result) == 1) {
                        if (preg_match('/^http/i', $result[0]->file)) {
                            $mRes = $result[0]->file;
                        }
                    } else {
                        $mRes = $result[count($result) - 1]->file;
                    }
                }
            }
        }
    } catch (HTTP_Request2_Exception $e) {
        $mRes = '';
    }
    return $mRes;
}
