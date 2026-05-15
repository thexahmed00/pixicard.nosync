<?php

namespace Pixi\Likecard\Models;

use Webkul\Category\Models\Category as BaseCategory;

class Category extends BaseCategory
{
      protected $fillable = [
        'position',
        'status',
        'display_mode',
        'parent_id',
        'additional',
        'card_lc_category_id', 
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        
    }

}
