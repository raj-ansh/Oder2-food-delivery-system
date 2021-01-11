<?php

namespace App\Http\Controllers;

use App\Supplyhead;

use App\DataTables\SupplyheadDataTable;
use App\Http\Requests\CreateSupplyheadRequest;
use App\Http\Requests\UpdateSupplyheadRequest;
use App\Repositories\SupplyheadRepository;
use App\Repositories\UserRepository;
use App\Repositories\CustomFieldRepository;
use App\Models\RegManager;

use App\Repositories\RoleRepository;
use App\Models\User;

use Flash;
use App\Repositories\UploadRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class SupplyheadController extends Controller
{
    /** @var  supplyheadRepository */
    private $supplyheadRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    private $roleRepository;
    private $userRepository;
    private $regManage;

    public function __construct(SupplyheadRepository $supplyheadRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo,UserRepository $userRepository, RegManager $regMan, RoleRepository $roleRepo)
    {
        parent::__construct();
        $this->supplyheadRepository = $supplyheadRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->userRepository = $userRepository;
        $this->regManage = $regMan;
        $this->roleRepository = $roleRepo;
    }

    /**
     * Display a listing of the Category.
     *
     * @param CategoryDataTable $categoryDataTable
     * @return Response
     */
    public function index(SupplyheadDataTable $categoryDataTable)
    {
        return $categoryDataTable->render('supplyhead.index');
    }

    /**
     * Show the form for creating a new Category.
     *
     * @return Response
     */
    public function create()
    {
        $reg = $this->regManage->pluck('name','id');

        $hasCustomField = in_array($this->supplyheadRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->supplyheadRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('supplyhead.create')->with("customFields", isset($html) ? $html : false)->with("regmanager", $reg);

       // return view('items.create')->with("customFields", isset($html) ? $html : false)->with("categoryItem", $categoryItem);
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param CreateCategoryRequest $request
     *
     * @return Response
     */
    public function store(CreateSupplyheadRequest $request)
    {
        $input = $request->all();
       
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->supplyheadRepository->model());

        $inputs['roles'] = isset($input['roles']) ? $input['roles'] : [];
        $inputs['name'] =  $input['name'];
        $inputs['email'] =  $input['email'];
        $inputs['password'] = Hash::make(str_random(60));
        $inputs['api_token'] = str_random(60);

        try {
            $user_id = $this->_user_register($input);
            $input['user_id'] = $user_id;
            $input['dob'] = date("Y-m-d", strtotime($input['dob']));
            
            $supplyhead = $this->supplyheadRepository->create($input);
            $supplyhead->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($category, 'image');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('Supplyhead save success fully', ['operator' => __('lang.category')]));

        return redirect(route('supplyhead.index'));
    }

    /**
     * Display the specified Category.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $category = $this->supplyheadRepository->findWithoutFail($id);
        if (empty($category)) {
            Flash::error('Category not found');

            return redirect(route('supplyhead.index'));
        }

        return view('supplyhead.show')->with('category', $category);
    }

    /**
     * Show the form for editing the specified Category.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $category = $this->supplyheadRepository->findWithoutFail($id);


        if (empty($category)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.category')]));

            return redirect(route('supplyhead.index'));
        }
        $customFieldsValues = $category->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->supplyheadRepository->model());
        $hasCustomField = in_array($this->supplyheadRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('supplyhead.edit')->with('category', $category)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified Category in storage.
     *
     * @param int $id
     * @param UpdateCategoryRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateCategoryRequest $request)
    {
        $category = $this->supplyheadRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');
            return redirect(route('supplyhead.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->supplyheadRepository->model());
        try {
            $category = $this->supplyheadRepository->update($input, $id);

            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($category, 'image');
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $category->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.category')]));

        return redirect(route('supplyhead.index'));
    }

    /**
     * Remove the specified Category from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $category = $this->supplyheadRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');

            return redirect(route('supplyhead.index'));
        }

        $this->supplyheadRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.category')]));

        return redirect(route('supplyhead.index'));
    }

    /**
     * Remove Media of Category
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $category = $this->supplyheadRepository->findWithoutFail($input['id']);
        try {
            if ($category->hasMedia($input['collection'])) {
                $category->getFirstMedia($input['collection'])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private  function _user_register($data)
    {
        $password = $this->_gen_password();
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->device_token = '';
        $user->password = Hash::make($password);
        $user->api_token = str_random(60);
        $user->save();

        $defaultRoles = $this->roleRepository->findByField('name', 'marketer_ssh');
        $defaultRoles = $defaultRoles->pluck('name')->toArray();
        $user->assignRole($defaultRoles);
        return $user->id;
    }

    private function _gen_password() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < 6; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }
}
