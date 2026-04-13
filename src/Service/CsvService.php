<?php

namespace App\Service;

class CsvService
{
    private string $dataDir;

    public function __construct(string $dataDir)
    {
        $this->dataDir = $dataDir;
    }

    public function read(string $filename): array
    {
        $file = $this->dataDir . '/' . $filename;
        if (!file_exists($file)) {
            return [];
        }

        $data = [];
        if (($handle = fopen($file, 'r')) !== false) {
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    public function write(string $filename, array $data, array $headers): void
    {
        $file = $this->dataDir . '/' . $filename;
        $handle = fopen($file, 'w');

        fputcsv($handle, $headers);
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
    }

    public function append(string $filename, array $row): void
    {
        $file = $this->dataDir . '/' . $filename;
        $handle = fopen($file, 'a');
        fputcsv($handle, $row);
        fclose($handle);
    }
}
