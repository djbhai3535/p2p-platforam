@extends('layouts.dashboard')

@section('title', 'My P2P Advertisements')

@section('content')
<div class="glass-card">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="card-title-custom mb-0">My P2P Advertisements</h3>
            <p class="text-muted-custom small mb-0">Manage your active buy and sell ads posted on the exchange.</p>
        </div>
        <div>
            <a href="{{ route('advertisements.create') }}" class="btn btn-premium px-4">+ Post Ad</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle border-0 mb-0">
            <thead>
                <tr class="text-muted-custom small">
                    <th>Ad ID</th>
                    <th>Type</th>
                    <th>Rate</th>
                    <th>Amount USDT</th>
                    <th>Limits</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @if($ads->isEmpty())
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted-custom">
                            You have not posted any P2P advertisements yet.
                        </td>
                    </tr>
                @else
                    @foreach($ads as $ad)
                        <tr>
                            <td>#{{ substr($ad->id, 0, 8) }}</td>
                            <td>
                                <span class="badge {{ $ad->type === 'buy' ? 'bg-success' : 'bg-info' }} text-capitalize">
                                    {{ $ad->type }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold">{{ number_format($ad->rate, 2) }}</span> {{ $ad->country->currency_code }}
                            </td>
                            <td>{{ number_format($ad->amount, 8) }} USDT</td>
                            <td>{{ number_format($ad->min_limit, 2) }} - {{ number_format($ad->max_limit, 2) }} {{ $ad->country->currency_code }}</td>
                            <td>
                                <span class="badge {{ $ad->status === 'active' ? 'bg-success' : 'bg-warning' }}">
                                    {{ ucfirst($ad->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('advertisements.toggle', $ad->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-light me-2">
                                        {{ $ad->status === 'active' ? 'Pause' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
