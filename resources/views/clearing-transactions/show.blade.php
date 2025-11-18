@extends('layouts.app')

@section('title', 'تفاصيل التحويل')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exchange-alt"></i>
                        تفاصيل التحويل #{{ $transaction->id }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('clearing-transactions.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-right"></i> رجوع
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-arrow-up text-danger"></i> من (المصدر)</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">الوحدة:</th>
                                            <td>{{ $transaction->sourceUnit->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>المؤسسة:</th>
                                            <td>{{ $transaction->sourceCompany->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>رقم القيد:</th>
                                            <td>
                                                @if($transaction->source_journal_entry_id)
                                                    <span class="badge badge-info">{{ $transaction->source_journal_entry_id }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-arrow-down text-success"></i> إلى (الهدف)</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <th width="40%">الوحدة:</th>
                                            <td>{{ $transaction->targetUnit->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>المؤسسة:</th>
                                            <td>{{ $transaction->targetCompany->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>رقم القيد:</th>
                                            <td>
                                                @if($transaction->target_journal_entry_id)
                                                    <span class="badge badge-info">{{ $transaction->target_journal_entry_id }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> معلومات التحويل</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="20%">المبلغ:</th>
                                            <td>
                                                <h4 class="mb-0">
                                                    <strong class="text-primary">{{ number_format($transaction->amount, 2) }}</strong> ريال يمني
                                                </h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>نوع التحويل:</th>
                                            <td>
                                                @if($transaction->type === 'inter_company')
                                                    <span class="badge badge-info">تحويل بين مؤسسات</span>
                                                @else
                                                    <span class="badge badge-warning">تحويل بين وحدات</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الحالة:</th>
                                            <td>
                                                @if($transaction->status === 'completed')
                                                    <span class="badge badge-success badge-lg">
                                                        <i class="fas fa-check-circle"></i> مكتمل
                                                    </span>
                                                @elseif($transaction->status === 'pending')
                                                    <span class="badge badge-warning badge-lg">
                                                        <i class="fas fa-clock"></i> قيد الانتظار
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger badge-lg">
                                                        <i class="fas fa-times-circle"></i> ملغي
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الوصف:</th>
                                            <td>{{ $transaction->description }}</td>
                                        </tr>
                                        <tr>
                                            <th>تاريخ الإنشاء:</th>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @if($transaction->completed_at)
                                        <tr>
                                            <th>تاريخ الإكمال:</th>
                                            <td>{{ $transaction->completed_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
