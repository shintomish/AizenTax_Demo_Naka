<?php

namespace App\Repositories;

use App\Models\MailAttachment;


class MailAttachmentRepository implements MailAttachmentRepositoryInterface
{
    /**
     * @var MailAttachment
     */
    private $mailAttachment;

    public function __construct(MailAttachment $mailAttachment)
    {
        $this->mailAttachment = $mailAttachment;
    }

    /**
     * メール添付ファイル情報の保存
     *
     * @param array $putFileInfo
     * @return array $putFileInfo
     */
    public function saveFile($putFileInfo)
    {
        $fileInfo                   = $this->mailAttachment;
        $fileInfo->organization_id  = $putFileInfo['organization_id'];
        $fileInfo->user_id          = $putFileInfo['user_id'];
        $fileInfo->file_path        = $putFileInfo['filePath'];
        $fileInfo->file_name        = $putFileInfo['fileName'];
        $fileInfo->filesize         = $putFileInfo['filesize'];
        $fileInfo->individual_class = $putFileInfo['individual_class'];
        $fileInfo->save();

        return $putFileInfo;
    }
}