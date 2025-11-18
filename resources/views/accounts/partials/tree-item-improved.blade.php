@php
    // Determine icon based on account level and type
    $icon = 'fa-folder';
    if ($level == 0) {
        $icon = 'fa-layer-group';
    } elseif ($level == 1) {
        $icon = 'fa-folder-open';
    } elseif ($level == 2) {
        $icon = 'fa-file-invoice-dollar';
    } elseif ($level >= 3) {
        $icon = 'fa-file-alt';
    }
    
    // Special icons based on account type
    if ($account->analyticalAccountType) {
        switch($account->analyticalAccountType->name) {
            case 'بنك':
                $icon = 'fa-university';
                break;
            case 'صندوق':
                $icon = 'fa-cash-register';
                break;
            case 'صراف':
                $icon = 'fa-wallet';
                break;
            case 'محفظة':
                $icon = 'fa-money-bill-wave';
                break;
        }
    }
    
    $hasChildren = $account->children && $account->children->count() > 0;
@endphp

<div class="account-item level-{{ $level }}" data-account-id="{{ $account->id }}">
    <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1 d-flex align-items-center">
            {{-- Toggle Icon for accounts with children --}}
            @if($hasChildren)
                <span class="account-toggle" data-account-id="{{ $account->id }}">
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </span>
            @else
                <span style="width: 34px; display: inline-block;"></span>
            @endif
            
            {{-- Account Icon --}}
            <span class="account-icon">
                <i class="fas {{ $icon }}"></i>
            </span>
            
            {{-- Account Code --}}
            <span class="account-code">{{ $account->code ?? $account->account_code }}</span>
            
            {{-- Account Name --}}
            <span class="account-name">{{ $account->name_ar ?? $account->name }}</span>
            
            {{-- Badges --}}
            <div class="d-inline-block ms-2">
                @if($account->is_parent)
                    <span class="badge bg-light text-dark account-type-badge">
                        <i class="fas fa-sitemap"></i> رئيسي
                    </span>
                @else
                    <span class="badge bg-light text-dark account-type-badge">
                        <i class="fas fa-file"></i> فرعي
                    </span>
                @endif

                @if($account->accountType)
                    <span class="badge bg-light text-dark account-type-badge">
                        <i class="fas fa-tag"></i> {{ $account->accountType->name }}
                    </span>
                @endif

                @if($account->analyticalAccountType)
                    <span class="badge bg-warning text-dark account-type-badge">
                        <i class="fas fa-star"></i> {{ $account->analyticalAccountType->name }}
                    </span>
                @endif

                @if(!$account->is_active)
                    <span class="badge bg-secondary account-type-badge">
                        <i class="fas fa-ban"></i> غير نشط
                    </span>
                @endif
                
                @if($account->allow_posting)
                    <span class="badge bg-success account-type-badge">
                        <i class="fas fa-check-circle"></i> قابل للترحيل
                    </span>
                @endif
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="action-buttons">
            <a href="{{ route('accounts.create', ['parent_id' => $account->id]) }}" 
               class="btn btn-sm btn-info btn-action" 
               title="إضافة حساب فرعي">
                <i class="fas fa-plus"></i>
            </a>
            <a href="{{ route('accounts.edit', $account) }}" 
               class="btn btn-sm btn-warning btn-action" 
               title="تعديل">
                <i class="fas fa-edit"></i>
            </a>
            <form action="{{ route('accounts.destroy', $account) }}" 
                  method="POST" 
                  class="d-inline" 
                  onsubmit="return confirm('هل أنت متأكد من حذف هذا الحساب؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger btn-action" title="حذف">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Description --}}
    @if($account->description)
        <div class="account-description">
            <i class="fas fa-info-circle me-2"></i>{{ $account->description }}
        </div>
    @endif
</div>

{{-- Render children recursively --}}
@if($hasChildren)
    <div class="account-children" id="children-{{ $account->id }}">
        @foreach($account->children as $child)
            @include('accounts.partials.tree-item-improved', ['account' => $child, 'level' => $level + 1])
        @endforeach
    </div>
@endif
