<?php


function downloadVideo($videoUrl)
{
    function downloadCallback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max)
    {
        static $filesize = null;

        switch ($notification_code) {
            case STREAM_NOTIFY_FILE_SIZE_IS:
                $filesize = $bytes_max;
                break;

            case STREAM_NOTIFY_PROGRESS:
                if ($bytes_transferred > 0) {
                    if (!isset($filesize)) {
                        printf("\rUnknown filesize.. %2d kb done..", $bytes_transferred / 1024);
                    } else {
                        $length = (int)(($bytes_transferred / $filesize) * 100);
                        printf("\rDownloading: [%-100s] %d%% (%2d/%2d kb)", str_repeat("=", $length) . ">", $length, ($bytes_transferred / 1024), $filesize / 1024);
                    }
                }
                break;
        }
    }
    // printf("Downloading...\n");

    $ctx = stream_context_create();
    stream_context_set_params($ctx, array("notification" => "downloadCallback"));

    file_put_contents(VIDEO_FILE, file_get_contents($videoUrl, false, $ctx));

    printf("\nDownload compeleted !\n");
}
