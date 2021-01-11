<div class='btn-group btn-group-sm'>
  @can('reginal_manager.show')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.view_details')}}" href="{{ route('reginal_manager.show', $id) }}" class='btn btn-link'>
    <i class="fa fa-eye"></i>
  </a>
  @endcan
  @can('reginal_manager.edit')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.supplyhead_edit')}}" href="{{ route('supplyhead.edit', $id) }}" class='btn btn-link'>
    <i class="fa fa-edit"></i>
  </a>
  @endcan
  @can('reginal_manager.destroy')
{!! Form::open(['route' => ['reginal_manager.destroy', $id], 'method' => 'delete']) !!}
  {!! Form::button('<i class="fa fa-trash"></i>', [
  'type' => 'submit',
  'class' => 'btn btn-link text-danger',
  'onclick' => "return confirm('Are you sure?')"
  ]) !!}
{!! Form::close() !!}
  @endcan
</div>
