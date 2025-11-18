@extends('layouts.app')

@section('title', 'التحويلات بين المؤسسات والوحدات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="fas fa-exchange-alt"></i>
                        {{ $isMain ? 'جميع التحويلات' : 'التحويلات الخاصة بالوحدة' }}
                    </h3>
                    @if(!$isMain)
                    <a href="{{ route('clearing-transactions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> تحويل جديد
                    </a>
                    @endif
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">التاريخ</th>
                                    <th width="20%">من</th>
                                    <th width="20%">إلى</th>
                                    <th width="10%">المبلغ</th>
                                    <th width="10%">النوع</th>
                                    <th width="10%">الحالة</th>
                                    <th width="10%">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->id }}</td>
                                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <strong>{{ $transaction->sourceCompany->name }}</strong><br>
                                        <small class="text-muted">{{ $transaction->sourceUnit->name }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $transaction->targetCompany->name }}</strong><br>
                                        <small class="text-muted">{{ $transaction->targetUnit->name }}</small>
                                    </td>
                                    <td class="text-right">
                                        <strong>{{ number_format($transaction->amount, 2) }}</strong> ر.ي
                                    </td>
                                    <td>
                                        @if($transaction->type === 'inter_company')
                                            <span class="badge badge-info">بين مؤسسات</span>
                                        @else
                                            <span class="badge badge-warning">بين وحدات</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->status === 'completed')
                                            <span class="badge badge-success">مكتمل</span>
                                        @elseif($transaction->status === 'pending')
                                            <span class="badge badge-warning">قيد الانتظار</span>
                                        @else
                                            <span class="badge badge-danger">ملغي</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('clearing-transactions.show', $transaction->id) }}" 
                                           class="btn btn-sm btn-info" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>لا توجد تحويلات حتى الآن</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
