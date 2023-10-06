<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

use App\Services\ExportService as ExportService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExcelMakeController extends Controller
{
    //
	public function excel()
	{
        Log::info('ExcelMakeController excel START');

		// $exceloutfilepath = storage_path().'/app/public/invoice/xls/folder0002/20230201_from_to_請求書.xlsx';
        // $exceltmpfilepath = storage_path().'/app/public/invoice/xls/tmp/tmp_invoice.xlsx';
		// $excel_file = storage_path($exceltmpfilepath);
		// Excel::load($excel_file, function($reader) {
		// 	// 1番目のシートを選択
		// 	$reader->sheet(0, function($sheet) {
		// 		// セルM!に現在の日付を書き込み
		// 		$sheet->cell('M1', function($cell) {
		// 		    $cell->setValue( now()->format('Y/m/d') );
		// 		});		    
		// 	});		    
		// })->export('xlsx');
		// // })->export($exceloutfilepath);

        $to_name = 'com0002';
        $foloder_name = 'folder0002';
        $this->pdfmake($to_name,$foloder_name);            

        Log::info('ExcelMakeController excel END');

    }

    public function pdfmake($to_name,$foloder_name)
    {
        Log::info('ExcelMakeController pdfmake START');

        $file_name = now()->format('Ymd') .'_from_'.$to_name.'請求書';
        $pdf_path = 'public/invoice/pdf/'. $foloder_name. '/';

        $export_service = new ExportService();
        $export_service->makePdf($file_name,$foloder_name);

        // $file_path = Storage::path('pdf/export/' . $file_name . '.pdf');
        $pdf_path = 'app/public/invoice/pdf/'. $foloder_name. '/';
        // $export_pdf_path = storage_path('app/pdf/export');
        $file_path = storage_path($pdf_path . $file_name . '.pdf');

        // $file_path = Storage::path($pdf_path . $file_name . '.pdf');

        Log::debug('ExcelMakeController pdfmake file_path  = ' . $file_path);


        $headers = ['Content-Type' => 'application/pdf'];

        Log::info('ExcelMakeController pdfmake END');
        return response()->download($file_path, $file_name . '.pdf', $headers);
    }
}
