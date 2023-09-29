<?php

namespace App\Services;

use App\Repositories\MailAttachmentRepositoryInterface as MailAttachmentRepository;
use Illuminate\Support\Facades\Log;

class MailAttachmentService
{
    /**
     * @var MailAttachmentRepositoryInterface
     */
    private $mailAttachmentRepository;

    public function __construct(MailAttachmentRepository $mailAttachmentRepository)
    {
        $this->mailAttachmentRepository = $mailAttachmentRepository;
    }

    /**
     * メール添付ファイルのアップロード
     *
     * @param [type] $file
     * @return void
     */
    public function saveFile($postData)
    {
        // Log::debug('MailAttachmentService saveFile $postData  = ' . print_r($postData ,true));

        $filePath         = storage_path('app/public/mail_attachments/');
        $organization_id  = $postData['organization_id'];
        $individual_class = $postData['individual_mail'];
        $user_id          = $postData['user_id'];
        $image            = $postData['file'];
        $fileName         = $image->getClientOriginalName();         // FileName
        $filesize         = $image->getSize();                       // FileSize
        $image->move($filePath,$fileName);                           // Strage Move

        // $filePath        = Storage::putFile('/mail_attachments', $fileName);

        // Log::debug('MailAttachmentService saveFile $organization_id  = ' . print_r($organization_id ,true));
        // Log::debug('MailAttachmentService saveFile $user_id          = ' . print_r($user_id ,true));
        // Log::debug('MailAttachmentService saveFile $filePath         = ' . print_r($filePath ,true));
        // Log::debug('MailAttachmentService saveFile $fileName         = ' . print_r($fileName ,true));
        Log::debug('MailAttachmentService saveFile $filesize         = ' . print_r($filesize ,true));

        $putFileInfo = [
            'organization_id'  => $organization_id,
            'user_id'          => $user_id,
            'fileName'         => $fileName,
            'filePath'         => $filePath,
            'filesize'         => $filesize,
            'individual_class' => $individual_class,
        ];

        return $this->mailAttachmentRepository->saveFile($putFileInfo);
    }
}