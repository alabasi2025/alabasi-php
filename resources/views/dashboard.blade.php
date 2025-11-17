@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="jumbotron bg-light p-5 rounded text-center">
                <h1 class="display-3">🎉 مرحباً بك في نظام الأباسي المحاسبي</h1>
                <p class="lead mt-3">تم تحويل النظام إلى Laravel Framework بنجاح!</p>
                <hr class="my-4">
                <p class="mb-4">النظام الآن جاهز للتطوير والاختبار على السحابة</p>
                <div class="btn-group" role="group">
                    <a class="btn btn-primary btn-lg mx-2" href="{{ route('accounts.index') }}">
                        <i class="bi bi-list-ul"></i> دليل الحسابات
                    </a>
                    <a class="btn btn-success btn-lg mx-2" href="{{ route('journal-entries.index') }}">
                        <i class="bi bi-journal-text"></i> القيود اليومية
                    </a>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-md-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h3 class="text-success">✅</h3>
                            <h5 class="card-title">Laravel Framework</h5>
                            <p class="card-text">تم إنشاء المشروع باستخدام Laravel 10</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h3 class="text-success">✅</h3>
                            <h5 class="card-title">قاعدة البيانات</h5>
                            <p class="card-text">الاتصال بقاعدة البيانات يعمل بنجاح</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h3 class="text-success">✅</h3>
                            <h5 class="card-title">Models & Controllers</h5>
                            <p class="card-text">تم إنشاء Models و Controllers الأساسية</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4" role="alert">
                <h5 class="alert-heading"><i class="bi bi-info-circle"></i> ملاحظة</h5>
                <p>هذا النظام في مرحلة التطوير الأولية. سيتم إضافة المزيد من الميزات قريباً.</p>
            </div>
        </div>
    </div>
</div>
@endsection
