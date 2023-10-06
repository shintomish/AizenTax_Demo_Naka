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
            Log::info('ExportService makePdf Pdf出力 START');

            // $pdf_path = 'app/public/invoice/pdf/'. $foloder_name. '/';
            // $export_pdf_path = storage_path($pdf_path);
            // $cmd = 'export HOME=/tmp; libreoffice --headless --convert-to pdf --outdir ' . $export_pdf_path . ' ' . $export_excel_path;
            // exec($cmd);

            // Excel -> Pdf
            $this->convertOfficeToPdf($file_name, $foloder_name, $export_excel_path);

            Log::info('ExportService makePdf Pdf出力 END');
        }
        Log::info('ExportService makePdf END');
        return;
    }

    public function convertOfficeToPdf($file_name, $foloder_name, $office_path)
    {
        Log::info('ExportService convertOfficeToPdf START');

        // putenv('HOME=/tmp'); // libreoffice の作業スペースとして tmp を使う

        $pdf_dir = storage_path('app/public/invoice/pdf/'. $foloder_name);
$pdf_dir = './storage/app/public/invoice/pdf/folder0002';
        # /usr/bin/soffice --headless --convert-to pdf --outdir {出力先のディレクトリ} {変換する元のExcel}
        # 例) /tmp/sample.xls -> /tmp/sample.pdfに変換する場合
        // /usr/bin/soffice --headless --convert-to pdf --outdir /tmp /tmp/sample.xls

        $office_path = '/mnt/d/010_Web開発/100_TaxDemo/act100/storage/app/public/invoice/xls/folder0002/20231007_global_com0002_請求書.xlsx';
        // exec("export LANG=ja_JP.UTF-8 && /usr/bin/soffice --headless --convert-to pdf --outdir /tmp /tmp/sample.xls");
        // $command_parts = [
        //     'HOME=/tmp',
        // LANG=ja_JP.utf8 libreoffice --language=ja
        //     'export LANG=ja_JP.UTF-8 && /usr/bin/soffice',
        //     '--headless',
        //     '--convert-to pdf',
        //     '--outdir '. $pdf_dir,
        //     $office_path
        // ];
        // $command = implode(' ', $command_parts);
        // exec($command);

        $command_parts = [
            'HOME=/tmp;',
            'libreoffice',
            '--language=ja',
            '--headless',
            '--convert-to pdf:writer_pdf_Export',
            '--outdir '. $pdf_dir,
            $office_path
        ];
        $command = implode(' ', $command_parts);
        exec($command);

        Log::debug('ExportService convertOfficeToPdf $command = ' . $command);

        $filename = pathinfo($office_path, PATHINFO_FILENAME);
        $pdf_path = $pdf_dir . '/' . $filename . '.pdf';

        // Log::debug('ExportService convertOfficeToPdf $pdf_path = ' . $pdf_path);

        Log::info('ExportService convertOfficeToPdf END');

        return file_exists($pdf_path) ? $pdf_path : null;
    }
}
