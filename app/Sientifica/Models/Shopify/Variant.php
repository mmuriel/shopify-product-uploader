<?php

namespace Sientifica\Models\Shopify;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    //
    protected $table = 'variants';

    public function product(){

    	return $this->belongsTo('Sientifica\Models\Shopify\Product','idproduct');

    }
}
