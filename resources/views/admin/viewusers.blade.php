@extends('layouts.app')
<style>
  td{
    padding:5px
  }
</style>
@section('content')
@auth
<div class="block text-gray-700 text-2xl font-bold p-10">All Users</div>
<div class="flex w-1/2 sm:w-full md:w-full lg:w-full xl:w-full justify-center bg-white shadow-lg rounded px-4 pb-4" style="display: flex;">
<table class="table-auto rounded-lg shadow-lg">

  @foreach($user as $user)
    <tr>
      <td class="bg-red-{{300 * is_null($user->drivers)}}">
        {{$user->id}}
      </td>
      <td>
        <img src="{{$user->avatar}}" alt="" style="width:30px" class="rounded">
      </td>
      <td class="text-gray-800">
        {{$user->name}}
      </td>
      <td>
        <a href="{{route('user.profile', ['user' => $user->id])}}" class="bg-blue-100 rounded py-2 px-4 text-blue-800 cursor-pointer hover:text-blue-900 hover:bg-blue-200 ">View Details</a>
      </td>
      <td>
        <a href="{{route('driver.allotpage', ['id' => $user->id])}}" class="bg-yellow-100 rounded py-2 px-4 text-yellow-800 cursor-pointer hover:text-yellow-900 hover:bg-yellow-200 ">Allot Driver</a>
      </td>
    </tr>
  @endforeach
          
</table>
</div>
@endauth     
@endsection

