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

    // $export_service->makeXlsPdf($data['to_company'], $data['to_represent'] , $data['foloder_name'], $data['file_name']);
    public function makeXlsPdf(
                        $tourokuno,
                        $tekiyou,
                        $furibi,
                        $from_company,
                        $from_repres,
                        $kanrino,
                        $tanka,
                        $to_company, 
                        $to_represent, 
                        $foloder_name, 
                        $file_name
                        )
    {
        Log::info('ExportService makeXlsPdf START');

        // もとになるExcelを読み込み
        $excel_file = storage_path('app/public/invoice/xls/tmp/tmp_invoice.xlsx');
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($excel_file);

        // 編集するシート名を指定
        $worksheet = $spreadsheet->getSheetByName('invoice');

        // セルに指定した値挿入 M1 請求日
        $worksheet->setCellValue('M1', now()->format('Y/m/d'));
        // セルに指定した値挿入 M2 登録番号
        $worksheet->setCellValue('M2', $tourokuno);
        // セルに指定した値挿入 K14 請求書宛名会社名
        $worksheet->setCellValue('K14', $from_company);
        // セルに指定した値挿入 K15 請求書宛名代表者名
        $worksheet->setCellValue('K15', $from_repres);
        // セルに指定した値挿入 K19 管理番号
        $worksheet->setCellValue('K19', $kanrino);
        // セルに指定した値挿入 K22 単価
        $worksheet->setCellValue('K22', $tanka);
        // セルに指定した値挿入 B22 適用欄
        $worksheet->setCellValue('B22', $tekiyou);
        // セルに指定した値挿入 C5 会社名 
        $worksheet->setCellValue('C5', $to_company);
        // セルに指定した値挿入 C6 代表者名
        $worksheet->setCellValue('C6', $to_represent. ' 様');
        // セルに指定した値挿入 E55 ※大変恐縮ではございますが、　2023年4月15日　までにお振込みください
        $worksheet->setCellValue('E55', $furibi);

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

        // Pdf出力
        if (file_exists($export_xls_path)) {

            // Log::debug('ExportService makeXlsPdf $export_xls_path = ' . $export_xls_path);

            // ExcelファイルをPDFに変換するコード
            $this->convertOfficeToPdf($file_name, $foloder_name, $export_xls_path);

        }

        Log::info('ExportService makeXlsPdf END');
        return;
    }

    // Local commandではうまく行くが実行すると出来ない SV=OK
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

        // Log::debug('ExportService convertOfficeToPdf exec $command = '    .print_r($command,true));
        Log::debug('ExportService convertOfficeToPdf exec $return_var = ' .print_r($return_var,true));

        // $filename = pathinfo($office_path, PATHINFO_FILENAME);
        $pdf_path = $pdf_dir . '/' . $file_name . '.pdf';

        Log::info('ExportService convertOfficeToPdf END');

        return file_exists($pdf_path) ? $pdf_path : null;
    }
}
