<?php

namespace Pixi\Likecard\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CardLcCategory extends Model
{
    use HasFactory;

    // ... (rest of your model code, including fillable, casts, relationships) ...
    protected $table = 'card_lc_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'categoryParentId',
        'categoryName',
        'amazonImage',
        'description',
        'redeemSteps',
        'TANDC',
        'main_cat',
        'sort_order',
        'name_ar',
        'name_fr',
        'name_ur'
    ];

    /**
     * The attributes that should be cast.  Important for correct data handling.
     *
     * @var array
     */
    protected $casts = [
        'categoryParentId' => 'integer', // Cast to integer
        'amazonImage' => 'string',      
        'description'     => 'string',
        'redeemSteps'     => 'string',
        'TANDC'            => 'string'
    ];


    /**
     * Get the parent category.  Self-referencing relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(CardLcCategory::class, 'categoryParentId', 'id');
    }


    /**
     * Get the child categories. Self-referencing relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function childCategories(): HasMany
    {
        return $this->hasMany(CardLcCategory::class, 'categoryParentId', 'id');
    }

    /**
     * Get all descendant categories (children, grandchildren, etc.).
     *  Recursive relationship.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allChildCategories()
    {
        return $this->childCategories()->with('allChildCategories');
        //This eager loads all children and their children recursively
    }


    public function products()
    {
        return $this->hasMany(CardLcProduct::class, 'categoryId', 'id');
    }

    /**
     * Get only parent categories.
     */
    public static function getParentCategories()
    {
        return self::whereNull('categoryParentId')->get();
    }

    /**
     * Gets Parent categories in flatten array using map.
     * @return Collection
     */
    public static function getParentCategoriesFlattened(): Collection
    {

        return self::getParentCategories()->map(function ($category) {
            return $category->toSimpleArray();
        });
    }

    /**
     * Fetches categories from the 'card_lc_categories' table with a consistent set of columns.
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public static function get_filtered_categories($id = "", $name = "", $parent_id = "", $main_cat_id=null, $status = true, int $page = 1, int $count = 20)
    {
        return DB::table('card_lc_categories')
                ->select(
                    'id',
                    'categoryName AS name',
                    'amazonImage AS image',
                    'categoryParentId AS parent_id',
                    'mc.id AS main_cat_id',
                    'mc.name AS main_cat',
                    'sort_order',
                    DB::raw('(SELECT COUNT(*) FROM card_lc_categories c WHERE c.categoryParentId = card_lc_categories.id AND c.status = TRUE) AS child_count'),
                    DB::raw('(SELECT CASE WHEN EXISTS (SELECT true FROM card_lc_categories c WHERE c.categoryParentId = card_lc_categories.id AND c.status = TRUE) THEN TRUE ELSE FALSE END) AS has_child'),
                    DB::raw('(SELECT JSON_ARRAYAGG(JSON_OBJECT(
                        "id", c.id,
                        "name", c.categoryName,
                        "image", c.amazonImage,
                        "parent_id", c.categoryParentId,
                        "sort_order", c.sort_order,
                        "child_count", CAST((SELECT COUNT(*) FROM card_lc_categories cc WHERE cc.categoryParentId = c.id AND cc.status = TRUE) AS SIGNED),
                        "has_child", (SELECT CASE WHEN EXISTS 
                            (SELECT true FROM card_lc_categories cc WHERE cc.categoryParentId = c.id AND cc.status = TRUE) 
                            THEN TRUE ELSE FALSE END)
                    ) ORDER BY COALESCE(c.sort_order, 99999) ASC, c.categoryName ASC) 
                    FROM card_lc_categories c 
                    WHERE c.categoryParentId = card_lc_categories.id 
                    AND c.status = TRUE 
                    AND card_lc_categories.status = TRUE) AS child')
                )
                ->leftJoin('main_categories AS mc', 'card_lc_categories.main_cat', '=', 'mc.id')
                ->when($id, function ($query) use ($id) {
                    return $query->where('id', $id);
                })
                ->when($name, function ($query) use ($name) {
                    return $query->where('categoryName', 'like', "%$name%");
                })
                ->when($parent_id, function ($query) use ($parent_id) {
                    return $query->where('categoryParentId', $parent_id);
                })
                // ->when(isset($parent_id), function ($query) use ($parent_id) {
                //     return $query->where('categoryParentId', $parent_id);
                // }, function ($query) {
                //     return $query->where('categoryParentId', 0);
                // })
                ->when($main_cat_id, function ($query) use ($main_cat_id) {
                    return $query->where('mc.id', $main_cat_id);
                })
                ->when($status, function ($query) use ($status) {
                    return $query->where('status', $status);
                })
                ->orderByRaw("COALESCE(sort_order, 99999) ASC")
                ->orderBy('categoryName')
                ->paginate(perPage: $count, page: $page)
                ->through(function ($category) {
                    $category->has_child = (bool) $category->has_child; 
                    $category->child = json_decode($category->child, true);
                    
                    return $category;
                });

    }

    public static function get_parent_categories($parent_id=null, $main_cat_id=null, bool $show_all=false, int $page = 1, int $count = 20)
    {
        $category_name = DB::raw("card_lc_categories.categoryName AS name");

        if (Auth::user()->lang && Auth::user()->lang != "en") {
            $category_name = DB::raw("COALESCE(card_lc_categories.name_" . Auth::user()->lang . ", card_lc_categories.categoryName) AS name");
        }
        return DB::table('card_lc_categories')
            ->select(
                'card_lc_categories.id',
                $category_name,
                'card_lc_categories.amazonImage AS image',
                'card_lc_categories.categoryParentId AS parent_id',
                DB::raw('(SELECT COUNT(*) FROM card_lc_categories c WHERE c.categoryParentId = card_lc_categories.id AND c.status = TRUE) AS child_count'),
                DB::raw('(SELECT CASE WHEN EXISTS (SELECT true FROM card_lc_categories c WHERE c.categoryParentId = card_lc_categories.id AND c.status = TRUE) THEN TRUE ELSE FALSE END) AS has_child'),
                'mc.id AS main_cat_id',
                'mc.name AS main_cat',
                'card_lc_categories.sort_order',
            )
            ->leftJoin('main_categories AS mc', 'card_lc_categories.main_cat', '=', 'mc.id')
            ->where('card_lc_categories.status', 1)
            ->when(!$main_cat_id && !$show_all, function ($query) use ($parent_id) {
                return $query->when($parent_id, function ($query) use ($parent_id) {
                    return $query->where('card_lc_categories.categoryParentId', $parent_id);
                }, function ($query) use ($parent_id) {
                    return $query->where('card_lc_categories.categoryParentId', 0);
                });
            })
            ->when($main_cat_id, function ($query) use ($main_cat_id) {
                return $query->where('mc.id', $main_cat_id);
            })
            ->orderByRaw("COALESCE(card_lc_categories.sort_order, 99999) ASC")
            ->orderBy('categoryName')
            ->paginate(perPage: $count, page: $page)
            ->through(function ($category) {
                $category->child_count = (int) $category->child_count;
                $category->has_child = (bool) $category->has_child;
                return $category;
            });
            
    }

    // public function mainCategory()
    // {
    //     return $this->belongsTo(MainCategories::class, 'main_cat', 'id');
    // }

    public function children()
    {
        return $this->hasMany(CardLcCategory::class, 'categoryParentId', 'id')
        ->where(function ($query) {
            $query->where('status', 1)
                  ->orWhereNotNull('main_cat');
        })
        ->with('children'); 
    }
}
