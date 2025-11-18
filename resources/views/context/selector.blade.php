<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختر الوحدة والمؤسسة - نظام الأباسي المحاسبي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .selector-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-section h1 {
            color: #2c3e50;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .logo-section p {
            color: #7f8c8d;
            font-size: 1rem;
        }
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-start {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .icon-box {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .icon-box i {
            font-size: 30px;
            color: white;
        }
        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="selector-card">
        <div class="logo-section">
            <div class="icon-box">
                <i class="fas fa-building"></i>
            </div>
            <h1><i class="fas fa-chart-line"></i> نظام الأباسي المحاسبي</h1>
            <p>اختر الوحدة والمؤسسة للبدء في العمل</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('context.set-unit') }}" id="contextForm">
            @csrf
            
            <!-- اختيار الوحدة -->
            <div class="mb-4">
                <label for="unit_id" class="form-label">
                    <i class="fas fa-building text-primary"></i> الوحدة (الشركة)
                </label>
                <select name="unit_id" id="unit_id" class="form-select" required>
                    <option value="">-- اختر الوحدة --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ $activeUnit && $activeUnit->id == $unit->id ? 'selected' : '' }}>
                            {{ $unit->unit_name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">اختر الوحدة (الشركة) التي تريد العمل عليها</small>
            </div>

            <!-- اختيار المؤسسة -->
            <div class="mb-4">
                <label for="company_id" class="form-label">
                    <i class="fas fa-briefcase text-success"></i> المؤسسة (النظام المحاسبي)
                </label>
                <select name="company_id" id="company_id" class="form-select" required>
                    <option value="">-- اختر المؤسسة --</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ $activeCompany && $activeCompany->id == $company->id ? 'selected' : '' }}>
                            {{ $company->company_name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">اختر المؤسسة (النظام المحاسبي) التي تريد العمل عليها</small>
            </div>

            <!-- زر البدء -->
            <button type="submit" class="btn btn-start">
                <i class="fas fa-arrow-left"></i> ابدأ العمل
            </button>
        </form>

        @if($activeUnit && $activeCompany)
            <div class="text-center mt-4">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-home"></i> الذهاب إلى لوحة التحكم
                </a>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // دالة لتحميل المؤسسات حسب الوحدة
        function loadCompaniesByUnit(unitId) {
            const companySelect = document.getElementById('company_id');
            
            companySelect.innerHTML = '<option value="">-- اختر المؤسسة --</option>';
            
            if (unitId) {
                fetch(`/api/companies-by-unit/${unitId}`)
                    .then(response => response.json())
                    .then(companies => {
                        companies.forEach(company => {
                            const option = document.createElement('option');
                            option.value = company.id;
                            option.textContent = company.company_name;
                            companySelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('خطأ في تحميل المؤسسات:', error);
                    });
            }
        }
        
        // تحديث قائمة المؤسسات عند تغيير الوحدة
        document.getElementById('unit_id').addEventListener('change', function() {
            loadCompaniesByUnit(this.value);
        });

        // تحديث action عند تغيير المؤسسة
        document.getElementById('company_id').addEventListener('change', function() {
            const form = document.getElementById('contextForm');
            if (this.value) {
                form.action = '{{ route("context.set-company") }}';
            } else {
                form.action = '{{ route("context.set-unit") }}';
            }
        });
        
        // تحميل المؤسسات عند تحميل الصفحة إذا كانت هناك وحدة محددة
        window.addEventListener('DOMContentLoaded', function() {
            const unitSelect = document.getElementById('unit_id');
            if (unitSelect.value) {
                loadCompaniesByUnit(unitSelect.value);
            }
        });
    </script>
</body>
</html>
