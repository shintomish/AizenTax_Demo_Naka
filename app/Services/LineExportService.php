<?php

namespace App\Services;

use App\Models\Line_Trial_Users_History;
use App\Models\Line_Trial_Users;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LineExportService
{
    protected $proxies = "*";

    /**
     *    LinemakeXlsPdf() : Excelを作成しPDFに変換
     *    $nowyear         : 年
     *    $nowmonth        : 月
     *    $user_id         : ユーザーID
     *    $user_name       : 体験者名
     *    $kanrino         : 管理番号 No
     *    $file_name       : ファイル名
     */
    public function LinemakeXlsPdf(
                        $nowyear,
                        $nowmonth,
                        $user_id,
                        $user_name,
                        $kanrino,
                        $file_name,
                    )
    {
        Log::info('ExportService LinemakeXlsPdf START');

        // もとになるExcelを読み込み
        $excel_file = storage_path('app/public/line/xls/tmp/tmp_line.xlsx');
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($excel_file);

        // 編集するシート名を指定
        $worksheet = $spreadsheet->getSheetByName('Receipt');

        // セルに指定した値挿入 J8 管理番号 R-231112-xx
        $worksheet->setCellValue('J8', $kanrino);
        // セルに指定した値挿入 J9 請求日R-231112-xx
        $worksheet->setCellValue('J9', now()->format('Y/m/d'));
        // セルに指定した値挿入 A11 体験者名
        $worksheet->setCellValue('A11', $user_name);

        // Dirなければ作成
        // if(!file_exists( storage_path('app/public/line/xls/'. $foloder_name))){
        //     mkdir( storage_path('app/public/line/xls/'. $foloder_name), $mode = 0777, true);
        // }
        // if(!file_exists( storage_path('app/public/line/pdf/'. $foloder_name))){
        //     mkdir( storage_path('app/public/line/pdf/'. $foloder_name), $mode = 0777, true);
        // }

        // Excel出力
        $xls_path        = 'app/public/line/xls/'. $file_name. '.xlsx';
        $export_xls_path = storage_path($xls_path);         //'app/invoice'
        $writer          = new XlsxWriter($spreadsheet);
        $writer->save($export_xls_path);

        // Pdf出力
        if (file_exists($export_xls_path)) {

            // ファイルサイズを取得する
            $filesize = filesize($export_xls_path); // ファイルサイズをバイト単位で取得
            // Log::debug('ExportService LinemakeXlsPdf xls $filesize = ' . $filesize);

            // billdatas更新
            $extension_flg      = 1;
            $extension_filename = $file_name . '.xlsx';
            $ins_path = 'public/line/xls/'. $file_name. '.xlsx';
            $this->line_trial_users_history_Update($nowyear, $nowmonth, $ins_path, $extension_filename, $user_name, $user_id, $filesize, $extension_flg);

            // ExcelファイルをPDFに変換するコード
            $pdf_path = $this->convertOfficeToPdf($file_name, $export_xls_path);
            if (file_exists($pdf_path)) {
                // ファイルサイズを取得する
                $filesize = filesize($pdf_path); // ファイルサイズをバイト単位で取得
                // Log::debug('ExportService LinemakeXlsPdf pdf $filesize = ' . $filesize);
                // billdatas更新
                $extension_flg      = 2;
                $extension_filename = $file_name . '.pdf';
                $ins_path = 'public/line/pdf/'. $file_name. '.pdf';
                $this->line_trial_users_history_Update($nowyear, $nowmonth, $ins_path, $extension_filename, $user_name, $user_id, $filesize, $extension_flg);
            }
        }

        // * line_trial_usersテーブルの更新
        $this->line_trial_users_Update($user_id);

        Log::info('ExportService LinemakeXlsPdf END');
        return;
    }

    // Local commandではうまく行くが実行すると出来ない SV=OK
    /**
     *    convertOfficeToPdf() : ExcelをPDFに変換
     *    $file_name           : ファイル名
     *    $office_path         : Excelフルパス
     */
    public function convertOfficeToPdf($file_name, $office_path)
    {
        Log::info('ExportService convertOfficeToPdf START');

        // putenv('HOME=/tmp'); // libreoffice の作業スペースとして tmp を使う

        $pdf_dir = storage_path('app/public/line/pdf');
        // Log::debug('ExportService convertOfficeToPdf exec $pdf_dir = '    .print_r($pdf_dir,true));
        // Log::debug('ExportService convertOfficeToPdf exec $foloder_name = '    .print_r($foloder_name,true));
        // $office_path = './storage/app/public/line/xls/'. $foloder_name. '/'. $file_name. '.xlsx';

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

    public function line_trial_users_history_Update($nowyear, $nowmonth, $filepath, $fileName, $user_name, $user_id, $filesize, $extension_flg)
    {
        Log::info('ExportService  line_trial_users_history_Update START');

        try {
            DB::beginTransaction();
            Log::info('beginTransaction - ExportService  line_trial_users_history_Update saveFile start');

            $line_trial_users_history = new Line_Trial_Users_History();
            $line_trial_users_history->year            = $nowyear;
            $line_trial_users_history->mon             = $nowmonth;
            $line_trial_users_history->filepath        = $filepath;
            $line_trial_users_history->filename        = $fileName;
            $line_trial_users_history->organization_id = 1;
            $line_trial_users_history->user_name       = $user_name;
            $line_trial_users_history->user_id         = $user_id;
            $line_trial_users_history->extension_flg   = $extension_flg;
            $line_trial_users_history->filesize        = $filesize;
            $line_trial_users_history->urgent_flg      = 2;  // 1:既読 2:未読
            $line_trial_users_history->save();               //  Inserts

            DB::commit();
            Log::info('beginTransaction - ExportService  line_trial_users_history_Update saveFile end(commit)');
        }
        catch(\QueryException $e) {
            Log::error('exception : ' . $e->getMessage());
            DB::rollback();
            Log::info('beginTransaction - ExportService  line_trial_users_history_Update saveFile end(rollback)');
            $errormsg = '更新出来ませんでした。';
            return \Response::json(['error'=>$errormsg,'status'=>'NG'], 400);
        }

        Log::info('ExportService  line_trial_users_history_Update END');

        return \Response::json(['error'=>'更新処理が正常に終了しました。','status'=>'OK'], 200);

    }

    /**
     * line_trial_usersテーブルの更新
     */
    public function line_trial_users_Update($id)
    {
        Log::info('line_trial_users_Update START');

        $counts = array();
        $update = [];
        $update['urgent_flg']       = 2;  // 作成フラグ(1):未作成 (2):作成済
        $update['updated_at']       = date('Y-m-d H:i:s');

        $status = array();
        DB::beginTransaction();
        Log::info('line_trial_users_Update beginTransaction - start');
        try{
            // 更新処理
            Line_Trial_Users::where( 'id', $id )->update($update);

            $status = array( 'error_code' => 0, 'message'  => 'Your data has been changed!' );
            $counts = 1;
            DB::commit();
            Log::info('line_trial_users_Update beginTransaction - end');
        }
        catch(Exception $e){
            Log::error('line_trial_users_Update exception : ' . $e->getMessage());
            DB::rollback();
            Log::info('line_trial_users_Update beginTransaction - end(rollback)');
            echo "エラー：" . $e->getMessage();
            $counts = 0;
            $status = array( 'error_code' => 501, 'message'  => $e->getMessage() );
        }

        Log::info('line_trial_users_Update END');
        return response()->json([ compact('status','counts') ]);

    }

}
