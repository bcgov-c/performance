<aside class="main-sidebar {{ config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4') }}">

    {{-- Sidebar brand logo --}}
    @if(config('adminlte.logo_img_xl'))
        @include('adminlte::partials.common.brand-logo-xl')
    @else
        @include('adminlte::partials.common.brand-logo-xs')
    @endif
    <div id="sidebar-profile-picture" class="text-center my-3" >
        <div class="d-flex flex-column align-items-center">
            <!-- comment by James Poon on 2021-Dec-03 
            <img src="{{asset('img/profile-pic.png')}}" alt="" class="rounded-circle" style="max-width:90px; max-height:90px">
            -->
            <!-- added by James Poon on 2021-Dec-03 -->
            @if( session('profilePhoto') )
                <span>
                    <img style="width: 90px; height: 90px; border-radius:50%;" src="data:image/jpeg;base64,{{ session('profilePhoto') }}" alt="Profile Picture">
                </span>
            @else
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72" fill="white" class="bi bi-person-circle" viewBox="0 0 16 16"  alt="Profile Picture">
                        <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                        <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                    </svg>
                </span>
            @endif

            <div class="text-white">
                {{ Auth::user()->name }}
            </div>
        </div>
    </div>

    {{-- Sidebar menu --}}
    <div class="sidebar">
        <nav class="pt-2">
            <ul class="nav nav-pills nav-sidebar flex-column {{ config('adminlte.classes_sidebar_nav', '') }}"
                data-widget="treeview" role="menu"
                @if(config('adminlte.sidebar_nav_animation_speed') != 300)
                    data-animation-speed="{{ config('adminlte.sidebar_nav_animation_speed') }}"
                @endif
                @if(!config('adminlte.sidebar_nav_accordion'))
                    data-accordion="false"
                @endif>
                {{-- Configured sidebar links --}}
                @each('adminlte::partials.sidebar.menu-item', $adminlte->menu('sidebar'), 'item')
            </ul>
        </nav>
    </div>

    <span class="d-flex flex-column align-items-center  py-5">
        <img src="{{asset('img/brand/WhereIdeasWork.png')}}" alt="" style="max-width:200px; max-height:200px">
    </span>

</aside>
