<?php

require 'vendor/autoload.php';
require_once('./vendor/pear/http_request2/HTTP/Request2.php');

/**
 * Function get link
 */
function getLink($vlxxID)
{
    $mRes = '';
    $request = new HTTP_Request2();
    $request->setUrl('https://vlxx.info/ajax.php');
    $request->setMethod(HTTP_Request2::METHOD_POST);
    $request->addPostParameter('vlxx_server', 1);
    $request->addPostParameter('server', 1);
    $request->addPostParameter('id', $vlxxID);
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



print_r(getLink(5));
