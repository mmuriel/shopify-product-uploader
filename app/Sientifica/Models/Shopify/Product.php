<?php

namespace Sientifica\Models\Shopify;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $table = 'products';


    public function variants(){

    	return $this->hasMany('Sientifica\Models\Shopify\Variant','idproduct');

    }

    public function images(){

    	return $this->hasMany('Sientifica\Models\Shopify\ProductImage','idproducto');

    }
}
