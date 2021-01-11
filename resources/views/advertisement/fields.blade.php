@if($customFields)
<h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">
<!-- Name Field -->
<div class="form-group row ">
  {!! Form::label('name', trans("lang.services_name"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('name', null, ['class' => 'form-control','placeholder'=>
     trans("lang.services_name_placeholder")  ]) !!}
    <div class="form-text text-muted">{{ trans("lang.services_name_help") }}</div>
  </div>
</div>

<!-- Price Field -->
<div class="form-group row ">
  {!! Form::label('price', trans("lang.services_price"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('price', null, ['class' => 'form-control','placeholder'=>
     trans("lang.services_price_placeholder")  ]) !!}
    <div class="form-text text-muted">{{ trans("lang.services_price_help") }}</div>
  </div>
</div>

<!-- Agent Commission -->
<div class="form-group row ">
  {!! Form::label('agent_commission', trans("lang.services_agent_commission"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('agent_commission', null, ['class' => 'form-control','placeholder'=>
     trans("lang.services_agent_commission_placeholder")  ]) !!}
    <div class="form-text text-muted">{{ trans("lang.services_agent_commission_help") }}</div>
  </div>
</div>
</div>
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

<!-- Faq Category Id Field -->
<div class="form-group row ">
  {!! Form::label('insurance_id', trans("lang.services_insurance_id"),['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::select('insurance_id', $insurance, null, ['class' => 'select2 form-control']) !!}
    <div class="form-text text-muted">{{ trans("lang.services_insurance_id_help") }}</div>
  </div>
</div>

</div>
@if($customFields)
<div class="clearfix"></div>
<div class="col-12 custom-field-container">
  <h5 class="col-12 pb-4">{!! trans('lang.custom_field_plural') !!}</h5>
  {!! $customFields !!}
</div>
@endif
<!-- Submit Field -->
<div class="form-group col-12 text-right">
  <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.services')}}</button>
  <a href="{!! route('faqs.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
