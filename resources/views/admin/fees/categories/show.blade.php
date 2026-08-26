@extends('layouts.master')

@section('title', 'Fee Category Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tag mr-2"></i>{{ $category->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('fee-categories.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('fee-categories.edit', $category->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">Name</th>
                            <td><strong>{{ $category->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Code</th>
                            <td>
                                @if($category->code)
                                    <span class="badge badge-info badge-lg">{{ $category->code }}</span>
                                @else
                                    <span class="text-muted">No code set</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $category->description ?? 'No description provided.' }}</td>
                        </tr>
                        <tr>
                            <th>Sort Order</th>
                            <td>{{ $category->sort_order }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge badge-pill {{ $category->is_active ? 'badge-success' : 'badge-danger' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td>{{ $category->created_at->format('F d, Y H:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $category->updated_at->format('F d, Y H:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Usage Statistics</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 text-primary">
                        {{ $category->feeItems()->count() }}
                    </div>
                    <p class="text-muted">Fee Items using this category</p>
                    
                    @if($category->feeItems()->count() > 0)
                        <hr>
                        <h6>Recent Fee Items</h6>
                        <ul class="list-unstyled text-left">
                            @foreach($category->feeItems()->latest()->limit(5)->get() as $item)
                                <li class="border-bottom py-2">
                                    <i class="fas fa-file-invoice text-primary mr-2"></i>
                                    {{ $item->name }}
                                    <span class="float-right text-muted">
                                        GHC {{ number_format($item->amount, 2) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection