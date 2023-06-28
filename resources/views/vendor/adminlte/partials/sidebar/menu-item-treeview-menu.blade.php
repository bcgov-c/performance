@if(!session()->has('view-profile-as'))
<?php 
$display_menu = true;

if (session('sr_user') && $item['id'] == 'sys-admin-menu'){
    $display_menu = false;
} 
?>
@if($display_menu)
<li @isset($item['id']) id="{{ $item['id'] }}" @endisset class="nav-item has-treeview {{ $item['submenu_class'] }}">

    {{-- Menu toggler --}}
    
    @if(session()->has('SR_ALLOWED') && $item['text'] == 'My Team')
        <a class="nav-link py-3 {{ $item['class'] }} @isset($item['shift']) {{ $item['shift'] }} @endisset"
        href="{{ array_key_exists('url', $item) ? url($item['url']) : '' }}" {!! $item['data-compiled'] ?? '' !!}>

            <i class="mr-2 {{ $item['icon'] ?? 'far fa-fw fa-circle' }} {{
                isset($item['icon_color']) ? 'text-'.$item['icon_color'] : ''
            }}"></i>

            <p>
                {{ $item['text'] }}
                <i class="py-2 fas fa-angle-left right"></i>

                @isset($item['label'])
                    <span class="badge badge-{{ $item['label_color'] ?? 'primary' }} right">
                        {{ $item['label'] }}
                    </span>
                @endisset
            </p>
        </a>
    @elseif(!session()->has('SR_ALLOWED') )
        <a class="nav-link py-3 {{ $item['class'] }} @isset($item['shift']) {{ $item['shift'] }} @endisset"
             href="{{ array_key_exists('url', $item) ? url($item['url']) : '' }}" {!! $item['data-compiled'] ?? '' !!}>

            <i class="mr-2 {{ $item['icon'] ?? 'far fa-fw fa-circle' }} {{
                isset($item['icon_color']) ? 'text-'.$item['icon_color'] : ''
            }}"></i>

            <p>
                {{ $item['text'] }}
                <i class="py-2 fas fa-angle-left right"></i>

                @isset($item['label'])
                    <span class="badge badge-{{ $item['label_color'] ?? 'primary' }} right">
                        {{ $item['label'] }}
                    </span>
                @endisset
            </p>
        </a>
    @endif

    {{-- Menu items --}}
    <ul class="nav nav-treeview">
        @each('adminlte::partials.sidebar.menu-item', $item['submenu'], 'item')
    </ul>

</li>
@endif
@endif