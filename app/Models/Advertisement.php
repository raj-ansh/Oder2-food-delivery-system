<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Faq
 * @package App\Models
 * @version August 29, 2019, 9:39 pm UTC
 *
 * @property \App\Models\FaqCategory faqCategory
 * @property string question
 * @property string answer
 * @property integer faq_category_id
 */
class Advertisement extends Model
{

    public $table = 'advertisement';
    


    public $fillable = [
        'name',
        'radius',
        'per_day_price',
        'service_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'radius' => 'integer',
        'per_day_price' => 'integer',
        'service_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required',
        'radius' => 'required',
        'per_day_price' => 'required',
        'service_id' => 'required|exists:services,id',
    ];

    /**
     * New Attributes
     *
     * @var array
     */
    protected $appends = [
        'custom_fields',
        
    ];

    public function customFieldsValues()
    {
        return $this->morphMany('App\Models\CustomFieldValue', 'customizable');
    }

    public function getCustomFieldsAttribute()
    {
        $hasCustomField = in_array(static::class,setting('custom_field_models',[]));
        if (!$hasCustomField){
            return [];
        }
        $array = $this->customFieldsValues()
            ->join('custom_fields','custom_fields.id','=','custom_field_values.custom_field_id')
            ->where('custom_fields.in_table','=',true)
            ->get()->toArray();

        return convertToAssoc($array,'name');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function services()
    {
        return $this->belongsTo(\App\Models\Services::class, 'service_id', 'id');
    }
    
}
