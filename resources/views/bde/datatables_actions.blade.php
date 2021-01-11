<div class='btn-group btn-group-sm'>
  @can('bde.show')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.bde_details')}}" href="{{ route('bde.show', $id) }}" class='btn btn-link'>
    <i class="fa fa-eye"></i>
  </a>
  @endcan
  @can('bde.edit')
  <a data-toggle="tooltip" data-placement="bottom" title="{{trans('lang.bde_edit')}}" href="{{ route('bde.edit', $id) }}" class='btn btn-link'>
    <i class="fa fa-edit"></i>
  </a>
  @endcan
  @can('bde.destroy')
{!! Form::open(['route' => ['bde.destroy', $id], 'method' => 'delete']) !!}
  {!! Form::button('<i class="fa fa-trash"></i>', [
  'type' => 'submit',
  'class' => 'btn btn-link text-danger',
  'onclick' => "return confirm('Are you sure?')"
  ]) !!}
{!! Form::close() !!}
  @endcan
</div>
