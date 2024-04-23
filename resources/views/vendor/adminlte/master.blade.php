<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv='cache-control' content='no-cache'>
    <meta http-equiv='expires' content='0'>
    <meta http-equiv='pragma' content='no-cache'>

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets --}}
    @if(!config('adminlte.enabled_laravel_mix_css'))
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">

        {{-- Configured Stylesheets --}}
        @include('adminlte::plugins', ['type' => 'css'])

        <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    @else
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
        
        @include('adminlte::plugins', ['type' => 'css'])
    @endif

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(app()->version() >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon --}}
    @if(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    @endif

</head>

<body class="@yield('classes_body')" @yield('body_data') data-panel-auto-height="{{session()->has('view-profile-as') ? -63 : 0}}">
    @if(session()->has('view-profile-as'))
    <div class="top-message-bar p-3 text-center bg-warning d-flex justify-content-center align-items-center sticky-top">
        <span class="flex-fill"></span>
        <span>
            <i class="icon fas fa-exclamation-circle"></i> 
            @if($viewingProfileAs)
                You are viewing {{$viewingProfileAs->name}}'s profile. Click "Return to My Profile" to go back to your own.
            @else
                Click "Return to My Profile" to go back to your own.
            @endif
        </span>
        <span class="flex-fill"></span>

        <div class="form-inline" style="position:absolute; right:0">
            <x-button :href="route('my-team.return-to-my-view')" size="sm" style="light" class="mx-2">Return to my profile</x-button>
        </div>
    </div>
    
    <div class="top-message-bar p-3 text-center bg-warning d-flex justify-content-center align-items-center fixed-top">
        <span class="flex-fill"></span>
        <span>
            <i class="icon fas fa-exclamation-circle"></i> 
            @if($viewingProfileAs)
                You are viewing {{$viewingProfileAs->name}}'s profile. Click "Return to My Profile" to go back to your own.
            @else
                Click "Return to My Profile" to go back to your own.
            @endif
        </span>
        <span class="flex-fill"></span>

        <div class="form-inline" style="position:absolute; right:0">
            <x-button :href="route('my-team.return-to-my-view')" size="sm" style="light" class="mx-2">Return to my profile</x-button>
        </div>
    </div>
    @endif
    
    @if(session()->has('user_is_switched') && !session()->has('view-profile-as'))
    <div class="top-message-bar p-3 text-center bg-warning d-flex justify-content-center align-items-center sticky-top ">
        <span class="flex-fill"></span>
        <span>
            <i class="icon fas fa-exclamation-circle"></i> You are logged in as {{auth()->user()->name}}'s account. Click "Revert Identity" to return to your own profile.
        </span>
        <span class="flex-fill"></span>

        <div class="form-inline" style="position:absolute; right:0">
            <x-button :href="route('dashboard.revert-identity')" size="sm" style="light" class="mx-2">Revert Identity</x-button>
        </div>
    </div>
    
    <div class="top-message-bar p-3 text-center bg-warning d-flex justify-content-center align-items-center fixed-top">
        <span class="flex-fill"></span>
        <span>
            <i class="icon fas fa-exclamation-circle"></i> You are logged in as {{auth()->user()->name}}'s account. Click "Revert Identity" to return to your own profile.
        </span>
        <span class="flex-fill"></span>

        <div class="form-inline" style="position:absolute; right:0">
            <x-button :href="route('dashboard.revert-identity')" size="sm" style="light" class="mx-2">Revert Identity</x-button>
        </div>
    </div>
    @endif
    
    
    {{-- Body Content --}}
    @yield('body')
    {{-- Base Scripts --}}
    @if(!config('adminlte.enabled_laravel_mix_js'))
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>

        {{-- Configured Scripts --}}
        @include('adminlte::plugins', ['type' => 'js'])

        <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @else
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @endif

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(app()->version() >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Custom Scripts --}}
    @yield('adminlte_js')
    <script>
        var large_device = true;
        $(document).ready(function() {
            // Get the button element
            var menuToggleBtn = $('.main-sidebar');

            // Initially set the aria-label
            menuToggleBtn.attr('aria-label', 'This button will hide the left menu bar');
            var isExpanded = false;

            // Add click event listener to toggle aria-label
            menuToggleBtn.on('click', function() {
                // Update the aria-label based on the state
                if (isExpanded) {
                    menuToggleBtn.attr('aria-label', 'This button will hide the hidden left menu bar');
                    isExpanded = false;
                } else {
                    menuToggleBtn.attr('aria-label', 'This button will display the hidden left menu bar');
                    isExpanded = true;
                }
            });


            if ($('body').hasClass('sidebar-closed')) {
                large_device = false;
            } else {
                large_device = true;
            }
        });


        /* $(document).on('change', '#view-profile-as', function () {
            const url = '{{ route('my-team.view-profile-as', '')}}';
            window.location = url + "/" + $(this).val();
        }); */
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.config.disableNativeSpellChecker = false;
        }
    </script>

    <script>
        // Hide the Profile Picture when the main sidebar collapse
        $(function() { 
            $(document).on('shown.lte.pushmenu', function()  {
                $('div#sidebar-profile-picture').show(100);
            }).on('collapsed.lte.pushmenu', function() {
                $('div#sidebar-profile-picture').hide(100);
            });
        });
    </script>
        

    <script>
        // Check session expiration status every minute (adjust as needed)
        const minutes = 120;
        const SessionTime = 1000 * 60 * minutes;
        setInterval(checkSessionExpiration, SessionTime);

        function checkSessionExpiration() {
            $.ajax({
                url: '/check-session-expiration', // Replace with the actual route to check session expiration
                type: 'GET',
                success: function(response) {
                    if (response.sessionExpired) {
                        window.location.href = '/session-expired';
                    }
                }
            });
        }
    </script>

    <script>
        // <!-- Snowplow starts plowing - Standalone vE.2.14.0 -->
        var collector = '{{ env('SNOWPLOW_COLLECTOR') }}';
        if(collector != null && collector != ''){
            ;(function(p,l,o,w,i,n,g){if(!p[i]){p.GlobalSnowplowNamespace=p.GlobalSnowplowNamespace||[];
            p.GlobalSnowplowNamespace.push(i);p[i]=function(){(p[i].q=p[i].q||[]).push(arguments)
            };p[i].q=p[i].q||[];n=l.createElement(o);g=l.getElementsByTagName(o)[0];n.async=1;
            n.src=w;g.parentNode.insertBefore(n,g)}}(window,document,"script","https://www2.gov.bc.ca/StaticWebResources/static/sp/sp-2-14-0.js","snowplow"));
            window.snowplow('newTracker','rt',collector, {
            appId: 'Snowplow_standalone_PSA',
            cookieLifetime: 86400 * 548,
            platform: 'web',
            post: true,
            forceSecureTracker: true,
            contexts: {
            webPage: true,
            performanceTiming: true
            }
            });
            window.snowplow('enableActivityTracking', 30, 30); // Ping every 30 seconds after 30 seconds
            window.snowplow('enableLinkClickTracking');
            window.snowplow('trackPageView');
        }
        // <!-- Snowplow stops plowing –>
        

        $(document).ready(function() {
            // Select the anchor element within the menu item
            $('.nav-item a.nav-link').on('blur', function() {
                // Check if the text content of the anchor element is "Resources"
                if(large_device != true) {
                    if ($(this).text().trim() === 'Resources') {
                        $('[data-widget="pushmenu"]').click();
                    }
                }
            });
        });

        


        // Get all <a> elements with data-dt-idx attribute
        var links = document.querySelectorAll('a[data-dt-idx]');

        // Loop through each link
        links.forEach(function(link) {
            // Get the text content of the link
            var labelText = link.textContent.trim();
            
            // Add the aria-label attribute with the text content
            link.setAttribute('aria-label', labelText);
        });
    </script>


</body>

</html>