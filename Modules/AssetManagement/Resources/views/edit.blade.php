@extends('layouts.app')

@section('content')

    <div class="content-wrapper">
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                <x-app-title class="m-0 mr-4" :pageTitle="$pageTitle" />
            </div>
            <div class="d-flex w-100 justify-content-end">
                <x-forms.link-secondary :link="route('assetmanagement.index')" class="mr-3" icon="arrow-left">
                    @lang('app.back')
                </x-forms.link-secondary>
            </div>
        </div>

        <div class="card card-white border-0 b-shadow-4 mt-3">
            <div class="card-body">
                <x-form id="save-asset-data-form" method="PUT">
                    <div class="row">
                        <div class="col-md-4">
                            <x-forms.text fieldId="name" :fieldLabel="__('Name')" fieldName="name" fieldRequired="true"
                                :fieldValue="$asset->name" :fieldPlaceholder="__('e.g. MacBook Pro')" />
                        </div>
                        <div class="col-md-4">
                            <x-forms.text fieldId="serial_number" :fieldLabel="__('Serial Number')" fieldName="serial_number"
                                fieldRequired="true" :fieldValue="$asset->serial_number" :fieldPlaceholder="__('e.g. SN12345678')" />
                        </div>
                        <div class="col-md-4">
                            <x-forms.text fieldId="type" :fieldLabel="__('Type')" fieldName="type" fieldRequired="true"
                                :fieldValue="$asset->type" :fieldPlaceholder="__('e.g. Laptop, Phone')" />
                        </div>
                        <div class="col-md-4">
                            <x-forms.select fieldId="status" :fieldLabel="__('Status')" fieldName="status">
                                <option value="available" @if($asset->status == 'available') selected @endif>Available</option>
                                <option value="assigned" @if($asset->status == 'assigned') selected @endif>Assigned</option>
                                <option value="damaged" @if($asset->status == 'damaged') selected @endif>Damaged</option>
                                <option value="lost" @if($asset->status == 'lost') selected @endif>Lost</option>
                            </x-forms.select>
                        </div>
                        <div class="col-md-4">
                            <x-forms.select fieldId="user_id" :fieldLabel="__('Assign To')" fieldName="user_id"
                                search="true">
                                <option value="">-- Select Employee --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @if($asset->user_id == $employee->id) selected @endif>{{ $employee->name }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="col-md-4">
                             <x-forms.number fieldId="value" :fieldLabel="__('Value')" fieldName="value"
                                :fieldValue="$asset->value" :fieldPlaceholder="__('e.g. 1500.00')" />
                        </div>
                        <div class="col-md-12">
                            <x-forms.textarea fieldId="description" :fieldLabel="__('Description')" fieldName="description"
                                :fieldValue="$asset->description" />
                        </div>
                    </div>
                    <div class="w-100 border-top-grey d-flex justify-content-start px-4 py-3">
                        <x-forms.button-primary id="save-asset-form" class="mr-3" icon="check">@lang('app.save')
                        </x-forms.button-primary>
                        <x-forms.button-cancel :link="route('assetmanagement.index')" class="border-0">@lang('app.cancel')
                        </x-forms.button-cancel>
                    </div>
                </x-form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $('#save-asset-form').click(function() {
            const url = "{{ route('assetmanagement.update', $asset->id) }}";
            $.easyAjax({
                url: url,
                container: '#save-asset-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-asset-form",
                data: $('#save-asset-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl || "{{ route('assetmanagement.index') }}";
                    }
                }
            });
        });
    </script>
@endpush
