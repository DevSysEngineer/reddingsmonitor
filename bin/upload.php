<?php

namespace Reddingsmonitor\Bin;

/* Set dir */
chdir(__DIR__);

class Upload {

    private $_targetURL = '';
    private $_secretKey = '';
    private $_enableDebug = FALSE;
    private $_logFile = 'upload.log';

    public function setTargetURL(string $targetURL) : void {
        /* Remove / from target */
        if (substr($targetURL, -1) === '/') {
            $targetURL = substr($targetURL, 0, -1);
        }

        /* Set target */
        $this->_targetURL = $targetURL;
    }

    public function setSecretKey(string $secretKey) : void {
        $this->_secretKey = $secretKey;
    }

    public function enableDebug(bool $state) : void {
        $this->_enableDebug = $state;
    }

    public function log(string $text, bool $stop = FALSE) : void {
        /* Create text */
        $text = '[' . date('c') . '] ' . $text . PHP_EOL;

        /* Show only text when debug mode */
        if ($this->_enableDebug) {
            echo $text;
        }

        /* Write file */
        file_put_contents($this->_logFile, $text, FILE_APPEND | LOCK_EX);

        /* Stop script */
        if ($stop) {
            exit;
        }
    }

    public function getToken() : string {
        /* Try to get token */
        $result = FALSE;
        do {
            /* Try to open website */
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_targetURL . '/access.php');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $result = curl_exec($ch);

            /* Try to get status code */
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($status !== 200) {
                /* Create log */
                $this->log('Page not found, try again');

                /* Restart again */
                $result = FALSE;
                sleep(60);
                continue;
            }

            /* Close curl */
            curl_close($ch);
        } while ($result === FALSE);

        /* Return token */
        return trim($result);
    }

    public function sendFile(string $token, array $post) : bool {
        /* Create URL */
        $fullURL = $this->_targetURL . '/scripts/upload.php?secretkey=' . $this->_secretKey . '&token=' . $token;

        /* Try to open website */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type' => 'multipart/form-data']);
        curl_setopt($ch, CURLOPT_URL, $fullURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $result = curl_exec($ch);

        /* Try to get status code */
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status !== 200) {
            switch ($status) {
                case 405:
                    $this->log('Upload failed, secretkey is invalid');
                    break;
                default:
                    $this->log('Upload failed, code: ' . $status);
                    break;
            }
            $result = FALSE;
        }

        /* Close curl */
        curl_close($ch);

        /* Check the result */
        return ($result === 'OK');
    }
}

/* Create object */
$upload = new Upload;

/* Try to get arguments */
$params = ['--url', '--secretKey', '--filename'];
$commandLine = $argv;
array_shift($commandLine);
if (empty($commandLine)) {
    $upload->log('Missing arguments', TRUE);
}

/* Parse arguments */
$settings = [];
$lineFound = '';
foreach ($commandLine as $line) {
    if ($lineFound !== '') {
        $settings[$lineFound] = $line;
        $lineFound = '';
    } else if (in_array($line, $params)) {
        $lineFound = $line;
    }
}

/* Check params */
foreach ($params as $setting) {
    if (empty($settings[$setting])) {
        $upload->log('Value missing for agument ' . $setting, TRUE);
    }
}

/* Set settings */
$upload->setTargetURL($settings['--url']);
$upload->setSecretKey($settings['--secretKey']);

/* Check if we need to show errors */
$stopForking = FALSE;
if (in_array('--debug', $commandLine, TRUE)) {
    $upload->enableDebug(TRUE);
    $stopForking = TRUE;
}

/* Fork only when we are not running in debug mode */
if (!$stopForking) {
    /* Run in background. */
    $pid = pcntl_fork();
    if ($pid === -1) {
        $upload->log('Can\'t fork...', TRUE);
    } else if ($pid) {
        /* Parent. */
        $upload->log('Running upload in background on PID: ' . $pid, TRUE);
    }
}

/* Start while */
$filename = $settings['--filename'];
while(TRUE) {
    /* Create log */
    $upload->log('Try to get token');

    /* Try to get token */
    $index = 0;
    $modifyTime = 0;
    $token = $upload->getToken();
    do {
        /* Check if file exists */
        $found = FALSE;
        do {
            clearstatcache(TRUE);
            if (file_exists($filename)) {
                $fileTime = filemtime($filename);
                if ($fileTime !== NULL && $fileTime > $modifyTime) {
                    $modifyTime = $fileTime;
                    $found = TRUE;
                }
            } else {
                /* Create log */
                $upload->log("File '" . $filename . "' not exists");

                /* Sleep for 10 seconds */
                sleep(10);
            }
        } while (!$found);

        /* Try to upload file */
        $post = ['kml'=> curl_file_create($filename)];
        if (!$upload->sendFile($token, $post)) {
            /* Error */
            $index++;
            $modifyTime = 0;

            /* Sleep for 10 seconds */
            sleep(10);
        }
    } while ($index < 5);
}
