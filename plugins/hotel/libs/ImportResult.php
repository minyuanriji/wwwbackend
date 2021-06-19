<?php
namespace app\plugins\hotel\libs;


class ImportResult
{
    const IMPORT_SUCC = 0;
    const IMPORT_FAIL = 1;

    public $code = 0;
    public $message;
    public $finished = false;
    public $totalCount = 0;
    public $totalPages = 0;
}