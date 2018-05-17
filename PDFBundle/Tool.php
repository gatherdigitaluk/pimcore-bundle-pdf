<?php

namespace PDFBundle;

class Tool
{
    const TOOL_PATH = PIMCORE_PROJECT_ROOT . '/src/PDFBundle/Tool/';

    public static function printToFile($url, $path, $scale = 1)
    {
        $cmd = sprintf(
            'node %sindex.js --url %s --path %s --scale %s  2>&1',
            self::TOOL_PATH,
            $url,
            $path,
            $scale
        );

        $output = trim(shell_exec($cmd));

        if (preg_match('/\[success\]/', $output) === 1) {
            return (object) [
                'success' => true,
            ];
        }
        else {
            return (object) [
                'success' => false,
                'error' => $output,
            ];
        }
    }

    public static function printToData($url)
    {
        $tmpPath = '/tmp/pdf_'.uniqid().'.pdf';
        $result = self::printToFile($url, $tmpPath);

        if ($result->success == false) {
            return $result;
        }

        $data = file_get_contents($tmpPath);
        unlink($tmpPath);

        return (object) [
            'success' => true,
            'data' => $data,
        ];
    }

    public static function printToDataURI($url)
    {
        $result = self::printToData($url);

        if ($result->success == false) {
            return $result;
        }

        $uri = 'data:application/pdf;base64,' . base64_encode($result->data);

        return (object) [
            'success' => true,
            'dataURI' => $uri,
        ];
    }

    public static function printAsDownload($url, $filename = 'download.pdf')
    {
        $tmpPath = '/tmp/pdf_'.uniqid().'.pdf';
        $result = self::printToFile($url, $tmpPath);

        if ($result->success == false) {
            return $result;
        }

        header('Content-type:  application/pdf');
        header('Content-Length: ' . filesize($tmpPath));
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        readfile($tmpPath);
        unlink($tmpPath);
        exit; // stop sending extra bytes
    }

}
