<?php

namespace App\Http\Controllers;

use App\RegManager;
use Illuminate\Http\Request;

use App\DataTables\RegManagerDataTable;
use App\Http\Requests\CreateRegManagerRequest;
use App\Http\Requests\UpdateRegManagerRequest;
use App\Repositories\RegManagerRepository;
use App\Models\Businesshead;

use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use App\Repositories\CustomFieldRepository;
use App\Repositories\UploadRepository;
use Flash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class RegManagerController extends Controller
{
    /** @var  regmanagerRepository */
    private $regmanagerRepository;

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
    private $bh;

    public function __construct(RegManagerRepository $regmangRepo,UserRepository $userRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo, Businesshead $businesshead, RoleRepository $roleRepo)
    {
        parent::__construct();
        $this->regmanagerRepository = $regmangRepo;
        $this->user = $userRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
        $this->bh = $businesshead;
        $this->roleRepository = $roleRepo;
    }

    public function index(RegManagerDataTable $regionalManagerDataTable)
    {
        return $regionalManagerDataTable->render('regional_manager.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bh = $this->bh->pluck("name", "id");
        $hasCustomField = in_array($this->regmanagerRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->regmanagerRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('regional_manager.create')->with("customFields", isset($html) ? $html : false)->with('businesshead', $bh);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRegManagerRequest $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->regmanagerRepository->model());

        try {
            
            $user_id = $this->_user_register($input);
            $input['user_id'] = $user_id;
            $input['dob'] = date("Y-m-d", strtotime($input['dob']));

            $category = $this->regmanagerRepository->create($input);
            $category->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($category, 'image');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.category')]));

        return redirect(route('regional_manager.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\RegManager  $regManager
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = $this->regmanagerRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('regional_manager not found');

            return redirect(route('regional_manager.index'));
        }

        return view('regional_manager.show')->with('regional_manager', $category);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\RegManager  $regManager
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category = $this->regmanagerRepository->findWithoutFail($id);


        if (empty($category)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.category')]));

            return redirect(route('regional_manager.index'));
        }
        $customFieldsValues = $category->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->regmanagerRepository->model());
        $hasCustomField = in_array($this->regmanagerRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('regional_manager.edit')->with('category', $category)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RegManager  $regManager
     * @return \Illuminate\Http\Response
     */
    public function update($id, UpdateRegManagerRequest $request)
    {
       $category = $this->regmanagerRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('Category not found');
            return redirect(route('regional_manager.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->regmanagerRepository->model());
        try {
            $category = $this->regmanagerRepository->update($input, $id);

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

        return redirect(route('regional_manager.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RegManager  $regManager
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = $this->regmanagerRepository->findWithoutFail($id);

        if (empty($category)) {
            Flash::error('regional_manager not found');

            return redirect(route('regional_manager.index'));
        }

        $this->regmanagerRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.category')]));

        return redirect(route('regional_manager.index'));
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

        $defaultRoles = $this->roleRepository->findByField('name', 'marketer_rh');
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
