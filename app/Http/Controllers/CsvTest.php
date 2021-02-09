<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\TestCase;

class ExtractCsvTest extends TestCase
{

    public function testExtractCsv()
    {
        $csvRows = $this->readCsv('/home/husnan/Downloads/temp/try_load_invoice_gabisa.csv', 1);
        if (array_key_exists("error", $csvRows)) {
            return [
                'success' => false,
                'message' => $csvRows['message'],
                'exception' => $csvRows['exception']
            ];
        }
        echo var_dump($csvRows);
        $this->assertEquals(3, $this->count($csvRows));
    }

    private function readCsv($path, $csvId): array
    {
        chmod($path, 0777);
        $file = fopen($path, 'r');

        $headers = fgetcsv($file);

        $rows = [];
        $counter = 1;

        while (!feof($file)) {
            $counter++;

            $csv = fgetcsv($file);

            $mappingCsv = $this->mappingCsvRowAsTableRow($csv, $csvId, $headers);

            if (array_key_exists('error', $mappingCsv)) {
                return [
                    'error' => true,
                    'message' => 'Error while extraction on row ' . $counter,
                    'exception' => $mappingCsv['exception']
                ];
            }


            if (!empty($mappingCsv)) {
                array_push($rows, $mappingCsv);
            } else {
                Log::error("You have an empty row");
            }
        }
        fclose($file);
        return $rows;
    }

    private function mappingCsvRowAsTableRow($csvContent, $csvId, $headers)
    {

        if (!is_bool($csvContent)) {
            $now = Carbon::now()->toDateTimeString();

            try {
                $csvContent = array_combine($headers, $csvContent);
//                $filePath = $csvContent['File Path'];
                $csvContent2 = ['File Path' => 'Test'];
                $filePath2 = $csvContent2['File Path'];
                print_r($csvContent);
                print_r(array_keys($csvContent));
                return [
                    'csv_id' => $csvId,
                    'file_path' => $csvContent['File Path'],
                    'name' => $csvContent['Name'],
                    'path_s3' => 'NOT_PROCESSING_YET',
                    'file_size' => -1,
                    'file_type' => empty($csvContent['Type']) ? $this->get_file_extension($csvContent['Name']) : $csvContent['Type'],
                    'jnid' => $csvContent['jnid'],
                    'api_key' => $csvContent['Api Key'],
                    'status' => config('jobstatus.queue'),
                    'description' => $csvContent['Description'],
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            } catch (\Exception $e) {
                return [
                    'error' => true,
                    'exception' => $e->getMessage()
                ];

            }
        } else {
            return [];
        }

    }

    public function get_file_extension($file_name)
    {
        return substr(strrchr($file_name, '.'), 1);
    }

    function removeBomUtf8($s){
        if(substr($s,0,3)==chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'))){
            return substr($s,3);
        }else{
            return $s;
        }
    }
}
