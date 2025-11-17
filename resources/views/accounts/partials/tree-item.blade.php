<div class="account-item {{ $account->is_main ? 'main-account' : 'sub-account' }}" style="margin-right: {{ $level * 30 }}px;">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1">
            <span class="account-code">{{ $account->account_code }}</span>
            <span class="account-name">- {{ $account->name }}</span>
            
            {{-- Badges --}}
            @if($account->is_main)
                <span class="badge badge-primary account-type-badge">رئيسي</span>
            @else
                <span class="badge badge-success account-type-badge">فرعي</span>
            @endif

            @if($account->accountType)
                <span class="badge badge-info account-type-badge">{{ $account->accountType->name }}</span>
            @endif

            @if($account->analyticalAccountType)
                <span class="badge badge-warning account-type-badge">
                    <i class="fas fa-tag"></i> {{ $account->analyticalAccountType->name }}
                </span>
            @endif

            @if(!$account->is_active)
                <span class="badge badge-secondary account-type-badge">غير نشط</span>
            @endif
        </div>

        <div class="btn-group btn-group-sm">
            <a href="{{ route('accounts.create', ['parent_id' => $account->id]) }}" 
               class="btn btn-sm btn-info" 
               title="إضافة حساب فرعي">
                <i class="fas fa-plus"></i>
            </a>
            <a href="{{ route('accounts.edit', $account) }}" 
               class="btn btn-sm btn-warning" 
               title="تعديل">
                <i class="fas fa-edit"></i>
            </a>
            <form action="{{ route('accounts.destroy', $account) }}" 
                  method="POST" 
                  class="d-inline" 
                  onsubmit="return confirm('هل أنت متأكد من حذف هذا الحساب؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>

    @if($account->description)
        <div class="mt-2 text-muted small">
            <i class="fas fa-info-circle"></i> {{ $account->description }}
        </div>
    @endif
</div>

{{-- Render children recursively --}}
@if($account->children && $account->children->count() > 0)
    <div class="account-children">
        @foreach($account->children as $child)
            @include('accounts.partials.tree-item', ['account' => $child, 'level' => $level + 1])
        @endforeach
    </div>
@endif
