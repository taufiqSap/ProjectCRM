<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Collection;

class ExcelProcessorService
{
    /**
     * Baca file Excel dan kembalikan Collection baris dengan key yang sudah disesuaikan.
     *
     * @param  string  $filePath
     * @return \Illuminate\Support\Collection
     */
    public function process(string $filePath): Collection
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = collect($sheet->toArray(null, false));

        // 1) Fill merged cells (ffill)
       $rawRows = $rows->toArray(); // convert Collection to array

        for ($i = 1; $i < count($rawRows); $i++) {
            foreach ($rawRows[$i] as $j => $val) {
                if ($val === null || $val === '') {
                    $rawRows[$i][$j] = $rawRows[$i - 1][$j];
                }
            }  

        }

        $rows = collect($rawRows); // convert back to Collection if needed


        // 2) Drop kolom 1–4 (index 0–3)
        $rows = $rows->map(fn($row) => array_slice($row, 4));

        // 3) Hapus 2 baris header lama dan 2 baris footer
        $rows = $rows->slice(2, $rows->count() - 4)->values();

        // 4) Buat header manual
        $headerManual = collect(range(1, count($rows[0])))
            ->map(fn($i) => "Header{$i}")
            ->toArray();

        // 5) Gabungkan jadi array assoc
        $data = $rows->map(fn($row) => array_combine($headerManual, $row));

        // 6) Drop kolom tidak terpakai
        $drop = [
            "Header3","Header4","Header5","Header8","Header9","Header10",
            "Header16","Header17","Header24","Header25","Header26","Header27",
            "Header28","Header29","Header30","Header34","Header35","Header37",
            "Header38","Header39"
        ];
        $data = $data->map(fn($r) => array_diff_key($r, array_flip($drop)));

        // 7) Rename headers dan tambah `id`
        $new = [
            "date","service_order","no_invoice","nama","no_telp",
            "brand","model_name","no_frame","service_category",
            "service_package","labor_cost_service","no_part",
            "part_name","part_qty","part_price","total_labor",
            "total_part_service","total_oil_service","total_amount",
            "technician_name"
        ];

    return $data->map(function($row, $idx) use($new) {
        $row = array_values($row);
        $assoc = array_combine($new, $row);

        if (isset($assoc['date'])) {
        try {
            $date = \DateTime::createFromFormat('n/j/Y', $assoc['date']);
            $assoc['date'] = $date ? $date->format('Y-m-d') : null;
        } catch (\Exception $e) {
            $assoc['date'] = null;
        }
    }

        // Bersihkan koma dari angka-angka agar tidak error saat insert ke DB
        $assoc['labor_cost_service'] = str_replace(',', '', $assoc['labor_cost_service']);
        $assoc['part_price'] = str_replace(',', '', $assoc['part_price']);
        $assoc['total_oil_service'] = str_replace(',', '', $assoc['total_oil_service']);

        // Validasi jika bukan angka, isi dengan 0
        $assoc['labor_cost_service'] = is_numeric($assoc['labor_cost_service']) ? $assoc['labor_cost_service'] : 0;
        $assoc['part_price'] = is_numeric($assoc['part_price']) ? $assoc['part_price'] : 0;
        $assoc['total_oil_service'] = is_numeric($assoc['total_oil_service']) ? $assoc['total_oil_service'] : 0;

        $assoc['row_id'] = $idx + 1;
        return $assoc;
    });

    }
}
