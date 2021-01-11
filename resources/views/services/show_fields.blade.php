<!-- Id Field -->
<div class="form-group row col-6">
  {!! Form::label('id', 'Id:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $services->id !!}</p>
  </div>
</div>

<!-- Question Field -->
<div class="form-group row col-6">
  {!! Form::label('name', 'Name:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $services->name !!}</p>
  </div>
</div>

<!-- services Category Id Field -->
<div class="form-group row col-6">
  {!! Form::label('insurance_id', 'Insurance:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $services->insurance_id !!}</p>
  </div>
</div>

<!-- Answer Field -->
<div class="form-group row col-6">
  {!! Form::label('price', 'Price:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $services->price !!}</p>
  </div>
</div>

<div class="form-group row col-6">
  {!! Form::label('agent_commission', 'Agent Commission:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $services->agent_commission !!}</p>
  </div>
</div>

<!-- Created At Field -->
<div class="form-group row col-6">
  {!! Form::label('created_at', 'Created At:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $services->created_at !!}</p>
  </div>
</div>

<!-- Updated At Field -->
<div class="form-group row col-6">
  {!! Form::label('updated_at', 'Updated At:', ['class' => 'col-3 control-label text-right']) !!}
  <div class="col-9">
    <p>{!! $services->updated_at !!}</p>
  </div>
</div>

