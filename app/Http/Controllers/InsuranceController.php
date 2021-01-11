<?php

namespace App\Http\Controllers;

use App\DataTables\InsuranceDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateInsuranceRequest;
use App\Http\Requests\UpdateInsuranceRequest;
use App\Repositories\InsuranceRepository;
use App\Repositories\CustomFieldRepository;

use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;

class InsuranceController extends Controller
{
    /** @var  InsuranceRepository */
    private $insuranceRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    

    public function __construct(InsuranceRepository $insuranceRepo, CustomFieldRepository $customFieldRepo )
    {
        parent::__construct();
        $this->insuranceRepository = $insuranceRepo;
        $this->customFieldRepository = $customFieldRepo;
        
    }

    /**
     * Display a listing of the FaqCategory.
     *
     * @param InsuranceDataTable $faqCategoryDataTable
     * @return Response
     */
    public function index(InsuranceDataTable $insuranceDataTable)
    {
        return $insuranceDataTable->render('insurance.index');
    }

    /**
     * Show the form for creating a new FaqCategory.
     *
     * @return Response
     */
    public function create()
    {
        $hasCustomField = in_array($this->insuranceRepository->model(),setting('custom_field_models',[]));
            if($hasCustomField){
                $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->insuranceRepository->model());
                $html = generateCustomField($customFields);
            }
        return view('insurance.create')->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Store a newly created FaqCategory in storage.
     *
     * @param CreateInsuranceRequest $request
     *
     * @return Response
     */
    public function store(CreateInsuranceRequest $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->insuranceRepository->model());
        try {
            $insurance = $this->insuranceRepository->create($input);
            $insurance->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));
            
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.faq_category')]));

        return redirect(route('insurance.index'));
    }

    /**
     * Display the specified FaqCategory.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $insurance = $this->insuranceRepository->findWithoutFail($id);

        if (empty($insurance)) {
            Flash::error('Insurance not found');

            return redirect(route('insurance.index'));
        }

        return view('insurance.show')->with('insurance', $insurance);
    }

    /**
     * Show the form for editing the specified FaqCategory.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $insurance = $this->insuranceRepository->findWithoutFail($id);
        
        

        if (empty($insurance)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.faq_category')]));

            return redirect(route('insurance.index'));
        }
        $customFieldsValues = $insurance->customFieldsValues()->with('customField')->get();
        $customFields =  $this->customFieldRepository->findByField('custom_field_model', $this->insuranceRepository->model());
        $hasCustomField = in_array($this->insuranceRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('insurance.edit')->with('insurance', $insurance)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified Insurance in storage.
     *
     * @param  int              $id
     * @param UpdateInsuranceRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateInsuranceRequest $request)
    {
        $insurance = $this->insuranceRepository->findWithoutFail($id);

        if (empty($insurance)) {
            Flash::error('Insurance not found');
            return redirect(route('insurance.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->insuranceRepository->model());
        try {
            $insurance = $this->insuranceRepository->update($input, $id);
            
            
            foreach (getCustomFieldsValues($customFields, $request) as $value){
                $insurance->customFieldsValues()
                    ->updateOrCreate(['custom_field_id'=>$value['custom_field_id']],$value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully',['operator' => __('lang.faq_category')]));

        return redirect(route('insurance.index'));
    }

    /**
     * Remove the specified FaqCategory from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $insurance = $this->insuranceRepository->findWithoutFail($id);

        if (empty($insurance)) {
            Flash::error('Insurance not found');

            return redirect(route('insurance.index'));
        }

        $this->insuranceRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.faq_category')]));

        return redirect(route('insurance.index'));
    }

        /**
     * Remove Media of FaqCategory
     * @param Request $request
     */
    // public function removeMedia(Request $request)
    // {
    //     $input = $request->all();
    //     $faqCategory = $this->faqCategoryRepository->findWithoutFail($input['id']);
    //     try {
    //         if($faqCategory->hasMedia($input['collection'])){
    //             $faqCategory->getFirstMedia($input['collection'])->delete();
    //         }
    //     } catch (\Exception $e) {
    //         Log::error($e->getMessage());
    //     }
    // }
}
