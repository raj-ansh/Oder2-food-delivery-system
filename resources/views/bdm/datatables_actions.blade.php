<div class='btn-group btn-group-sm'>
  @can('bdm.show')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}" href="{{ route('bdm.show', $id) }}" class='btn btn-link'>
    <i class="fa fa-eye"></i>
  </a>
  @endcan
  @can('bdm.edit')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.bdm_edit')}}" href="{{ route('bdm.edit', $id) }}" class='btn btn-link'>
    <i class="fa fa-edit"></i>
  </a>
  @endcan
  @can('bdm.destroy')
{!! Form::open(['route' => ['bdm.destroy', $id], 'method' => 'delete']) !!}
  {!! Form::button('<i class="fa fa-trash"></i>', [
  'type' => 'submit',
  'class' => 'btn btn-link text-danger',
  'onclick' => "return confirm('Are you sure?')"
  ]) !!}
{!! Form::close() !!}
  @endcan
</div>
