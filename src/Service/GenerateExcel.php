<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class GenerateExcel
{
    /**
     * @param $headers
     * @param $rows
     * @param string $fileName
     * @param string $type
     * @return BinaryFileResponse
     * @throws \Exception
     */
    public function generateExcel($headers, $rows, $fileName = 'export', $type = 'tmp')
    {
        $fileName = sprintf('%s.xlsx', $fileName);
        $fileToWrite = sprintf('/tmp/%s.xlsx', $type);

        $onesheet = new \OneSheet\Writer();

        $onesheet->setFreezePaneCellId('A2');
        $onesheet->enableCellAutosizing();

        $onesheet->addRow($headers);

        foreach($rows as $row) {
            $row = array_map(function($col) {
                if (strlen($col) === 0) {
                   return '-';
                }

                return $col;
            }, $row);

            $onesheet->addRow($row);
        }

        $onesheet->writeToFile($fileToWrite);

        $response = new BinaryFileResponse($fileToWrite, 200, [
            'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);
        $response->setMaxAge(0);
        $response->setPublic();

        return $response;
    }
}
