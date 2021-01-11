<div class='btn-group btn-group-sm'>
  @can('servicebdm.show')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}" href="{{ route('servicebdm.show', $id) }}" class='btn btn-link'>
    <i class="fa fa-eye"></i>
  </a>
  @endcan
  @can('servicebdm.edit')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.servicebdm_edit')}}" href="{{ route('servicebdm.edit', $id) }}" class='btn btn-link'>
    <i class="fa fa-edit"></i>
  </a>
  @endcan
  @can('servicebdm.destroy')
{!! Form::open(['route' => ['servicebdm.destroy', $id], 'method' => 'delete']) !!}
  {!! Form::button('<i class="fa fa-trash"></i>', [
  'type' => 'submit',
  'class' => 'btn btn-link text-danger',
  'onclick' => "return confirm('Are you sure?')"
  ]) !!}
{!! Form::close() !!}
  @endcan
</div>
