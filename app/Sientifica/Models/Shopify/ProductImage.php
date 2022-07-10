<?php

namespace Sientifica\Models\Shopify;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    //
    protected $table = 'product_images';


    public function product(){

    	return $this->belongsTo('Sientifica\Models\Shopify\Product','idproducto');

    }
}
