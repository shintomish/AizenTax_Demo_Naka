<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportService
{
    protected $proxies = "*";

    public function makePdf($to_name, $file_name, $foloder_name)
    {
        Log::info('ExportService makePdf START');

        // もとになるExcelを読み込み
        $excel_file = storage_path('app/public/invoice/xls/tmp/tmp_invoice.xlsx');
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($excel_file);

        // 編集するシート名を指定
        $worksheet = $spreadsheet->getSheetByName('invoice');

        // セルに指定した値挿入 M1 請求日
        $worksheet->setCellValue('M1', now()->format('Y/m/d'));
        // セルに指定した値挿入 B22 適用欄
        $worksheet->setCellValue('B22', '2023年10月分　顧問料金');

        // セルに指定した値挿入 C5 会社名 
        $worksheet->setCellValue('C5', $to_name);

        // セルに指定した値挿入 C6 氏名
        $c6 = $to_name. '氏名'; 
        $worksheet->setCellValue('C6', $c6. ' 様');

        // Dirなければ作成
        if(!file_exists( storage_path('app/public/invoice/xls/'. $foloder_name))){
            mkdir( storage_path('app/public/invoice/xls/'. $foloder_name), $mode = 0777, true);
        }
        if(!file_exists( storage_path('app/public/invoice/pdf/'. $foloder_name))){
            mkdir( storage_path('app/public/invoice/pdf/'. $foloder_name), $mode = 0777, true);
        }

        // Excel出力
        $xls_path = 'app/public/invoice/xls/'. $foloder_name. '/'. $file_name. '.xlsx';
        $export_xls_path = storage_path($xls_path);         //'app/invoice'
        $writer     = new XlsxWriter($spreadsheet);
        $writer->save($export_xls_path);

        // save後に上書き 属性 r->w
        // $this->resave($writer, $export_xls_path);

        // Pdf出力
        if (file_exists($export_xls_path)) {

            Log::debug('ExportService makePdf $export_xls_path = ' . $export_xls_path);

            // ExcelファイルをPDFに変換するコード
            $this->convertOfficeToPdf($file_name, $foloder_name, $export_xls_path);

            // $pdf_path = 'app/public/invoice/xls/'. $foloder_name. '/'. $file_name. '.pdf';
            // $export_pdf_path = storage_path($pdf_path);         //'app/invoice'
            // $this->generate_pdf($export_pdf_path, $export_xls_path);

        }

        Log::info('ExportService makePdf END');
        return;
    }

    // save後に上書き 属性 r->w
    public function resave($writer,$export_xls_path)
    {
        Log::info('ExportService makePdf resave START');
        Log::info('ExportService makePdf resave END');

        // return Excel::download(new AddressExport, 'output.xlsx');
        // Excel::store(new AddressExport, 'output.xlsx', 'local');
        return Excel::store($writer, $export_xls_path);
    }

    public function changeDompdf($export_pdf_path)
    {

        // DOMPDF
        // return (new AddressExport)->download('output.pdf', \Maatwebsite\Excel\Excel::DOMPDF);

    }

    // // ExcelファイルをPDFに変換するコード
    public function generate_pdf($export_pdf_path, $export_xls_path) {

        // Log::info('ExportService generate_pdf START');

        // $pdf = \PDF::loadFile($export_xls_path,\Maatwebsite\Excel\Excel::DOMPDF);
        // $pdf->save($export_pdf_path);

        // Log::info('ExportService generate_pdf END');
        // return;

    }

    // commandではうまく行くが実行すると出来ない
    public function convertOfficeToPdf($file_name,$foloder_name, $office_path)
    {
        Log::info('ExportService convertOfficeToPdf START');

        // putenv('HOME=/tmp'); // libreoffice の作業スペースとして tmp を使う

        $pdf_dir = storage_path('app/public/invoice/pdf/'.  $foloder_name);
        // $office_path = './storage/app/public/invoice/xls/'. $foloder_name. '/'. $file_name. '.xlsx';

        #  /usr/bin/soffice --headless --convert-to pdf --outdir {出力先のディレクトリ} {変換する元のExcel}
        #  例) /tmp/sample.xls -> /tmp/sample.pdfに変換する場合
        #  /usr/bin/soffice --headless --convert-to pdf --outdir /tmp /tmp/sample.xls
        #  exec("export LANG=ja_JP.UTF-8 && /usr/bin/soffice --headless --convert-to pdf --outdir /tmp /tmp/sample.xls");

        // $command_parts = [
        //     'export HOME=/tmp;',
        //     '/usr/bin/soffice',
        //     '--language=ja',
        //     '--headless',
        //     '--convert-to pdf:writer_pdf_Export',
        //     '--outdir '. $pdf_dir,
        //     $office_path
        // ];
        $command_parts = [
            'HOME=/tmp;',
            '/usr/bin/soffice',
            '--headless',
            '--language=ja',
            '--convert-to pdf',
            '--outdir '. $pdf_dir,
            $office_path
        ];
        $command = implode(' ', $command_parts);
        exec($command, $output, $return_var);

        // OK Exec
        // /usr/bin/soffice --language=ja --headless --convert-to pdf:writer_pdf_Export 
        // --outdir ./storage/app/public/invoice/pdf/folder0002 
        // ./storage/app/public/invoice/xls/folder0002/20231007_global_company-0002_請求書.xlsx

        Log::debug('ExportService convertOfficeToPdf exec $command = '    .print_r($command,true));
        // Log::debug('ExportService convertOfficeToPdf exec $output = '     .print_r($output,true));
        Log::debug('ExportService convertOfficeToPdf exec $return_var = ' .print_r($return_var,true));

        // $filename = pathinfo($office_path, PATHINFO_FILENAME);
        $pdf_path = $pdf_dir . '/' . $file_name . '.pdf';

        Log::info('ExportService convertOfficeToPdf END');

        return file_exists($pdf_path) ? $pdf_path : null;
    }
}
