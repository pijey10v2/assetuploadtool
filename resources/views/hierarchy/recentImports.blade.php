@extends('layouts.app')

@section('content')

<div class="container">

    <h2>Recent Uploads</h2>

    <div class="card mt-3">

        <div class="card-header">
            Import Batches
        </div>

        <div class="card-body">

            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th>Import Batch No.</th>
                        <th>Date Created</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($imports as $import)

                    <tr>

                        <td>
                            {{ $import['c_import_batch'] }}
                        </td>

                        <td>
                            {{ $import['dateCreated'] }}
                        </td>

                        <td>

                            <a
                                href="{{ url('/hierarchy/setupHierarchy?import_batch=' . urlencode($import['c_import_batch'])) }}"
                                class="btn btn-primary btn-sm">

                                Setup Hierarchy

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="3" class="text-center">
                            No uploads found.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection