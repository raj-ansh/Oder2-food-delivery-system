<?php

/**
 * File name: RestaurantAPIController.php
 * Last modified: 2020.05.04 at 09:04:19
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API;

use App\Criteria\Restaurants\ActiveCriteria;
use App\Criteria\Restaurants\RestaurantsOfCuisinesCriteria;
use App\Criteria\Restaurants\NearCriteria;
use App\Criteria\Restaurants\PopularCriteria;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Giftcard;
use App\Repositories\CustomFieldRepository;
use App\Repositories\RestaurantRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class RestaurantController
 * @package App\Http\Controllers\API
 */
class RestaurantAPIController extends Controller {

    /** @var  RestaurantRepository */
    private $restaurantRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    public function __construct(RestaurantRepository $restaurantRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo) {
        parent::__construct();
        $this->restaurantRepository = $restaurantRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
    }

    /**
     * Display a listing of the Restaurant.
     * GET|HEAD /restaurants
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        try {
            $this->restaurantRepository->pushCriteria(new RequestCriteria($request));
            $this->restaurantRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->restaurantRepository->pushCriteria(new RestaurantsOfCuisinesCriteria($request));
            if ($request->has('popular')) {
                $this->restaurantRepository->pushCriteria(new PopularCriteria($request));
            } else {
                $this->restaurantRepository->pushCriteria(new NearCriteria($request));
            }
            $this->restaurantRepository->pushCriteria(new ActiveCriteria());
            $restaurants = $this->restaurantRepository->all();

            // $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->restaurantRepository->model());
            // foreach ($customFields as $key => $value) {
            //     echo $value->name;
            // }
            // $dsf = getCustomFieldsValues($customFields, $request);
            // print_r($dsf);
            // exit();
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($restaurants->toArray(), 'Restaurants retrieved successfully');
    }

    /**
     * Display the specified Restaurant.
     * GET|HEAD /restaurants/{id}
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id) {
        /** @var Restaurant $restaurant */
        if (!empty($this->restaurantRepository)) {
            try {
                $this->restaurantRepository->pushCriteria(new RequestCriteria($request));
                $this->restaurantRepository->pushCriteria(new LimitOffsetCriteria($request));
                if ($request->has(['myLon', 'myLat', 'areaLon', 'areaLat'])) {
                    $this->restaurantRepository->pushCriteria(new NearCriteria($request));
                }
            } catch (RepositoryException $e) {
                return $this->sendError($e->getMessage());
            }
            $restaurant = $this->restaurantRepository->findWithoutFail($id);
        }

        if (empty($restaurant)) {
            return $this->sendError('Restaurant not found');
        }

        return $this->sendResponse($restaurant->toArray(), 'Restaurant retrieved successfully');
    }

    /**
     * Store a newly created Restaurant in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        $input = $request->all();
        if (auth()->user()->hasRole('manager')) {
            $input['users'] = [auth()->id()];
        }
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->restaurantRepository->model());
        try {
            $restaurant = $this->restaurantRepository->create($input);
            $restaurant->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($restaurant, 'image');
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($restaurant->toArray(), __('lang.saved_successfully', ['operator' => __('lang.restaurant')]));
    }

    /**
     * Update the specified Restaurant in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request) {
        $restaurant = $this->restaurantRepository->findWithoutFail($id);

        if (empty($restaurant)) {
            return $this->sendError('Restaurant not found');
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->restaurantRepository->model());
        try {
            $restaurant = $this->restaurantRepository->update($input, $id);
            $input['users'] = isset($input['users']) ? $input['users'] : [];
            $input['drivers'] = isset($input['drivers']) ? $input['drivers'] : [];
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($restaurant, 'image');
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $restaurant->customFieldsValues()
                        ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($restaurant->toArray(), __('lang.updated_successfully', ['operator' => __('lang.restaurant')]));
    }

    /**
     * Remove the specified Restaurant from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id) {
        $restaurant = $this->restaurantRepository->findWithoutFail($id);

        if (empty($restaurant)) {
            return $this->sendError('Restaurant not found');
        }

        $restaurant = $this->restaurantRepository->delete($id);

        return $this->sendResponse($restaurant, __('lang.deleted_successfully', ['operator' => __('lang.restaurant')]));
    }

    public function providePopularRestaurents() {
        $restaurant = $this->restaurantRepository->where('is_popular', 1)->get()->toArray();
        return $this->sendResponse($restaurant, 'Restaurant retrieved successfully');
    }

    public function provideSafetyRestaurents() {
        $restaurants = $this->restaurantRepository->where('is_safety', 1)->get()->toArray();
        if (!empty($restaurants)) {
            $msg = 'Restaurant retrieved successfully';
        } else {
            $restaurants = [];
            $msg = "No records found";
        }
        return $this->sendResponse($restaurants, $msg);
    }

    public function makeSafetyRestaurent(Request $request) {
        $input = $request->all();

        $data = [
            'is_safety' => 1
        ];
        $result = $this->restaurantRepository->where('id', $input['restaurant_id'])->update($data);

        if ($result) {
            $response = ['status' => 1];
            $msg = 'Records updated successfully';
            return $this->sendResponse($response, $msg);
        } else {
            return $this->sendError('could not updated');
        }
    }

    public function provideMostPopularRestaurents() {
        $restaurants = $this->restaurantRepository->where('is_most_popular', 1)->get()->toArray();
        if (!empty($restaurants)) {
            $msg = 'Restaurant retrieved successfully';
        } else {
            $restaurants = [];
            $msg = "No records found";
        }
        return $this->sendResponse($restaurants, $msg);
    }

    public function provideRestaurantGiftcards(Request $request) {
        $input = $request->all();
        $restaurant_id = $input['restaurant_id'];


        $gift_cards = Giftcard::join('restaurent_giftcards', 'restaurent_giftcards.giftcard_id', '=', 'giftcard.id')
                        ->where('restaurent_giftcards.restaurant_id', $restaurant_id)
                        ->get()->toArray();

        if (!empty($gift_cards)) {
            $msg = 'Restaurant retrieved successfully';
        } else {
            $gift_cards = [];
            $msg = 'No records found';
        }

        return $this->sendResponse($gift_cards, $msg);
    }

    public function provideGiftcardRestaurants(Request $request) {
        $input = $request->all();
        $giftcard_id = $input['giftcard_id'];

        try {
            $this->restaurantRepository->pushCriteria(new RequestCriteria($request));
            $this->restaurantRepository->pushCriteria(new LimitOffsetCriteria($request));
            $this->restaurantRepository->pushCriteria(new RestaurantsOfCuisinesCriteria($request));
            if ($request->has('popular')) {
                $this->restaurantRepository->pushCriteria(new PopularCriteria($request));
            } else {
                $this->restaurantRepository->pushCriteria(new NearCriteria($request));
            }
            $this->restaurantRepository->pushCriteria(new ActiveCriteria());
            $restaurants = $this->restaurantRepository->provideGiftcardRestaurants($giftcard_id);

            // $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->restaurantRepository->model());
            // foreach ($customFields as $key => $value) {
            //     echo $value->name;
            // }
            // $dsf = getCustomFieldsValues($customFields, $request);
            // print_r($dsf);
            // exit();
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }

        if (!empty($restaurants)) {
            $msg = 'Restaurant retrieved successfully';
        } else {
            $restaurants = [];
            $msg = 'No records found';
        }

        return $this->sendResponse($restaurants, $msg);
    }

}
