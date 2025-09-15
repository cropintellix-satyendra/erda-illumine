@extends('layout.default')
@section('content')
{{-- Default box --}}
<div class="container-fluid">
    <div class="row page-titles mx-0">
        <div class="col-sm-6 p-md-0">
            <div class="welcome-text">
                <h4>Backup Manager</h4>
            </div>
        </div>
        <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Admin</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Backup</a></li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            
        <div class="card">
            <div class="card-header px-0">
                <button id="create-new-backup-button" href="{{ url(config('backpack.base.route_prefix', 'admin').'/backup/create') }}" class="btn btn-primary mb-2">
                    <i class="la la-spinner"></i>
                    <i class="la la-plus"></i>
                    <span>{{ trans('backpack::backup.create_a_new_backup') }}</span>
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover pb-0 mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('backpack::backup.location') }}</th>
                            <th>{{ trans('backpack::backup.date') }}</th>
                            <th class="text-right">{{ trans('backpack::backup.file_size') }}</th>
                            <th class="text-right">{{ trans('backpack::backup.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($backups as $key => $backup)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $backup->diskName }}</td>
                            <td>{{ $backup->lastModified }}</td>
                            <td class="text-right">{{ $backup->fileSize }} MB</td>
                            <td class="text-right">
                                @if ($backup->downloadLink)
                                <a class="btn btn-sm btn-link" data-button-type="download" href="{{ $backup->downloadLink }}">
                                    <i class="la la-cloud-download"></i> {{ trans('backpack::backup.download') }}
                                </a>
                                @endif
                                <a class="btn btn-sm btn-link" data-button-type="delete" href="{{ $backup->deleteLink }}">
                                    <i class="la la-trash-o"></i> {{ trans('backpack::backup.delete') }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>

</div>
@endsection
@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.css" integrity="sha512-0p3K0H3S6Q4bEWZ/WmC94Tgit2ular2/n0ESdfEX8l172YyQj8re1Wu9s/HT9T/T2osUw5Gx/6pAZNk3UKbESw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    #create-new-backup-button.loading>.la-spinner {
        display: inherit;
        animation: rotation 1s steps(8, end) infinite;
    }
    #create-new-backup-button>.la-spinner,
    #create-new-backup-button.loading>.la-plus {
        display: none;
    }
    @keyframes rotation {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(359deg);
        }
    }
</style>
@stop
@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.js" integrity="sha512-lOrm9FgT1LKOJRUXF3tp6QaMorJftUjowOWiDcG5GFZ/q7ukof19V0HKx/GWzXCdt9zYju3/KhBNdCLzK8b90Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const storageKey = 'backpack.backupmanager.created';
        const createButton = document.querySelector('#create-new-backup-button');
        const deleteButtons = document.querySelectorAll('[data-button-type=delete]');
        const downloadButtons = document.querySelectorAll('[data-button-type=download]');
        const defaultHeaders = { 
            'X-CSRF-TOKEN': '{{csrf_token()}}',
            'Accept': 'application/json',
        };
        const trans = {
            create_confirmation_title: "{{ trans('backpack::backup.create_confirmation_title') }}",
            create_started_message: "{{ trans('backpack::backup.create_started_message') }}",
            create_error_title: "{{ trans('backpack::backup.create_error_title') }}",
            create_completed_title: "{{ trans('backpack::backup.create_completed_title') }}",
            download_confirmation_title: "{{ trans('backpack::backup.download_confirmation_title') }}",
            delete_error_title: "{{ trans('backpack::backup.delete_error_title') }}",
            delete_confirm: "{{ trans('backpack::backup.delete_confirm') }}",
            delete_cancel_title: "{{ trans('backpack::backup.delete_cancel_title') }}",
            delete_cancel_message: "{{ trans('backpack::backup.delete_cancel_message') }}",
            delete_confirmation_title: "{{ trans('backpack::backup.delete_confirmation_title') }}",
            delete_confirmation_message: "{{ trans('backpack::backup.delete_confirmation_message') }}",
        }
        // Noty alert helper
        const notyAlert = (title, message = '', type = 'success') => new Noty({text: `<strong>${title}</strong><br>${message}`, type}).show();
        // Set button status helper
        const setCreateButtonLoading = status => {
            createButton.classList.toggle('loading', status);
            createButton.toggleAttribute('disabled', status);
        }
        // capture the Create new backup button
        createButton.onclick = async e => {
            e.preventDefault();
            setCreateButtonLoading(true);
            notyAlert(trans.create_confirmation_title, trans.create_started_message);
            // do the backup through ajax
            try {
                let response = await fetch(createButton.getAttribute('href'), {
                    method: 'PUT', 
                    headers: defaultHeaders
                });
                let result = await response.text();
                // Show an alert with the result
                if(!response.ok || result.includes('failed')) {
                    throw new Error(result);
                }
                localStorage.setItem(storageKey, true);
                location.reload();
            }
            catch (result) {
                // Show an alert with the result
                notyAlert(trans.create_error_title, result, 'warning');
            }
            setCreateButtonLoading(false);
        }
        // capture the delete button
        deleteButtons.forEach(deleteButton => {
            deleteButton.onclick = async e => {
                e.preventDefault();
                if (!confirm(trans.delete_confirm)) {
                    return notyAlert(trans.delete_cancel_title, trans.delete_cancel_message, 'info');
                }
                try {
                    let response = await fetch(deleteButton.getAttribute('href'), {
                        method: 'DELETE', 
                        headers: defaultHeaders
                    });
                    let result = await response.text();
                    // Show an alert with the result
                    if(!response.ok) {
                        throw new Error(result);
                    }
                    notyAlert(trans.delete_confirmation_title, trans.delete_confirmation_message);
                    // delete the row from the table
                    deleteButton.closest('tr').remove();
                }
                catch (result) {
                    // Show an alert with the result
                    notyAlert(trans.delete_error_title, result, 'warning');
                }
            }
        });
        // capture the download button
        downloadButtons.forEach(downloadButton => {
            downloadButton.onclick = e => notyAlert(trans.download_confirmation_title);
        });
        // Show messages stored on session
        if(localStorage.getItem(storageKey)) {
            localStorage.removeItem(storageKey);
            notyAlert(trans.create_completed_title);
        }
    });
</script>
@stop