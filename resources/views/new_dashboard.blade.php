@php
$is_main = session('is_main', false);
$database = session('database', 'main');

use App\Models\Main\ClearingTransaction;
use App\Models\Unit\Account;
use App\Models\Unit\JournalEntry;

if ($is_main) {
    $total_transfers = ClearingTransaction::count();
    $completed_transfers = ClearingTransaction::where('status', 'completed')->count();
    $total_amount = ClearingTransaction::where('status', 'completed')->sum('amount');
    $recent_transfers = ClearingTransaction::orderBy('created_at', 'desc')->limit(10)->get();
} else {
    $company_id = session('company_id');
    $total_accounts = Account::on($database)->where('company_id', $company_id)->count();
    $total_entries = JournalEntry::on($database)->where('company_id', $company_id)->count();
    $posted_entries = JournalEntry::on($database)->where('company_id', $company_id)->where('is_posted', true)->count();
    $recent_entries = JournalEntry::on($database)
        ->where('company_id', $company_id)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
}
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - نظام Alabasi</title>
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
        }
        
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .header .user-info span {
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .header a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            transition: background 0.3s;
        }
        
        .header a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-right: 4px solid #667eea;
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
        }
        
        .section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: right;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #e0e0e0;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
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
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏢 نظام Alabasi المحاسبي</h1>
        <div class="user-info">
            <span>📍 {{ session('unit_name') }}</span>
            @if(!$is_main)
                <span>🏪 {{ session('company_name') }}</span>
            @endif
            <a href="/logout">🚪 خروج</a>
        </div>
    </div>
    
    <div class="container">
        @if($is_main)
            <!-- القاعدة المركزية -->
            <div class="stats">
                <div class="stat-card">
                    <h3>إجمالي التحويلات</h3>
                    <div class="number">{{ $total_transfers }}</div>
                </div>
                <div class="stat-card">
                    <h3>التحويلات المكتملة</h3>
                    <div class="number">{{ $completed_transfers }}</div>
                </div>
                <div class="stat-card">
                    <h3>إجمالي المبالغ المحولة</h3>
                    <div class="number">{{ number_format($total_amount, 0) }} ر.ي</div>
                </div>
            </div>
            
            <div class="section">
                <h2>📊 آخر التحويلات</h2>
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
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_transfers as $transfer)
                                <tr>
                                    <td>{{ $transfer->id }}</td>
                                    <td>{{ $transfer->sourceCompany->name ?? 'غير معروف' }}</td>
                                    <td>{{ $transfer->targetCompany->name ?? 'غير معروف' }}</td>
                                    <td>{{ number_format($transfer->amount, 0) }} ر.ي</td>
                                    <td>{{ $transfer->type === 'inter_company' ? 'بين مؤسسات' : 'بين وحدات' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $transfer->status === 'completed' ? 'success' : 'warning' }}">
                                            {{ $transfer->status === 'completed' ? 'مكتمل' : 'معلق' }}
                                        </span>
                                    </td>
                                    <td>{{ $transfer->created_at->format('Y-m-d H:i') }}</td>
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
            
        @else
            <!-- وحدة عمل -->
            <div class="stats">
                <div class="stat-card">
                    <h3>عدد الحسابات</h3>
                    <div class="number">{{ $total_accounts }}</div>
                </div>
                <div class="stat-card">
                    <h3>إجمالي القيود</h3>
                    <div class="number">{{ $total_entries }}</div>
                </div>
                <div class="stat-card">
                    <h3>القيود المرحلة</h3>
                    <div class="number">{{ $posted_entries }}</div>
                </div>
            </div>
            
            <div class="section">
                <h2>📝 آخر القيود</h2>
                @if($recent_entries->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>رقم القيد</th>
                                <th>التاريخ</th>
                                <th>الوصف</th>
                                <th>النوع</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_entries as $entry)
                                <tr>
                                    <td>{{ $entry->entry_number }}</td>
                                    <td>{{ $entry->entry_date }}</td>
                                    <td>{{ $entry->description }}</td>
                                    <td>{{ $entry->is_clearing_entry ? '🔄 تحويل' : '📝 عادي' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $entry->is_posted ? 'success' : 'warning' }}">
                                            {{ $entry->is_posted ? 'مرحل' : 'غير مرحل' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <p>لا توجد قيود بعد</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</body>
</html>
