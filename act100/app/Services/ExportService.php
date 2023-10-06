<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Illuminate\Support\Facades\Log;

class ExportService
{
    public function makePdf($file_name,$foloder_name)
    {
        Log::info('ExportService makePdf START');

        // もとになるExcelを読み込み
        $excel_file = storage_path('app/public/invoice/xls/tmp/tmp_invoice.xlsx');
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($excel_file);

        // 編集するシート名を指定
        $worksheet = $spreadsheet->getSheetByName('invoice');

        // セルに指定した値挿入 M1
        $worksheet->setCellValue('M1', now()->format('Y/m/d'));
        // セルに指定した値挿入 B22 
        $worksheet->setCellValue('B22', '2023年10月分　顧問料金xx');

        // Excel出力
        $writer = new XlsxWriter($spreadsheet);
        $excel_path = 'app/public/invoice/xls/'. $foloder_name. '/'. $file_name. '.xlsx';
        $export_excel_path = storage_path($excel_path);
        $writer->save($export_excel_path);

        // Pdf出力
        if (file_exists($export_excel_path)) {
            $pdf_path = 'app/public/invoice/pdf/'. $foloder_name;
            // $export_pdf_path = storage_path('app/pdf/export');
            $export_pdf_path = storage_path($pdf_path);
        // Log::debug('ExportService makePdf export_pdf_path  = ' . $export_pdf_path);
            $cmd = 'export HOME=/tmp; libreoffice --headless --convert-to pdf --outdir ' . $export_pdf_path . ' ' . $export_excel_path;

            Log::info('ExportService makePdf END');

            exec($cmd);
        }
        // if (file_exists($export_excel_path)) {
        //     $export_pdf_path = storage_path('app/pdf/export');
        //     $cmd = 'export HOME=/tmp; libreoffice --headless --convert-to pdf --outdir ' . $export_pdf_path . ' ' . $export_excel_path;
        //     exec($cmd);
        // }
    }
}
