@if($customFields)
<h5 class="col-12 pb-4">{!! trans('lang.main_fields') !!}</h5>
@endif
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

<div class="form-group row">
  {!! Form::label('name', trans("Regional Manager"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::select('chain_id', $regmanager, null, ['class' => 'select2 form-control']) !!}
    <div class="form-text text-muted">
      {{ trans("lang.category_name_help") }}
    </div>
  </div>
</div>
<!-- Name Field -->
<input type="hidden" name="user_id" value="3">
<div class="form-group row ">
  {!! Form::label('name', trans("lang.category_name"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('name', null,  ['class' => 'form-control']) !!}
    <div class="form-text text-muted">
      {{ trans("lang.category_name_help") }}
    </div>
  </div>
</div>
<div class="form-group row">
  {!! Form::label('name', trans("Gender"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::select('gender', ['female','male'], null, ['class' => 'select2 form-control']) !!}
    <div class="form-text text-muted">
      {{ trans("lang.category_name_help") }}
    </div>
  </div>
</div>
<div class="form-group row ">
  {!! Form::label('name', trans("Phone"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::number('phone', null,  ['class' => 'form-control','placeholder'=>  trans("Insert Phone")]) !!}
    <div class="form-text text-muted">
      {{ trans("lang.category_name_help") }}
    </div>
  </div>
</div>
<div class="form-group row ">
  {!! Form::label('name', trans("State"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::select('sate',['odisha','Kerala','Gujarat'], null,  ['class' => 'form-control']) !!}
    <div class="form-text text-muted">
      {{ trans("lang.category_name_help") }}
    </div>
  </div>
</div>
<!-- Address Field -->
<!-- Address Field -->
<div class="form-group row ">
  {!! Form::label('name', trans("District"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::select('distric',['Khroda','jagatsingpur','Kendrapada'], null,  ['class' => 'form-control']) !!}
    <div class="form-text text-muted">
      {{ trans("lang.category_name_help") }}
    </div>
  </div>
</div>
<div class="form-group row ">
  {!! Form::label('name', trans("PAN No"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('panno', null,  ['class' => 'form-control']) !!}
    <div class="form-text text-muted">
      {{ trans("lang.category_name_help") }}
    </div>
  </div>
</div>
<div class="form-group row">
  {!! Form::label('image', trans("PAN "), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <div style="width: 100%" class="dropzone image" id="image" data-field="image">
      <input type="hidden" name="image">
    </div>
    <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
    <div class="form-text text-muted w-50">
      {{ trans("lang.category_image_help") }}
    </div>
  </div>
</div>
</div>
<div style="flex: 50%;max-width: 50%;padding: 0 4px;" class="column">

<!-- Image Field -->
<div class="form-group row ">
  {!! Form::label('name', trans("DOB"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('dob', null,  ['class' =>'date form-control','autocomplete'=>'off']) !!}
    <div class="form-text text-muted">
      {{ trans("lang.category_name_help") }}
    </div>
  </div>
</div>
<div class="form-group row ">
  {!! Form::label('name', trans("Email"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('email', null,  ['class' => 'form-control']) !!}
    <div class="form-text text-muted">
      {{ trans("lang.category_name_help") }}
    </div>
  </div>
</div>
<div class="form-group row ">
  {!! Form::label('name', trans("PIN"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('pin', null,  ['class' => 'form-control']) !!}
    <div class="form-text text-muted">
      {{ trans("lang.category_name_help") }}
    </div>
  </div>
</div>
<div class="form-group row ">
  {!! Form::label('name', trans("Aadhaar No"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    {!! Form::text('adhno', null,  ['class' => 'form-control']) !!}
    <div class="form-text text-muted">
      {{ trans("lang.category_name_help") }}
    </div>
  </div>
</div>
<div class="form-group row">
  {!! Form::label('image', trans("Aadhaar Front"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <div style="width: 100%" class="dropzone image" id="image" data-field="image">
      <input type="hidden" name="image">
    </div>
    <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
    <div class="form-text text-muted w-50">
      {{ trans("lang.category_image_help") }}
    </div>
  </div>
</div>
<div class="form-group row">
  {!! Form::label('image', trans("Aadhaar Back"), ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <div style="width: 100%" class="dropzone image" id="image" data-field="image">
      <input type="hidden" name="image">
    </div>
    <a href="#loadMediaModal" data-dropzone="image" data-toggle="modal" data-target="#mediaModal" class="btn btn-outline-{{setting('theme_color','primary')}} btn-sm float-right mt-1">{{ trans('lang.media_select')}}</a>
    <div class="form-text text-muted w-50">
      {{ trans("lang.category_image_help") }}
    </div>
  </div>
</div>
@prepend('scripts')
<script type="text/javascript">
    var var15866134771240834480ble = '';
    @if(isset($category) && $category->hasMedia('image'))
    var15866134771240834480ble = {
        name: "{!! $category->getFirstMedia('image')->name !!}",
        size: "{!! $category->getFirstMedia('image')->size !!}",
        type: "{!! $category->getFirstMedia('image')->mime_type !!}",
        collection_name: "{!! $category->getFirstMedia('image')->collection_name !!}"};
    @endif
    var dz_var15866134771240834480ble = $(".dropzone.image").dropzone({
        url: "{!!url('uploads/store')!!}",
        addRemoveLinks: true,
        maxFiles: 1,
        init: function () {
        @if(isset($category) && $category->hasMedia('image'))
            dzInit(this,var15866134771240834480ble,'{!! url($category->getFirstMediaUrl('image','thumb')) !!}')
        @endif
        },
        accept: function(file, done) {
            dzAccept(file,done,this.element,"{!!config('medialibrary.icons_folder')!!}");
        },
        sending: function (file, xhr, formData) {
            dzSending(this,file,formData,'{!! csrf_token() !!}');
        },
        maxfilesexceeded: function (file) {
            dz_var15866134771240834480ble[0].mockFile = '';
            dzMaxfile(this,file);
        },
        complete: function (file) {
            dzComplete(this, file, var15866134771240834480ble, dz_var15866134771240834480ble[0].mockFile);
            dz_var15866134771240834480ble[0].mockFile = file;
        },
        removedfile: function (file) {
            dzRemoveFile(
                file, var15866134771240834480ble, '{!! url("categories/remove-media") !!}',
                'image', '{!! isset($category) ? $category->id : 0 !!}', '{!! url("uplaods/clear") !!}', '{!! csrf_token() !!}'
            );
        }
    });
    dz_var15866134771240834480ble[0].mockFile = var15866134771240834480ble;
    dropzoneFields['image'] = dz_var15866134771240834480ble;
</script>
@endprepend
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
  <button type="submit" class="btn btn-{{setting('theme_color')}}" ><i class="fa fa-save"></i> {{trans('lang.save')}} {{trans('lang.category')}}</button>
  <a href="{!! route('supplyhead.index') !!}" class="btn btn-default"><i class="fa fa-undo"></i> {{trans('lang.cancel')}}</a>
</div>
