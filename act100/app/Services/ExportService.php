<?php

namespace App\Services;

use App\Models\Billdata;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
// use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Facades\Excel;

// use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;

class ExportService
{
    protected $proxies = "*";

    /**
     *    makeXlsPdf()    : Excelを作成しPDFに変換
     *    $nowyear        : 年
     *    $tourokuno      : 登録番号
     *    $tekiyou        : 摘要名
     *    $furibi         : 振込日
     *    $from_company   : 送り主会社名
     *    $from_repres    : 送り主代表者名
     *    $tanka          : 単価
     *    $to_company     : 宛先会社名
     *    $to_represent   : 宛先代表者名
     *    $foloder_name   : フォルダー名
     *    $file_name      : ファイル名
     *    $customers_id   : 顧客ID
     */
    public function makeXlsPdf(
                        $nowyear,
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
                        $file_name,
                        $customers_id
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
        $xls_path        = 'app/public/invoice/xls/'. $foloder_name. '/'. $file_name. '.xlsx';
        $export_xls_path = storage_path($xls_path);         //'app/invoice'
        $writer          = new XlsxWriter($spreadsheet);
        $writer->save($export_xls_path);

        // Pdf出力
        if (file_exists($export_xls_path)) {

            // ファイルサイズを取得する
            $filesize = filesize($export_xls_path); // ファイルサイズをバイト単位で取得
            // Log::debug('ExportService makeXlsPdf xls $filesize = ' . $filesize);

            // billdatas更新
            $extension_flg      = 1;
            $extension_filename = $file_name . '.xlsx';
            $ins_path = 'public/invoice/xls/'. $foloder_name. '/'. $file_name. '.xlsx';
            $this->billdataUpdate($nowyear, $ins_path, $extension_filename, $customers_id, $filesize, $extension_flg);

            // ExcelファイルをPDFに変換するコード
            $pdf_path = $this->convertOfficeToPdf($file_name, $foloder_name, $export_xls_path);
            if (file_exists($pdf_path)) {
                // ファイルサイズを取得する
                $filesize = filesize($pdf_path); // ファイルサイズをバイト単位で取得
                // Log::debug('ExportService makeXlsPdf pdf $filesize = ' . $filesize);
                // billdatas更新
                $extension_flg      = 2;
                $extension_filename = $file_name . '.pdf';
                $ins_path = 'public/invoice/pdf/'. $foloder_name. '/'. $file_name. '.pdf';
                $this->billdataUpdate($nowyear, $ins_path, $extension_filename, $customers_id, $filesize, $extension_flg);
            }
        }

        Log::info('ExportService makeXlsPdf END');
        return;
    }

    // Local commandではうまく行くが実行すると出来ない SV=OK
    /**
     *    convertOfficeToPdf() : ExcelをPDFに変換
     *    $file_name           : ファイル名
     *    $foloder_name        : フォルダー名
     *    $office_path         : Excelフルパス
     */
    public function convertOfficeToPdf($file_name,$foloder_name, $office_path)
    {
        Log::info('ExportService convertOfficeToPdf START');

        // putenv('HOME=/tmp'); // libreoffice の作業スペースとして tmp を使う

        $pdf_dir = storage_path('app/public/invoice/pdf/'.  $foloder_name);
        // Log::debug('ExportService convertOfficeToPdf exec $pdf_dir = '    .print_r($pdf_dir,true));
        // Log::debug('ExportService convertOfficeToPdf exec $foloder_name = '    .print_r($foloder_name,true));
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

        // Log::debug('ExportService convertOfficeToPdf exec $office_path = '    .print_r($office_path,true));
        Log::info('ExportService convertOfficeToPdf exec $return_var = ' .print_r($return_var,true));

        // $filename = pathinfo($office_path, PATHINFO_FILENAME);
        $pdf_path = $pdf_dir . '/' . $file_name . '.pdf';

        Log::info('ExportService convertOfficeToPdf END');

        return file_exists($pdf_path) ? $pdf_path : null;
    }

    public function billdataUpdate($nowyear, $filepath, $fileName, $customers_id, $filesize, $extension_flg)
    {
        Log::info('ExportService  billdataUpdate START');

        try {
            DB::beginTransaction();
            Log::info('beginTransaction - ExportService  billdataUpdate saveFile start');

            $billdata = new Billdata();
            $billdata->year            = $nowyear;
            $billdata->filepath        = $filepath;
            $billdata->filename        = $fileName;
            $billdata->organization_id = 1;
            $billdata->extension_flg   = $extension_flg;
            $billdata->customer_id     = $customers_id;
            $billdata->filesize        = $filesize;
            $billdata->urgent_flg      = 2;  // 1:既読 2:未読
            $billdata->save();               //  Inserts

            DB::commit();
            Log::info('beginTransaction - ExportService  billdataUpdate saveFile end(commit)');
        }
        catch(\QueryException $e) {
            Log::error('exception : ' . $e->getMessage());
            DB::rollback();
            Log::info('beginTransaction - ExportService  billdataUpdate saveFile end(rollback)');
            $errormsg = '更新出来ませんでした。';
            return \Response::json(['error'=>$errormsg,'status'=>'NG'], 400);
        }

        Log::info('ExportService  billdataUpdate END');

        return \Response::json(['error'=>'更新処理が正常に終了しました。','status'=>'OK'], 200);

    }

}
