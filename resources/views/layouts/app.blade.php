<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="{{url('/img/IRC_logo/logo_square.png')}}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta property="og:title" content="Indian Racing Community">
        @if(isset($metaUserAvatar))
            <meta property="og:description" content="{{$metaName}}">
            <meta property="og:image" content="{{$metaUserAvatar}}">
        @else
            <meta property="og:description" content="A place For Every Indian Racing Enthusiast.">
            <meta property="og:image" content="/img/IRC_logo/logo_square_new.png">
        @endif
        <meta property="og:url" content="https://indianracingcommunity.co.in">
        <title>Indian Racing Community</title>
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css"
            integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('/css/custom.css')}}">
        <script src="{{ asset('js/jquery35.js')}}"></script>
        <script src="https://unpkg.com/@popperjs/core@2.9.1/dist/umd/popper.min.js" charset="utf-8"></script>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap');
            * {
                font-family: 'Inter', sans-serif;
            }

            .discord {
                color: #7289da
            }

            .youtube {
                color: #FF0000
            }

            .instagram {
                color: #E1306C
            }

            .facebook {
                color: #4267B2
            }

            .steam {
                color: #1f3d7a
            }

            .twitter {
                color: #1DA1F2
            }

            #footer i:hover, .discord:hover, .youtube:hover, .instagram:hover, .steam:hover, .twitter:hover, .facebook:hover {
                color: #1a202c
            }

            .profileImage:hover {
                filter: grayscale(100%);
                -webkit-filter: grayscale(100%);
                opacity: 90%;
            }
        </style>
    </head>

    <body class="bodyClass">
        <div class="" id="screen">
            <div class="md:w-auto bg-white z-50 min-h-full fixed shadow-lg opacity-0" style="min-width:250px; left:-330px" id="sidebar">
                <div class="h-screen text-black">
                    <div class="flex rounded-br-md py-3 md:px-3 bg-gray-800">
                        <div class="md:mx-3 text-white font-bold rounded-md">
                            <p class="flex px-3 mx-2 text-white font-bold rounded-md">
                                <img src="/img/IRC_logo/logo_square.png" class="h-12">
                                <span class="py-3 pl-3">Indian Racing Community</span>
                            </p>
                        </div>
                    </div>

                    <div>
                        <div class="my-8" id="main-menu">
                            <div class="font-bold text-sm px-5 tracking-wide">LEAGUE RACING</div>
                            
                            <div class="my-1">
                                <a href="{{route('driver.signup')}}" class="py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center"><div class="items-center flex-shrink-0 w-12 text-center"><i class="fas fa-edit"></i></div>All sign ups</a>
                            </div>
                            
                            <div class="font-bold text-sm px-5 mt-4 tracking-wide">LEAGUE RULES</div>
                            
                            <div class="my-1">
                                <a href="/F1_Rulebook_v8.pdf" target="_blank" class="py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center"><div class="items-center flex-shrink-0 w-12 text-center"><i class="fas fa-desktop"></i></div>F1</a>
                                <a href="/ACC_Rulebook_v4.pdf" target="_blank" class="py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center"><div class="items-center flex-shrink-0 w-12 text-center"><i class="fas fa-desktop"></i></div>PC ACC</a>
                                <!-- <a href="{{route('rules.xboxf1')}}" class="py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center"><div class="items-center flex-shrink-0 w-12 text-center"><i class="fab fa-xbox"></i></div>XBOX F1</a> -->
                            </div>  

                            <div class="font-bold text-sm px-5 mt-4 tracking-wide">LEAGUE INFO</div>
                            
                            <div class="my-1 ">
                                <div data-origin='champ' class="subMenuShow py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center"><div class="items-center flex-shrink-0 w-12 text-center"><i class='fas fa-trophy'></i></div>Championship Standings</div>
                                <div data-origin='race' class="subMenuShow py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center"><div class="items-center flex-shrink-0 w-12 text-center"><i class="fa fa-flag-checkered"></i></div>Race Results</div>
                            </div>
                            
                            @auth
                                @can('admin|coordinator|steward|signup')
                                    <div class="font-bold text-sm px-5 mt-4 tracking-wide">ADMIN CONTROLS</div>
                                @endcan
                                
                                <div class="my-1">
                                    @can('admin|coordinator')
                                    <a href="{{route('race.upload')}}" class="py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center">
                                        <div class="items-center flex-shrink-0 w-12 text-center">
                                            <i class="fas fa-upload"></i>
                                        </div>
                                        Upload Race Results
                                    </a>

                                    <a href="{{route('acc.parseupload')}}" class="py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center">
                                        <div class="items-center flex-shrink-0 w-12 text-center">
                                            <i class="fas fa-file"></i>
                                        </div>
                                        Parse ACC Results
                                    </a>
                                    @endcan

                                    @can('admin|steward')
                                        <a href="{{route('steward.control')}}" class="py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center">
                                            <div class="items-center flex-shrink-0 w-12 text-center">
                                                <i class="fa fa-gavel"></i>
                                            </div>
                                            Publish Verdicts
                                        </a>
                                    @endcan
                                    
                                    @can('admin|signup')
                                    <a href="{{route('coordinator.signup')}}" class="py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center">
                                        <div class="items-center flex-shrink-0 w-12 text-center">
                                            <i class="fa fa-eye"></i>
                                        </div>
                                        View Sign Ups
                                    </a>
                                    @endcan
                                    
                                    @can('admin|coordinator')
                                    <a href="{{route('coordinator.driverlist')}}" class="py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center">
                                        <div class="items-center flex-shrink-0 w-12 text-center">
                                            <i class="fas fa-sort"></i>
                                        </div>
                                        View/Allot Drivers
                                    </a>
                                    @endcan

                                    @can('steward|coordinator')
                                    {{-- Enable this route later when we need it otherwise it breaks the site --}}
                                    {{-- <a href="{{route('steward.list')}}" class="py-2 text-black cursor-pointer pr-4 mx-4 rounded-md hover:bg-gray-900 font-medium hover:text-white flex items-center"><div class="items-center flex-shrink-0 w-12 text-center"><i class="fas fa-exclamation"></i></div>View Reports</a> --}}
                                    @endcan

                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @auth    
            <div id="screenRight">
                <div class="md:w-auto bg-white z-50 fixed shadow-lg opacity-0 rounded-bl-md" style="min-width:200px; max-width:200px; right:-330px" id="sidebarRight">
                    <div class="text-black">
                        <div>
                            <div class="my-5 flex flex-col gap-5 px-5" id="main-menu-right">
                                <div class="flex flex-col items-center text-center gap-5">
                                    <img src="{{Auth::user()->avatar}}" class="rounded-full w-24" alt="">

                                    <div class="flex flex-col gap-1">
                                        <div class="font-bold uppercase tracking-wide break-all">
                                            {{Auth::user()->name}}
                                        </div>

                                        @if (Auth::user()->discord_discrim != "0")    
                                            <div class="font-semibold text-sm text-gray-700 tracking-wide break-all">
                                                {{ Auth::user()->name . "#" . Auth::user()->discord_discrim }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-1 font-semibold tracking-wide">
                                    <a href="{{route('user.home')}}" class="flex flex-row items-center gap-3 pl-3 py-2 rounded hover:bg-gray-900 hover:text-white">
                                        <i class="far fa-user w-5"></i>
                                        <span>Profile</span>
                                    </a>

                                    <a href="{{route('faq')}}" class="flex flex-row items-center gap-3 pl-3 py-2 rounded hover:bg-gray-900 hover:text-white">
                                        <i class="far fa-question-circle w-5"></i>
                                        <span>FAQs</span>
                                    </a>

                                    <a href="{{route('aboutus')}}" class="flex flex-row items-center gap-3 pl-3 py-2 rounded hover:bg-gray-900 hover:text-white">
                                        <i class="far fa-address-card w-5"></i>
                                        <span>About Us</span>
                                    </a>

                                    <a href="{{route('ourteam')}}" class="flex flex-row items-center gap-3 pl-3 py-2 rounded hover:bg-gray-900 hover:text-white">
                                        <i class="fas fa-user-friends w-5"></i>
                                        <span>Our Team</span>
                                    </a>

                                    <div class="flex flex-row items-center gap-3 pl-3 py-2 hover:bg-red-600 hover:text-white rounded text-red-600 cursor-pointer" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt w-5"></i>
                                        
                                        <div>
                                            <a>
                                                Logout
                                            </a>
                                            
                                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                                {{ csrf_field() }}
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endauth
        
        <div class="flex flex-col justify-between md:w-full min-h-screen" id="content">
            <div class="flex flex-col">
                <nav class="flex items-center border-b border-gray-400 justify-between px-2 py-2 bg-white z-100">
                    <div class="flex items-center">
                        <div id="leftSidebarMenu" class="items-center px-4 py-3 rounded-md flex-shrink-0 cursor-pointer hover:text-white hover:bg-gray-900" onclick="handleLeftMenuClick()"><i class="fas fa-bars"></i></div>
                        
                        <div class="px-3 bg-gray-800 mx-2 text-white font-bold rounded-md hover:bg-gray-700 cursor-pointer">
                            <a href="{{route('home')}}" class="flex" class="px-3 bg-gray-800 mx-2 text-white font-bold rounded-md hover:bg-gray-700 ">
                                <img src="/img/IRC_logo/logo_square.png" class="h-12"> 
                                <span class="hidden py-3 pl-2 navXl:block">Indian Racing Community</span>
                            </a>
                        </div>
    
                        <div class="hidden xl:block rounded-md py-3 items-center flex-shrink-0 font-semibold px-4 hover:bg-gray-900 hover:text-white dropdown">
                            <button class="font-semibold cursor-default px-4 rounded inline-flex items-center">
                                <span> <i class='fas fa-trophy mx-1 text-yellow-500'></i> Championship Standings</span>
                            </button>
                            
                            <ul class="dropdown-content absolute hidden text-gray-700 -ml-4 pt-3" style="width:18rem">
                                @foreach($topBarSeasons as $series)
                                    <li class="dropdown border-r border-b border-indigo-200 rounded">
                                        <p class="bg-indigo-100 cursor-default hover:bg-blue-300 py-2 px-4 block whitespace-no-wrap rounded">
                                            <i class='fas fa-caret-right pr-3 text-green-500'></i>
                                            {{$series['name']['website']}}
                                        </p>
    
                                        <ul class="dropdown-content hidden absolute text-gray-700 -mt-10" style="margin-left:18rem">
                                            @foreach($series['tier'] as $tier)
                                            <li class="dropdown border-r border-b border-orange-300 rounded">
                                                <a class="bg-orange-100 hover:bg-orange-300 py-2 px-4 w-64 block whitespace-no-wrap rounded" href="{{route('standings', ['code' => $series['name']['code'], 'tier' => $tier[0]['tier'], 'season' => $tier[0]['season']])}}">
                                                    <i class='fas fa-caret-right pr-3 text-blue-500'></i>
                                                    {{$tier[0]['tiername']}}
                                                </a>
    
                                                <ul class="dropdown-content absolute hidden text-gray-700 -mt-10" style="margin-left:16.05rem">
                                                    @foreach($tier as $season)
                                                    <li class="border-r border-b border-purple-300 rounded">
                                                        <a class="bg-purple-100 hover:bg-purple-300 py-2 px-4 block whitespace-no-wrap rounded" href="{{route('standings', ['code' => $series['name']['code'], 'tier' => $season['tier'], 'season' => $season['season']])}}">
                                                            <i class='fas fa-caret-right pr-3 text-red-400'></i>
                                                            Season {{floor($season['season'])}}
                                                        </a>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="hidden xl:block rounded-md py-3 items-center flex-shrink-0 font-semibold px-4 hover:bg-gray-900 hover:text-white dropdown">
                            <button class="font-semibold cursor-default px-4 rounded inline-flex items-center">
                                <span><i class="fa fa-flag-checkered mx-1 text-dark-500"></i> Race Results</span>
                            </button>
    
                            <ul class="dropdown-content absolute hidden text-gray-700 -ml-4 pt-3" style="width: 12rem;">
                                @foreach($topBarSeasons as $series)
                                    <li class="dropdown border-r border-b border-indigo-200 rounded">
                                        <p class="bg-indigo-100 hover:bg-blue-300 cursor-default py-2 px-4 block whitespace-no-wrap rounded">
                                            <i class='fas fa-caret-right pr-3 text-green-500'></i>
                                            {{$series['name']['website']}}
                                        </p>
    
                                        <ul class="dropdown-content hidden absolute text-gray-700 -mt-10" style="margin-left: 12rem">
                                            @foreach($series['tier'] as $tier)
                                            <li class="dropdown border-r border-b border-orange-300 rounded">
                                                <a class="bg-orange-100 hover:bg-orange-300 py-2 px-4 w-64 block whitespace-no-wrap rounded" href="{{route('allraces', ['code' => $series['name']['code'], 'tier' => $tier[0]['tier'], 'season' => $tier[0]['season']])}}">
                                                    <i class='fas fa-caret-right pr-3 text-blue-500'></i>
                                                    {{$tier[0]['tiername']}}
                                                </a>
    
                                                <ul class="dropdown-content absolute hidden text-gray-700 -mt-10" style="margin-left:16.05rem">
                                                    @foreach($tier as $season)
                                                    <li class="border-r border-b border-purple-300 rounded">
                                                        <a class="bg-purple-100 hover:bg-purple-300 px-4 py-2 block whitespace-no-wrap rounded" href="{{route('allraces', ['code' => $series['name']['code'], 'tier' => $season['tier'], 'season' => $season['season']])}}"><i class='fas fa-caret-right pr-3 text-red-400'></i> Season {{floor($season['season'])}} </a>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
    
                        @guest
                        <div class="flex flex-row items-center justify-center gap-4">
                            <a href="{{route('faq')}}">
                                <div class="hidden xl:block rounded-md py-3 items-center flex-shrink-0 font-semibold px-4 cursor-pointer hover:bg-gray-900 hover:text-white">
                                    <i class="mr-1 text-blue-700 far fa-question-circle"></i> 
                                    FAQs
                                </div>
                            </a>
        
                            <a href="{{route('ourteam')}}">
                                <div class="hidden xl:block rounded-md py-3 items-center flex-shrink-0 font-semibold px-4 cursor-pointer hover:bg-gray-900 hover:text-white">
                                    <i class="mr-1 text-purple-700 far fas fa-user-friends"></i> 
                                    Our Team
                                </div>
                            </a>
    
                            <a href="{{route('aboutus')}}">
                                <div class="hidden xl:block rounded-md py-3 items-center flex-shrink-0 font-semibold px-4 cursor-pointer hover:bg-gray-900 hover:text-white">
                                    <i class="mr-1 text-teal-700 far fa-address-card"></i> 
                                    About us
                                </div>
                            </a>
                        </div>
                        @endguest
                    </div>
    
                    <button data-origin='champ' class="subMenuShow font-semibold cursor-default px-1 text-xl xl:hidden rounded inline-flex items-center">
                        <span> <i class='fas fa-trophy mx-1 text-yellow-500'></i></span>
                    </button>
    
                    <button data-origin='race' class="subMenuShow font-semibold cursor-default px-1 text-xl xl:hidden rounded inline-flex items-center">
                        <span><i class="fa fa-flag-checkered mx-1 text-black-500"></i></span>
                    </button>
    
                    @guest
                        <button class="font-semibold cursor-default px-1 text-xl xl:hidden rounded hidden md:inline-flex items-center">
                            <a href="{{route('faq')}}" style="cursor:default"><span><i class='far fa-question-circle mx-1 text-blue-700'></i></span></a>
                        </button>
    
                        <button class="font-semibold cursor-default px-1 text-xl xl:hidden rounded hidden md:inline-flex items-center">
                            <a href="{{route('ourteam')}}" style="cursor:default"><span><i class='far fas fa-user-friends mx-1 text-purple-700'></i></span></a>
                        </button>
    
                        <button class="font-semibold cursor-default px-1 text-xl xl:hidden rounded hidden md:inline-flex items-center">
                            <a href="{{route('aboutus')}}" style="cursor:default"><span><i class='far fa-address-card mx-1 text-teal-700'></i></span></a>
                        </button>
    
                        <div class="px-4 mr-2 flex py-2 bg-purple-600 text-white rounded font-semibold shadow-md cursor-pointer hover:bg-gray-900 hover:text-white hover:shadow-none">
                            <a href="{{route('login.discord')}}" class="flex flex-row gap-2 items-center justify-center">
                                <i class="far fa-user py-2 px-1 md:px-0"></i> 
                                <span class="hidden md:block">Login</span>
                            </a>
                        </div>
                    @endguest
                    
                    @auth
                        <button class="font-semibold cursor-default px-4 rounded inline-flex items-center cursor-pointer rightSidebarMenu mr-2" onclick="handleRightMenuClick()" style="outline:none">
                            <img src="{{Auth::user()->avatar}}" class="rounded-full w-12 border-2 border-purple-500 hover:border-gray-900 rightSidebarMenu profileImage" alt="P">
                        </button>
                    @endauth
                </nav>
                
                <div class="fixed z-50 inset-0 overflow-y-auto champResModal" style='display:none;'>
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
    
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                            <div class='bg-gray-900 p-1 flex justify-end'>
                                <div class="modalTitle text-white w-full p-1 pl-4"></div>
    
                                <button id='closeModal' class="rounded-full text-white font-bold h-8 w-8 flex items-center justify-center"><i class="fas fa-times"></i></button>
                            </div>
    
                            <div class="my-5 mx-6 rounded-lg border" id="sub-menu">
                                <div class="font-bold text-sm px-5 pt-2 tracking-wide">Select Series</div>
    
                                <div class="px-5 w-full">
                                    <select class="seriesOptions border-2 rounded w-full border-gray-800 hover:bg-gray-900 bg-white text-black hover:text-white p-1 my-2">
                                        <option class="" selected value="">Choose Series</option>
                                        @foreach($topBarSeasons as $series)
                                            <option value='{{str_replace(' ', '_',strtolower($series['name']['website']))}}'>{{$series['name']['website']}}</option>
                                        @endforeach
                                    </select>
                                </div>
    
                                <div id="tierSelectDiv" style="display: none;">
                                    <div class="font-bold text-sm px-5 mt-4 tracking-wide">Select Tier</div>
    
                                    <div class="px-5 w-full">
                                        <select class="tierOptions border-2 w-full border-gray-800 rounded hover:bg-gray-900 bg-white text-black hover:text-white p-1 my-2">
                                            <option class="" selected value="">Choose Tier</option>
                                            @foreach($topBarSeasons as $series)
                                                @foreach($series['tier'] as $tier)
                                                    <option value="{{$tier[0]['tier']}}" class="allTierOptions tiersOf_{{str_replace(' ', '_',strtolower($series['name']['website']))}}" data-tier='{{$tier[0]['tier']}}' data-series='{{str_replace(' ', '_',strtolower($series['name']['website']))}}'>{{$tier[0]['tiername']}}</option>
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
    
                                <div id="seasonSelectDiv" style="display: none;">
                                    <div class="font-bold text-sm px-5 mt-4 tracking-wide">Select Season</div>
    
                                    <div class="px-5 w-full">
                                        <select class="seasonOptions border-2 border-gray-800 rounded w-full hover:bg-gray-900 bg-white text-black hover:text-white p-1 my-2">
                                            <option class="" selected value="">Choose Season</option>
                                            @foreach($topBarSeasons as $series)
                                                @foreach($series['tier'] as $tier)
                                                    @foreach($tier as $season)
                                                        <option 
                                                            class="allSeasonOptions seasonOf_{{$tier[0]['tier']}}_{{str_replace(' ', '_',strtolower($series['name']['website']))}}"
                                                            data-champLink="{{route('standings', ['code' => $series['name']['code'], 'tier' => $season['tier'], 'season' => $season['season']])}}" 
                                                            data-raceLink="{{route('allraces', ['code' => $series['name']['code'], 'tier' => $season['tier'], 'season' => $season['season']])}}"
                                                        >
                                                            Season {{floor($season['season'])}}
                                                        </option>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
    
                                <div class="text-center mx-5 my-4 font-semibold">
                                    <div style="display: none;" class="lickAndSend px-4 py-2 bg-blue-600 text-white rounded font-semibold shadow-md cursor-pointer hover:bg-blue-700 hover:text-white hover:shadow-none">
                                        <button id="lickAndSend" type="button" class="text-center">Send It!</button>
                                    </div>
                                    
                                    <span style="display: none;" id="optionError" class="text-red-800"><i class="fa fa-exclamation-triangle pt-2 pr-2" aria-hidden="true"></i> Please select all the options</span>
                                </div>
    
                                <a style="display: none;" id="redirectLickAndSend" href=""></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class='mainContent'>
                    @auth
                        @if (session()->has('error'))
                            <div class="mx-auto mt-4">
                                <div class="xl:gap-6 px-5 md:px-10 xl:px-14">
                                    <div class="flex flex-row items-start justify-center gap-3 break-words p-4 rounded-md text-red-800 bg-red-200 lg:text-lg tracking-wide font-semibold w-full">
                                        <i class="fa fa-exclamation-circle my-1"></i>
                                        <p>{{session()->get('error')}}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (session()->has('success'))
                            <div class="mx-auto mt-4">
                                <div class="xl:gap-6 px-5 md:px-10 xl:px-14">
                                    <div class="flex flex-row items-start justify-center gap-3 break-words p-4 rounded-md text-green-800 bg-green-200 lg:text-lg tracking-wide font-semibold w-full">
                                        <i class="far fa-check-circle my-1"></i>
                                        <p>{{session()->get('success')}}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (session()->has('info'))
                            <div class="mx-auto mt-4">
                                <div class="xl:gap-6 px-5 md:px-10 xl:px-14">
                                    <div class="flex flex-row items-start justify-center gap-3 break-words p-4 rounded-md text-blue-800 bg-blue-200 lg:text-lg tracking-wide font-semibold w-full">
                                        <i class="fa fa-exclamation-circle my-1"></i>
                                        <p>{{session()->get('info')}}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endauth
                    <main class="mx-auto my-8 w-full">
                        @yield('content')
                    </main>
                </div>
            </div>
            <footer id="footer" class="flex flex-col lg:flex-row items-center justify-center lg:justify-between px-8 py-5 gap-4 bg-white w-full border-t">
                <div class="leading-tight text-center lg:text-left">
                    <div class="text-gray-700 font-bold">Indian Racing Community</div>
                    <div class="text-gray-600 font-semibold text-sm">A place for every Indian racing enthusiast.</div>
                </div>

                <div class="grid grid-cols-3 lg:flex lg:flex-row gap-8 lg:gap-5 text-center text-sm font-bold text-gray-600">
                    <a href="{{route('faq')}}" class="hover:text-gray-800">FAQs</a>
                    <a href="{{route('aboutus')}}" class="hover:text-gray-800">About Us</a>
                    <a href="{{route('ourteam')}}" class="hover:text-gray-800">Our Team</a>
                </div>

                <div class="flex flex-row items-center justify-center gap-4 text-xl">
                    <a href="https://discord.gg/ngvX9Mm" target="_blank">
                        <i class="fab fa-discord discord"></i>
                    </a>

                    <a href="https://www.youtube.com/channel/UC2Li3g3zak9gQ6YtE3YThXw" target="_blank">
                        <i class="fab fa-youtube youtube"></i>
                    </a>
                    
                    <a href="https://www.instagram.com/indianracingcommunity/" target="_blank">
                        <i class="fab fa-instagram instagram"></i>
                    </a>

                    <a href="https://steamcommunity.com/groups/indianracingcommunity" target="_blank">
                        <i class="fab fa-steam steam"></i>
                    </a>

                    <a href="https://twitter.com/racing_indian" target="_blank">
                        <i class="fab fa-twitter twitter"></i>
                    </a>

                    <a href="https://www.facebook.com/indianracingcommunity/" target="_blank">
                        <i class="fab fa-facebook facebook"></i>
                    </a>
                    {{-- <span class="text-xl cursor-pointer">
                        <a href="https://www.reddit.com/r/IndianRacingCommunity/" target="_blank">
                            <i style="color:#ff581a" class="fab fa-reddit"></i>
                        </a>
                    </span> --}}
                </div>
            </footer>
        </div>
    </body>

    <script src="{{ asset('js/scripts.js') }}" type="text/javascript"></script>
    
    @if ("{{Auth::user()->mothertongue}}" == "")
        <script>
            $( document ).ready(function() {
                $('#sidebar').animate({left: '0px', opacity: '1'});
                sidebarVisible = 0;
            });
        </script>
    @endif
</html>