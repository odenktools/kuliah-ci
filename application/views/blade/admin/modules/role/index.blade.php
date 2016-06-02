@extends('admin.layouts.master')

@section('mainContent')

	<div class="title">Hallo</div>
	
	<div class="">
		 @if(isset($branches))
			{!! json_encode($branches) !!}
		 @endif
		
		 @if(isset($name))
			 <p>{!! $name !!}</p>
		 @endif
	 </div>
	 
@stop