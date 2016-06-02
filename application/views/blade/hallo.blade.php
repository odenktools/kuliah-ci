@extends('master')

@section('mainContent')
	<div class="title">Hallo</div>
	
	<div class="">
		 @if(isset($name))
			 <p>{!! $name !!}</p>
		 @endif
	 </div>

@stop