<?php

namespace App\Http\Controllers;


use App\Seniorbde;
use Illuminate\Http\Request;


use App\DataTables\SeniorbdeDataTable;
use App\Http\Requests\CreateSeniorbdeRequest;
use App\Http\Requests\UpdateSeniorbdeRequest;
use App\Repositories\SeniorbdeRepository;

use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;
use App\Models\User;
use App\Models\Bdm;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UploadRepository;
use Flash;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class SeniorbdeController extends Controller
{
    /** @var  seniorbdeRepository */
    private $seniorbdeRepository;

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
    private $bde;

    public function __construct(SeniorbdeRepository $seniorbdeRepo,UserRepository $userRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo, RoleRepository $roleRepo, Bdm $bdm)
    {
        parent::__construct();
        $this->seniorbdeRepository = $seniorbdeRepo;
        $this->user = $userRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->roleRepository = $roleRepo;
        $this->bde = $bdm;
    }

    /**
     * Display a listing of the Category.
     *
     * @param CategoryDataTable $categoryDataTable
     * @return Response
     */
    public function index(SeniorbdeDataTable $seniorbdeDataTable)
    {
        
        return $seniorbdeDataTable->render('seniorbde.index');
    }

    /**
     * Show the form for creating a new Category.
     *
     * @return Response
     */
    public function create()
    {
        $bde = $this->bde->pluck('name','id');
        $hasCustomField = in_array($this->seniorbdeRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->seniorbdeRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('seniorbde.create')->with("customFields", isset($html) ? $html : false)->with("bde", $bde);
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param CreateCategoryRequest $request
     *
     * @return Response
     */
    public function store(CreateSeniorbdeRequest $request)
    {
       // $use = $this->user->pluck('name','id');
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->seniorbdeRepository->model());

        $inputs['roles'] = isset($input['roles']) ? $input['roles'] : [];
        $inputs['name'] =  $input['name'];
        $inputs['email'] =  $input['email'];
        $inputs['password'] = Hash::make(str_random(60));
        $inputs['api_token'] = str_random(60);

        try {
            $user_id = $this->_user_register($input);
            $input['user_id'] = $user_id;
            $input['dob'] = date("Y-m-d", strtotime($input['dob']));
            $category = $this->seniorbdeRepository->create($input);
            $category->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($category, 'image');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('seniorbde Saved successfully', ['operator' => __('lang.category')]));

        return redirect(route('seniorbde.index'));
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
        $category = $this->seniorbdeRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('seniorbde not found');

            return redirect(route('seniorbde.index'));
        }

        return view('seniorbde.show')->with('seniorbde', $category);
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
        $category = $this->seniorbdeRepository->findWithoutFail($id);


        if (empty($category)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.category')]));

            return redirect(route('seniorbde.index'));
        }
        $customFieldsValues = $category->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->seniorbdeRepository->model());
        $hasCustomField = in_array($this->seniorbdeRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('seniorbde.edit')->with('category', $category)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified Category in storage.
     *
     * @param int $id
     * @param UpdateCategoryRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateSeniorbdeRequest $request)
    {
        $category = $this->seniorbdeRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');
            return redirect(route('seniorbde.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->seniorbdeRepository->model());
        try {
            $category = $this->seniorbdeRepository->update($input, $id);

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

        return redirect(route('seniorbde.index'));
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
        $category = $this->seniorbdeRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('seniorbde not found');

            return redirect(route('seniorbde.index'));
        }

        $this->seniorbdeRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.category')]));

        return redirect(route('seniorbde.index'));
    }

    /**
     * Remove Media of Category
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $category = $this->seniorbdeRepository->findWithoutFail($input['id']);
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

        $defaultRoles = $this->roleRepository->findByField('name', 'marketer_sbde');
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
