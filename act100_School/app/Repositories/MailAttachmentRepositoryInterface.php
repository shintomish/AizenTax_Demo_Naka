<?php

namespace App\Repositories;

interface MailAttachmentRepositoryInterface
{
    /**
     * @param file $putFileInfo
     * @return fileName|filePath
     */
    public function saveFile($putFileInfo);
}
