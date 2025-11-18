<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>القاعدة المركزية - نظام Alabasi</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header .actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .header .user-info {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .header a, .header button {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .header a:hover, .header button:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            color: #667eea;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #999;
            font-size: 12px;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .chart-card h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-header h2 {
            color: #333;
            font-size: 20px;
        }
        
        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filters select, .filters input {
            padding: 8px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 2px solid #e0e0e0;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: right;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #e0e0e0;
            font-size: 14px;
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .filters select, .filters input {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>
            <span>🏢</span>
            <span>القاعدة المركزية - نظام Alabasi</span>
        </h1>
        <div class="actions">
            <span class="user-info">📍 {{ session('unit_name') }}</span>
            <a href="/clearing-transactions/create">➕ تحويل جديد</a>
            <a href="/clearing-transactions/report">📊 تقرير الحسابات الوسيطة</a>
            <a href="/logout">🚪 خروج</a>
        </div>
    </div>
    
    <div class="container">
        <!-- الإحصائيات الرئيسية -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>📊 إجمالي الوحدات</h3>
                <div class="number">{{ $total_units }}</div>
                <div class="label">وحدة نشطة</div>
            </div>
            
            <div class="stat-card">
                <h3>🏪 إجمالي المؤسسات</h3>
                <div class="number">{{ $total_companies }}</div>
                <div class="label">مؤسسة مسجلة</div>
            </div>
            
            <div class="stat-card">
                <h3>🔄 إجمالي التحويلات</h3>
                <div class="number">{{ $total_transfers }}</div>
                <div class="label">تحويل</div>
            </div>
            
            <div class="stat-card">
                <h3>✅ التحويلات المكتملة</h3>
                <div class="number">{{ $completed_transfers }}</div>
                <div class="label">تحويل مكتمل</div>
            </div>
            
            <div class="stat-card">
                <h3>⏳ التحويلات المعلقة</h3>
                <div class="number">{{ $pending_transfers }}</div>
                <div class="label">تحويل معلق</div>
            </div>
            
            <div class="stat-card">
                <h3>💰 إجمالي المبالغ</h3>
                <div class="number">{{ number_format($total_amount, 0) }}</div>
                <div class="label">ريال يمني</div>
            </div>
        </div>
        
        <!-- الرسوم البيانية -->
        <div class="charts-grid">
            <div class="chart-card">
                <h2>📈 التحويلات حسب الشهر</h2>
                <canvas id="monthlyChart"></canvas>
            </div>
            
            <div class="chart-card">
                <h2>📊 التحويلات حسب النوع</h2>
                <canvas id="typeChart"></canvas>
            </div>
        </div>
        
        <!-- آخر التحويلات -->
        <div class="section">
            <div class="section-header">
                <h2>🔄 آخر التحويلات</h2>
                <div class="filters">
                    <select id="statusFilter">
                        <option value="">جميع الحالات</option>
                        <option value="completed">مكتمل</option>
                        <option value="pending">معلق</option>
                    </select>
                    <select id="typeFilter">
                        <option value="">جميع الأنواع</option>
                        <option value="inter_company">بين مؤسسات</option>
                        <option value="inter_unit">بين وحدات</option>
                    </select>
                    <input type="date" id="dateFilter" placeholder="التاريخ">
                    <button class="btn btn-secondary" onclick="applyFilters()">🔍 بحث</button>
                    <button class="btn btn-secondary" onclick="resetFilters()">🔄 إعادة تعيين</button>
                </div>
            </div>
            
            @if($recent_transfers->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>من</th>
                            <th>إلى</th>
                            <th>المبلغ</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="transfersTable">
                        @foreach($recent_transfers as $transfer)
                            <tr>
                                <td>{{ $transfer->id }}</td>
                                <td>{{ $transfer->sourceCompany->name ?? 'غير معروف' }}</td>
                                <td>{{ $transfer->targetCompany->name ?? 'غير معروف' }}</td>
                                <td><strong>{{ number_format($transfer->amount, 0) }}</strong> ر.ي</td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $transfer->type === 'inter_company' ? 'بين مؤسسات' : 'بين وحدات' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $transfer->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ $transfer->status === 'completed' ? 'مكتمل' : 'معلق' }}
                                    </span>
                                </td>
                                <td>{{ $transfer->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <button class="btn btn-secondary" onclick="viewDetails({{ $transfer->id }})">👁️ عرض</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <p>لا توجد تحويلات بعد</p>
                </div>
            @endif
        </div>
    </div>
    
    <script>
        // بيانات الرسوم البيانية
        const monthlyData = @json($monthly_stats);
        const typeData = @json($type_stats);
        
        // رسم بياني للتحويلات حسب الشهر
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(d => d.month),
                datasets: [{
                    label: 'عدد التحويلات',
                    data: monthlyData.map(d => d.count),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // رسم بياني للتحويلات حسب النوع
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: typeData.map(d => d.type === 'inter_company' ? 'بين مؤسسات' : 'بين وحدات'),
                datasets: [{
                    data: typeData.map(d => d.count),
                    backgroundColor: ['#667eea', '#764ba2'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // دوال الفلاتر
        function applyFilters() {
            const status = document.getElementById('statusFilter').value;
            const type = document.getElementById('typeFilter').value;
            const date = document.getElementById('dateFilter').value;
            
            // هنا يمكن إضافة AJAX لتحميل البيانات المفلترة
            console.log('Filters:', { status, type, date });
        }
        
        function resetFilters() {
            document.getElementById('statusFilter').value = '';
            document.getElementById('typeFilter').value = '';
            document.getElementById('dateFilter').value = '';
            location.reload();
        }
        
        function viewDetails(id) {
            window.location.href = '/clearing-transactions/' + id;
        }
    </script>
</body>
</html>
