@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <!-- Filter Actions -->
        <div class="d-flex" id="users-table-actions">
            <x-forms.link-primary :link="route('assetmanagement.create')" class="mr-3 float-left" icon="plus">
                @lang('app.add') Asset
            </x-forms.link-primary>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                 <x-forms.link-primary :link="route('assetmanagement.create')" class="mr-3 float-left" icon="plus">
                    Add Asset
                </x-forms.link-primary>
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            <table class="table table-bordered table-hover" id="assets-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Serial Number</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th>Value</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                        <tr>
                            <td>{{ $asset->id }}</td>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->serial_number }}</td>
                            <td>{{ $asset->type }}</td>
                            <td>
                                <span class="badge badge-{{ $asset->status == 'available' ? 'success' : ($asset->status == 'assigned' ? 'primary' : 'danger') }}">
                                    {{ ucfirst($asset->status) }}
                                </span>
                            </td>
                            <td>{{ $asset->user ? $asset->user->name : '--' }}</td>
                            <td>{{ $asset->value }}</td>
                            <td>
                                <div class="task_view">
                                    <div class="dropdown">
                                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                                            id="dropdownMenuLink-{{ $asset->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-options-vertical icons"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-{{ $asset->id }}">
                                            <a class="dropdown-item openRightModal" href="{{ route('assetmanagement.edit', $asset->id) }}">
                                                <i class="fa fa-edit mr-2"></i> Edit
                                            </a>
                                            <a class="dropdown-item delete-table-row" href="javascript:;" data-id="{{ $asset->id }}">
                                                <i class="fa fa-trash mr-2"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-space" style="height: 200px;">
                                    <div class="empty-space-inner">
                                        <div class="icon" style="font-size: 30px"><i class="fa fa-box-open"></i></div>
                                        <div class="title" style="font-size: 18px">No Assets Found</div>
                                        <div class="subtitle">
                                            <a href="{{ route('assetmanagement.create') }}" class="btn btn-primary btn-sm mt-3">
                                                <i class="fa fa-plus"></i> Add First Asset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('assetmanagement.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
