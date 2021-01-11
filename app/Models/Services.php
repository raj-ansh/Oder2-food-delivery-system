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
class Services extends Model
{

    public $table = 'services';
    


    public $fillable = [
        'name',
        'price',
        'insurance_id',
        'agent_commission',
        'admin_commission'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'price' => 'integer',
        'insurance_id' => 'integer',
        'agent_commission' => 'string',
        'admin_commission' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required',
        'price' => 'required',
        'insurance_id' => 'required|exists:insurance,id',
        'agent_commission' => 'required',
        'admin_commission' => 'required',
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
    public function insurance()
    {
        return $this->belongsTo(\App\Models\Insurance::class, 'insurance_id', 'id');
    }
    
}
