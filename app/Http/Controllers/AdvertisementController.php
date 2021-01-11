<?php

namespace App\Http\Controllers;

use App\DataTables\AdvertisementDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateAdvertisementRequest;
use App\Http\Requests\UpdateAdvertisementRequest;
use App\Repositories\AdvertisementRepository;
use App\Repositories\ServicesRepository;
use App\Repositories\CustomFieldRepository;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Prettus\Validator\Exceptions\ValidatorException;
class AdvertisementController extends Controller
{
    /** @var  FaqRepository */
    private $advertisementRepository;

    /**
     * @var CustomFieldRepository
     */
    private $customFieldRepository;

    /**
    * @var FaqCategoryRepository
    */
    private $servicesRepository;

    public function __construct(AdvertisementRepository $advertisementRepo, CustomFieldRepository $customFieldRepo , ServicesRepository $servicesRepo)
    {
        parent::__construct();
        $this->advertisementRepository = $advertisementRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->servicesRepository = $servicesRepo;
    }

    /**
     * Display a listing of the Faq.
     *
     * @param FaqDataTable $faqDataTable
     * @return Response
     */
    public function index(AdvertisementDataTable $advertisementDataTable)
    {
        return $advertisementDataTable->render('advertisement.index');
    }

    /**
     * Show the form for creating a new Faq.
     *
     * @return Response
     */
    public function create()
    {
        $services = $this->servicesRepository->pluck('name','id');
        
        $hasCustomField = in_array($this->advertisementRepository->model(),setting('custom_field_models',[]));
            if($hasCustomField){
                $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->advertisementRepository->model());
                $html = generateCustomField($customFields);
            }
        return view('advertisement.create')->with("customFields", isset($html) ? $html : false)->with("services",$services);
    }

    /**
     * Store a newly created Faq in storage.
     *
     * @param CreateFaqRequest $request
     *
     * @return Response
     */
    public function store(CreateAdvertisementRequest $request)
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->advertisementRepository->model());
        try {
            $advertisement = $this->advertisementRepository->create($input);
            
            $advertisement->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));
            
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.faq')]));

        return redirect(route('advertisement.index'));
    }

    /**
     * Display the specified Faq.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $advertisement = $this->advertisementRepository->findWithoutFail($id);

        if (empty($advertisement)) {
            Flash::error('Advertisement not found');

            return redirect(route('advertisement.index'));
        }

        return view('advertisement.show')->with('services', $advertisement);
    }

    /**
     * Show the form for editing the specified Faq.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $services = $this->servicesRepository->findWithoutFail($id);
        $insurance = $this->insuranceRepository->pluck('name','id');
        

        if (empty($services)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.services')]));

            return redirect(route('services.index'));
        }
        $customFieldsValues = $services->customFieldsValues()->with('customField')->get();
        $customFields =  $this->customFieldRepository->findByField('custom_field_model', $this->servicesRepository->model());
        $hasCustomField = in_array($this->servicesRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('services.edit')->with('services', $services)->with("customFields", isset($html) ? $html : false)->with("insurance",$insurance);
    }

    /**
     * Update the specified Faq in storage.
     *
     * @param  int              $id
     * @param UpdateFaqRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateServicesRequest $request)
    {
        $services = $this->servicesRepository->findWithoutFail($id);

        if (empty($services)) {
            Flash::error('services not found');
            return redirect(route('services.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->servicesRepository->model());
        try {
            $services = $this->servicesRepository->update($input, $id);
            
            
            foreach (getCustomFieldsValues($customFields, $request) as $value){
                $services->customFieldsValues()
                    ->updateOrCreate(['custom_field_id'=>$value['custom_field_id']],$value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully',['operator' => __('lang.faq')]));

        return redirect(route('services.index'));
    }

    /**
     * Remove the specified Faq from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $faq = $this->servicesRepository->findWithoutFail($id);

        if (empty($faq)) {
            Flash::error('Faq not found');

            return redirect(route('services.index'));
        }

        $this->servicesRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.faq')]));

        return redirect(route('services.index'));
    }

        /**
     * Remove Media of Faq
     * @param Request $request
     */
    public function removeMedia(Request $request)
    {
        $input = $request->all();
        $faq = $this->servicesRepository->findWithoutFail($input['id']);
        try {
            if($faq->hasMedia($input['collection'])){
                $faq->getFirstMedia($input['collection'])->delete();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
