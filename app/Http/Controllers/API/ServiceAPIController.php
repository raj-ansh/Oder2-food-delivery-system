<?php
/**
 * File name: CategoryAPIController.php
 * Last modified: 2020.05.04 at 09:04:18
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Repositories\ServicesRepository;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class CategoryController
 * @package App\Http\Controllers\API
 */
class ServiceAPIController extends Controller
{
    /** @var  CategoryRepository */
    private $serviceRepository;

    public function __construct(ServicesRepository $serviceRepo)
    {
        $this->serviceRepository = $serviceRepo;
    }

    /**
     * Display a listing of the Category.
     * GET|HEAD /categories
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $categories = $this->serviceRepository->all();

        return $this->sendResponse($categories->toArray(), 'Services retrieved successfully');
    }

    /**
     * Display the specified Category.
     * GET|HEAD /categories/{id}
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function show($id)
    // {
    //     /** @var Category $category */
    //     if (!empty($this->serviceRepository)) {
    //         $category = $this->serviceRepository->findWithoutFail($id);
    //     }

    //     if (empty($category)) {
    //         return $this->sendError('Category not found');
    //     }

    //     return $this->sendResponse($category->toArray(), 'Category retrieved successfully');
    // }

    /**
     * Store a newly created Category in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function store(Request $request)
    // {
    //     $input = $request->all();
    //     $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->serviceRepository->model());
    //     try {
    //         $category = $this->serviceRepository->create($input);
    //         $category->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
    //         if (isset($input['image']) && $input['image']) {
    //             $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
    //             $mediaItem = $cacheUpload->getMedia('image')->first();
    //             $mediaItem->copy($category, 'image');
    //         }
    //     } catch (ValidatorException $e) {
    //         return $this->sendError($e->getMessage());
    //     }

    //     return $this->sendResponse($category->toArray(), __('lang.saved_successfully', ['operator' => __('lang.category')]));
    // }

    /**
     * Update the specified Category in storage.
     *
     * @param int $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function update($id, Request $request)
    // {
    //     $category = $this->serviceRepository->findWithoutFail($id);

    //     if (empty($category)) {
    //         return $this->sendError('Category not found');
    //     }
    //     $input = $request->all();
    //     $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->serviceRepository->model());
    //     try {
    //         $category = $this->serviceRepository->update($input, $id);

    //         if (isset($input['image']) && $input['image']) {
    //             $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
    //             $mediaItem = $cacheUpload->getMedia('image')->first();
    //             $mediaItem->copy($category, 'image');
    //         }
    //         foreach (getCustomFieldsValues($customFields, $request) as $value) {
    //             $category->customFieldsValues()
    //                 ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
    //         }
    //     } catch (ValidatorException $e) {
    //         return $this->sendError($e->getMessage());
    //     }

    //     return $this->sendResponse($category->toArray(), __('lang.updated_successfully', ['operator' => __('lang.category')]));

    // }

    /**
     * Remove the specified Category from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function destroy($id)
    // {
    //     $category = $this->serviceRepository->findWithoutFail($id);

    //     if (empty($category)) {
    //         return $this->sendError('Category not found');
    //     }

    //     $category = $this->serviceRepository->delete($id);

    //     return $this->sendResponse($category, __('lang.deleted_successfully', ['operator' => __('lang.category')]));
    // }
}
