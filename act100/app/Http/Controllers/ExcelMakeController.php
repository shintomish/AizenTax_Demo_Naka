<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\ExportService as ExportService;
use Illuminate\Support\Facades\Log;

class ExcelMakeController extends Controller
{
    //
	public function excel()
	{
        Log::info('ExcelMakeController excel START');

        $to_name        = 'company-0002';
        $from_name      = 'global';
        $foloder_name   = 'folder0002';
        $file_name      = now()->format('Ymd') .'_'. $from_name. '_'. $to_name. '_請求書';

        // App\Services\ExportService
        $export_service = new ExportService();
        $export_service->makePdf($to_name, $file_name, $foloder_name);
 
        Log::info('ExcelMakeController excel END');

        return redirect()->route('advisorsfee.input');

    }

}
