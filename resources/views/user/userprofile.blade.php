@extends('layouts.app')
@section('content')
<div class="container">
@if (Auth::user()->steam_id == NULL)
 <div class="font-semibold text-orange-800 bg-orange-200 rounded-md p-8">
     Welcome to <strong>Indian Racing Comunity!</strong> Your account is created but not yet <strong>verified</strong>. To verify your Account please please Sign in with your <strong>Steam</strong> account
 </div>
@endif

<div class="flex my-8">
    <div>
        <img src="{{Auth::user()->avatar}}" class="rounded-md " alt="">
    </div>
    <div class="py-3">
        <div class="flex">
            <div class="text-xs font-semibold text-gray-600 mx-4 mt-1">USERNAME</div>
            <div class=" font-semibold text-gray-900">{{Auth::user()->name}}</div>
        </div>
        <div class="flex">
            <div class="text-xs font-semibold text-gray-600 ml-4 mr-3 mt-1">EMAIL</div>
            <div class=" font-semibold text-gray-900 ml-8">{{Auth::user()->email}}</div>
        </div>
        <div class="flex">
            <div class="text-xs font-semibold text-gray-600 ml-4 mr-6 mt-1">DISCORD</div>
            <div class=" font-semibold text-gray-900 ml-1">{{Auth::user()->name}}#{{Auth::user()->discord_discrim}}</div>
        </div>
        <div class="flex">
            <div class="text-xs font-semibold text-gray-600 ml-4 mr-6 mt-1">STEAM</div>
            <div class=" font-semibold text-gray-900 ml-4"><a href= "{{Auth::user()->steam_id}}">{{Auth::user()->steam_id}} </a></div>
        </div>
    </div>
</div>
<div>
    <form method="POST" action="setsteam/{{Auth::user()->id}}">
        @csrf
        <br><br>

        @if (Auth::user()->steam_id == NULL)
         <span class="text-xs font-semibold text-gray-600 mt-1">STEAM PROFILE LINK</span>
         <span class="text-red-600 mr-4">●</span>
         <a href="/login/steam"> <img src="{{url('/img/steam.png')}}" alt=""> </a>
         <span class="text-red-600 mr-2">●</span><span class="text-xs font-semibold text-gray-700">To verify your account please Sign in with your Steam account</span>
        @endif
    </form>
</div>

@endsection