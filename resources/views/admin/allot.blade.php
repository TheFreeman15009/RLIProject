@extends('layouts.app')
@section('content')

<div class="w-1/3 mx-auto">
    <div class="text-2xl font-semibold text-gray-700">
        Allot Driver / User Profile
    </div>
    <div class="flex my-4 py-2">
        <div>
            <img src="{{$user->avatar}}" class="rounded" alt="">
        </div>
        <div class="flex flex-col justify-between ml-4">
            <div>
                <div class="text-xl font-bold text-gray-700 uppercase">{{$user->name}}</div>
                <div class="font-bold text-gray-700">{{$user->email}}</div>
            </div>
            <div class="flex flex-col-reverse">
                @if ($user->discord_discrim != "0")
                    <div class="font-bold text-gray-700 text-xl flex flex-row-reverse cursor-pointer" onclick="copyDiscordId()">
                        <i class="far fa-clipboard mt-2 text-base ml-2"></i> #{{$user->discord_discrim}}
                    </div>
                @endif
                <div>
                    <a href="https://steamcommunity.com/profiles/{{$user->steam_id}}" target="_blank">
                        <span class="px-2 pt-3 rounded cursor-pointer hover:bg-blue-700 pb-2 bg-blue-800">
                            <i class="fab fa-steam text-2xl text-white"></i>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <form method="POST" action="{{route('driver.allot')}}">
        @csrf
        <input type="hidden" value="{{$user->id}}" name="user_id">
        <span class="font-bold text-gray-700 ">Team</span>
        <select name="tier" id="tier" class="border p-1 mx-2">
            <option value="1">PC F1 Tier 1</option>
            <option value="2">PC F1 Tier 2</option>
            <option value="3">PC F1 Reserve/Challenger</option>
            <option value="11">Xbox F1 Tier 1</option>
            <option value="12">Xbox F1 Tier 2</option>
            <option value="13">Xbox F1 Reserve/Challenger</option>
            <option value="21">ACC</option>
            <option value="31">AC</option>
        </select>
        @if (count($existing) == 0)
        <button type="submit" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white py-1 px-8 rounded font-semibold">Submit</button>
        @endif

        @if (count($existing) > 1)
        <div class="bg-red-200 rounded text-red-800 p-4 mb-3 font-semibold">
            Duplicate Drivers in DB detected please check the database before proceeding !
        </div>
        @endif
    </form>
    <input type="text" value="{{$user->name}}#{{$user->discord_discrim}}" id="myInput" class="hidden">
    <script>
        function copyDiscordId() {
            var copyText = document.getElementById("myInput");;
            copyText.select();
            copyText.setSelectionRange(0, 99999)
            document.execCommand("copy");
            alert("Copied the DiscordId: " + copyText.value);
        }
    </script>
</div>

@endsection