<?php
require 'vendor/autoload.php';
require_once('./vendor/pear/http_request2/HTTP/Request2.php');


/** */
$vlxxID = 7;

$link = getLink($vlxxID);

if ($link == "") {
    echo "Link is invalid";
    return;
}


//Download
file_put_contents("video.mp4", file_get_contents($link));

//Upload
$file_name = $vlxxID . '.mp4';
$file_path = './video.mp4';

$cfile = new CURLFile($file_path, mime_content_type($file_path), $file_name);
$post = array('file' => $cfile);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://up.hydrax.net/7a449dc98ca689fc19335da93157573a');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_VERBOSE, true);
$result = curl_exec($ch);
curl_close($ch);

try {
    $result = json_decode($result);
    if ($result->status == true) {
        file_put_contents('data.txt', $vlxxID . "|" . $result->slug, FILE_APPEND | LOCK_EX);
        echo "SUCCESS !";
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
