<?php

namespace App\Repositories;

use App\Models\Referral;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class CartRepository
 * @package App\Repositories
 * @version September 4, 2019, 3:38 pm UTC
 *
 * @method Cart findWithoutFail($id, $columns = ['*'])
 * @method Cart find($id, $columns = ['*'])
 * @method Cart first($columns = ['*'])
*/
class ReferralRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'user_id',
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Referral::class;
    }
}
