<?php

namespace App\Http\Controllers;

use App\DataTables\BdeDataTable;
use App\Http\Requests\CreateBdeRequest;
use App\Http\Requests\UpdateBdeRequest;

use App\Repositories\BdeRepository;
use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;
use App\Models\User;
use App\Models\Seniorbde;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UploadRepository;
use Flash;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class BdeController extends Controller
{
    /** @var  contryRepository */
    private $contryRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private $uploadRepository;

    private $roleRepository;

    private $user;
    private $seniorbde;

    public function __construct(BdeRepository $contryRepo,UserRepository $userRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo, RoleRepository $roleRepo, Seniorbde $seniorbde)
    {
        parent::__construct();
        $this->contryRepository = $contryRepo;
        $this->user = $userRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->roleRepository = $roleRepo;
        $this->seniorbde = $seniorbde;
    }

    /**
     * Display a listing of the Category.
     *
     * @param CategoryDataTable $categoryDataTable
     * @return Response
     */
    public function index(BdeDataTable $contryDataTable)
    {
        
        return $contryDataTable->render('bde.index');
    }

    /**
     * Show the form for creating a new Category.
     *
     * @return Response
     */
    public function create()
    {
        $seniorbde = $this->seniorbde->pluck('name','id');

        $hasCustomField = in_array($this->contryRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->contryRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('bde.create')->with("customFields", isset($html) ? $html : false)->with("seniorbde", $seniorbde);
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param CreateCategoryRequest $request
     *
     * @return Response
     */
    public function store(CreateBdeRequest $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->contryRepository->model());

        $inputs['roles'] = isset($input['roles']) ? $input['roles'] : [];
        $inputs['name'] =  $input['name'];
        $inputs['email'] =  $input['email'];
        $inputs['password'] = Hash::make(str_random(60));
        $inputs['api_token'] = str_random(60);
        
        try {
            $user_id = $this->_user_register($input);
            $input['user_id'] = $user_id;
            $input['dob'] = date("Y-m-d", strtotime($input['dob']));
            $category = $this->contryRepository->create($input);
            $category->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($category, 'image');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('Country Saved successfully', ['operator' => __('lang.category')]));

        return redirect(route('bde.index'));
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
        $category = $this->contryRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Country not found');

            return redirect(route('country.index'));
        }

        return view('bde.show')->with('country', $category);
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
        $seniorbde = $this->seniorbde->pluck('name','id');
        $category = $this->contryRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.category')]));

            return redirect(route('bde.index'));
        }
        $customFieldsValues = $category->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->contryRepository->model());
        $hasCustomField = in_array($this->contryRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('bde.edit')->with('category', $category)->with("customFields", isset($html) ? $html : false)->with("seniorbde", $seniorbde);
    }

    /**
     * Update the specified Category in storage.
     *
     * @param int $id
     * @param UpdateCategoryRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateBdeRequest $request)
    {
        $category = $this->contryRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');
            return redirect(route('bde.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->contryRepository->model());
        try {
            $category = $this->contryRepository->update($input, $id);

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

        return redirect(route('bde.index'));
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
        $category = $this->contryRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Country not found');

            return redirect(route('bde.index'));
        }

        $this->contryRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.category')]));

        return redirect(route('bde.index'));
    }

    /**
     * Remove Media of Category
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $category = $this->contryRepository->findWithoutFail($input['id']);
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

        $defaultRoles = $this->roleRepository->findByField('name', 'marketer_bde');
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
