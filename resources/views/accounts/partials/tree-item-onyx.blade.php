<div class="tree-item" 
     data-account-id="{{ $account->id }}" 
     data-level="{{ $level }}"
     style="padding-right: {{ $level * 20 }}px;"
     onclick="selectAccount({{ $account->id }})">
    
    @if($account->children && $account->children->count() > 0)
        <i id="icon-{{ $account->id }}" 
           class="fas fa-plus-square toggle-icon" 
           onclick="event.stopPropagation(); toggleChildren({{ $account->id }})"></i>
    @else
        <span class="toggle-icon"></span>
    @endif
    
    <div class="account-icon">
        @if($level == 0)
            <i class="fas fa-folder"></i>
        @elseif($level == 1)
            <i class="fas fa-folder-open"></i>
        @elseif($account->allow_posting)
            <i class="fas fa-file-invoice-dollar"></i>
        @else
            <i class="fas fa-folder"></i>
        @endif
    </div>
    
    <span class="account-code">{{ $account->code }}</span>
    <span class="account-name">{{ $account->name_ar }}</span>
    
    @if($account->is_parent)
        <span class="badge bg-secondary account-badge">رئيسي</span>
    @else
        <span class="badge bg-primary account-badge">فرعي</span>
    @endif
    
    @if($account->analytical_type)
        <span class="badge bg-info account-badge">{{ $account->analytical_type }}</span>
    @endif
    
    @if($account->allow_posting)
        <span class="badge bg-success account-badge">قابل للترحيل</span>
    @endif
</div>

@if($account->children && $account->children->count() > 0)
    <div id="children-{{ $account->id }}" class="tree-children">
        @foreach($account->children as $child)
            @include('accounts.partials.tree-item-onyx', ['account' => $child, 'level' => $level + 1])
        @endforeach
    </div>
@endif
