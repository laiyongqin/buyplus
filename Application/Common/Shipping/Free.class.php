<?php

namespace Common\Shipping;

use Common\Interfaces\I_Shipping;

/**
 * 免运费
 */
class Free implements I_Shipping
{
    private $title = '免运费';
	
    public function title()
    {
        return $this->title;
    }
    public function key()
    {
        return basename(__CLASS__);
    }
    public function price()
    {
        return 0.0;
    }
}
