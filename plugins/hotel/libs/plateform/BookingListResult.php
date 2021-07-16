<?php
namespace app\plugins\hotel\libs\plateform;


class BookingListResult
{
    const CODE_SUCC = 0;
    const CODE_FAIL = 1;

    public $code = 0;
    public $message;

    private $items = [];

    public function addItem(BookingListItemModel $item){
        $this->items[] = $item;
    }

    public function getAll(){
        return $this->items;
    }
}