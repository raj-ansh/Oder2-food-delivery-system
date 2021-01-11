<?php

namespace App\Http\Controllers;

use App\Bdm;
use Illuminate\Http\Request;


use App\DataTables\bdmDataTable;
use App\Http\Requests\CreateBdmRequest;
use App\Http\Requests\UpdateBdmRequest;
use App\Repositories\BdmRepository;

use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;
use App\Models\User;
use App\Models\Servicecebdm;
use App\Repositories\CustomFieldRepository;
use Flash;
use App\Repositories\UploadRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class bdmController extends Controller
{
    /** @var  bdmRepository */
    private $bdmRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    private $userRepository;
    private $roleRepository;
    private $servicebdm;

    //public $lastId;

    public function __construct(BdmRepository $bdmRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo,UserRepository $userRepository, Servicecebdm $servicebdm, RoleRepository $roleRepo)
    {
        parent::__construct();
        $this->bdmRepository = $bdmRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->userRepository = $userRepository;
        $this->servicebdm = $servicebdm;
        $this->roleRepository = $roleRepo;
    }

    /**
     * Display a listing of the Category.
     *
     * @param CategoryDataTable $categoryDataTable
     * @return Response
     */
    public function index(bdmDataTable $categoryDataTable)
    {
        return $categoryDataTable->render('bdm.index');
    }

    /**
     * Show the form for creating a new Category.
     *
     * @return Response
     */
    public function create()
    {
        //$user = $this->user->pluck('name','id');
        $servicebdm = $this->servicebdm->pluck('name','id');

        $hasCustomField = in_array($this->bdmRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->bdmRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('bdm.create')->with("customFields", isset($html) ? $html : false)->with("servicebdm", $servicebdm);

       // return view('items.create')->with("customFields", isset($html) ? $html : false)->with("categoryItem", $categoryItem);
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param CreateCategoryRequest $request
     *
     * @return Response
     */
    public function store(CreateBdmRequest $request)
    {
        
        $input = $request->all();
        
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->bdmRepository->model());

        $inputs['roles'] = isset($input['roles']) ? $input['roles'] : [];
        $inputs['name'] =  $input['name'];
        $inputs['email'] =  $input['email'];
        $inputs['password'] = Hash::make(str_random(60));
        $inputs['api_token'] = str_random(60);

        try {

            $user_id = $this->_user_register($input);

            
            $input['user_id'] = $user_id;
            $input['dob'] = date("Y-m-d", strtotime($input['dob']));
            

            $category = $this->bdmRepository->create($input);
            $category->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($category, 'image');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('Businesshead save success fully', ['operator' => __('lang.category')]));

        return redirect(route('bdm.index'));
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
        $category = $this->bdmRepository->findWithoutFail($id);
        if (empty($category)) {
            Flash::error('Category not found');

            return redirect(route('bdm.index'));
        }

        return view('bdm.show')->with('category', $category);
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
        $category = $this->bdmRepository->findWithoutFail($id);


        if (empty($category)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.category')]));

            return redirect(route('bdm.index'));
        }
        $customFieldsValues = $category->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->bdmRepository->model());
        $hasCustomField = in_array($this->bdmRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('bdm.edit')->with('category', $category)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified Category in storage.
     *
     * @param int $id
     * @param UpdateCategoryRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateBdmRequest $request)
    {
        $category = $this->bdmRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');
            return redirect(route('bdm.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->bdmRepository->model());
        try {
            $category = $this->bdmRepository->update($input, $id);

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

        return redirect(route('bdm.index'));
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
        $category = $this->bdmRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');

            return redirect(route('bdm.index'));
        }

        $this->bdmRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.category')]));

        return redirect(route('bdm.index'));
    }

    /**
     * Remove Media of Category
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $category = $this->bdmRepository->findWithoutFail($input['id']);
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

        $defaultRoles = $this->roleRepository->findByField('name', 'marketer_bdm');
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
