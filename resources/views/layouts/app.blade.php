<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{url('/img/IRC_logo/logo_square.png')}}">
        
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>RLI Admin Panel</title>

    <!-- Scripts -->
    <!-- <script src="{{ asset('js/app.js') }}" defer></script> -->

    <!-- Fonts -->
    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"
    integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/custom.css">

    <!-- Styles -->
    <!-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> -->
</head>
<body>
    <!-- <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    Indian Racing Community
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent"> -->
                    <!-- Left Side Of Navbar -->
                    <!-- <ul class="navbar-nav mr-auto">

                    </ul> -->

                    <!-- Right Side Of Navbar -->
                    <!-- <ul class="navbar-nav ml-auto"> -->
                        <!-- Authentication Links -->
                        <!-- @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('') }}</a>
                                </li>
                            @endif
                        @else -->
                            <!-- <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>
                             
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="/user/profile/{{Auth::user()->id}}">
                                        {{ __('Profile') }}
                                    </a>
                                   
                                   
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                   

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav> -->
    
        <nav class="flex justify-between border-b fixed bg-white w-screen z-10 py-2">
         <div class="flex items-center flex-shrink-0">
               <div class="px-3 bg-gray-800 mx-2 text-white font-bold rounded-md hover:bg-gray-700 cursor-pointer flex items-center flex-shrink-0">
                  <a href="/"   class="flex" class="px-3 bg-gray-800 mx-2 text-white font-bold rounded-md hover:bg-gray-700"><img src="/img/IRC_logo/logo_square.png" height="45" width="45"> <span class="p-3">Indian Racing Comunity</span> </a>
               </div>
               <div class=" mx-2 flex items-center flex-shrink-0">
                  <a  class="px-4 py-3 font-semibold rounded hover:bg-gray-200 cursor-pointer" href="/joinus"><i class='fas fa-question-circle mx-1 text-blue-500'></i> FAQ</a>
               </div>
               <!-- <div class=" mx-2 flex items-center flex-shrink-0">
                    <a class="dropbtn px-4 py-3 font-semibold rounded hover:bg-gray-200 cursor-pointer" href="/standings"><i class='fas fa-trophy mx-1 text-yellow-500'></i> Championship Standings</a>
                    <div class="dropdown-content mx-5 my-3">
                        <a href="/1/4/standings" class="hover:bg-blue-300 "><i class='fas fa-caret-right px-1 text-green-500'></i> Tier 1</a>
                        <a href="/1/4/standings" class="hover:bg-green-300"><i class='fas fa-caret-right px-1 text-blue-500'></i> Tier 2</a>
                    </div>
               </div> -->
               <div class="px-4 py-3 font-semibold rounded hover:bg-gray-200 cursor-pointer mx-2 dropdown">
                    <a class="dropbtn" href="/standings"><i class='fas fa-trophy mx-1 text-yellow-500'></i> Championship Standings</a>
                    <div class="dropdown-content mx-5 my-3">
                    <a href="/1/4/standings" class="hover:bg-blue-300 "><i class='fas fa-caret-right pr-3 text-green-500'></i> Tier 1</a>
                        <a href="/2/1/standings" class="hover:bg-green-300"><i class='fas fa-caret-right pr-3 text-blue-500'></i> Tier 2</a>
                        <a href="/1/4.5/standings" class="hover:bg-yellow-300 "><i class='fas fa-caret-right pr-3 text-orange-500'></i> Mini Championship</a>
                        <a href="/1/4.75/standings" class="hover:bg-orange-300"><i class='fas fa-caret-right pr-3 text-yellow-500'></i> Classic Cars</a>
                    </div>
                </div>
               <div class=" mx-2 flex items-center flex-shrink-0">
                  <a  class="px-4 py-3 font-semibold rounded hover:bg-gray-200 cursor-pointer" href="/aboutus"><i class='far fa-address-card mx-1 text-indigo-500'></i>About Us</a>
               </div>
         </div>
         <div class="flex items-center flex-shrink-0 mr-8">
                <div class="px-4 py-2 bg-red-200 text-red-700 rounded font-semibold cursor-pointer hover:bg-red-300 text-center">
                    <a  href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt text-red-600 mr-2 text-center"></i>Logout
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden"> 
                        {{ csrf_field() }}
                    </form>
                </div>
                <!-- <div class=" bg-red-200 py-4 px-2">log</div> -->
            </div>
         <!-- @auth
          <div class="flex">
            <div >
            <div class="px-4 flex py-2 m-2 bg-gray-100 rounded font-semibold border cursor-pointer hover:bg-gray-200 hover:shadow-none">
                <a class="dropdown-item" href="/user/profile/{{Auth::user()->id}}">
                    {{Auth::user()->name}}
                </a>
            </div>
            </div>
            <div>
                <div class="px-4 flex py-2 m-2 bg-red-600 text-white rounded font-semibold cursor-pointer hover:bg-red-700 hover:shadow-none">
                <a  href="{{ route('logout') }}"
                    onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">
                        Logout
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                    {{ csrf_field() }}
                </form>
            </div>
         </div>
         @endauth -->
      </nav>
      <div class="flex">
      @auth
          <div class="sidebar fixed h-screen bg-gray-100 border w-56 py-4 px-4 shadow mt-16">
              <a href="/user/profile/" class="flex hover:bg-gray-200 rounded-md py-4 px-2">
                <img src="{{Auth::user()->avatar}}" class="rounded-full w-16" alt="">
                <div class="px-4 py-2">
                    <div class="font-semibold text-indigo-600">
                        {{Auth::user()->name}}
                    </div>
                    <div class="font-semibold text-sm">
                        #{{Auth::user()->discord_discrim}}
                    </div>
                </div>
              </a>
            <div class="pt-8 text-sm font-bold text-gray-700">
                USER CONTROLS
            </div>
            <div class="flex flex-col">
                <a href="" class="px-3 py-2 font-semibold hover:bg-gray-300 hover:text-blue-600 rounded-md text-gray-700"><i class="text-yellow-500 fas fa-trophy w-8 text-center"></i>View Standings</a>
                <a href="" class="px-3 py-2 font-semibold hover:bg-gray-300 hover:text-blue-600 rounded-md text-gray-700"><i class="text-purple-600 fas fa-award w-8 text-center"></i>View Team Stats</a>
                <a href="/f1/signup/" class="px-3 py-2 font-semibold hover:bg-gray-300 hover:text-blue-600 rounded-md text-gray-700"><i class="text-indigo-600 fas fa-edit w-8 text-center"></i>Sign Up </a>
                <a href="/home/report/create" class="px-3 py-2 font-semibold hover:bg-gray-300 hover:text-blue-600 rounded-md text-gray-700"><i class="text-orange-500 fas fa-exclamation-triangle w-8 text-center"></i>Create Report</a>
            </div>
            @if(Auth::user()->isadmin==1)
            <div class="pt-8 text-sm font-bold text-gray-700">
                ADMIN CONTROLS
            </div>
            <div class="flex flex-col">
                <a href="/home/admin/users" class="px-3 py-2 font-semibold hover:bg-gray-300 hover:text-blue-600 rounded-md text-gray-700"><i class="text-blue-500 fas fa-binoculars w-8 text-center"></i>View/Allot Drivers</a>
                <a href="" class="px-3 py-2 font-semibold hover:bg-gray-300 hover:text-blue-600 rounded-md text-gray-700"><i class="text-purple-600 fas fa-pen-alt w-8 text-center"></i>Update Standings</a>
                <a href="/home/admin/report" class="px-3 py-2 font-semibold hover:bg-gray-300 hover:text-blue-600 rounded-md text-gray-700"><i class="text-orange-500 fas fa-exclamation-triangle w-8 text-center"></i>View Reports</a>
            </div>
            @endif
          </div>
          
          @endauth
            <main class="py-20 ml-64 w-full">
               
                    
                <!-- <div class="bg-green-200 rounded text-green-800 p-4 mb-3 font-semibold"> -->
                        <!-- {{session()->get('success')}} -->
                        <!-- You have logged in! -->
                <!-- </div> -->
                @yield('content')
                
    
               
            </main>
        </div>

      </div>
</body>
</html>
