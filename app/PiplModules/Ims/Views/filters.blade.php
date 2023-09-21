<div class="row">
<div class="col-sm-12 col-md-8 col-md-offset-4 text-right">
<div class="row">
<div class="col-md-8">
<div class="form-group">
<a class="btn btn-sm btn-info" href="javascript:void(0)" id="btn-filter" title="Click to filter the messages"><i class="fa fa-filter"></i> Filter options</a>
</div>
</div>
<div class="col-md-4">

<div class="input-group">
<span class="input-group-addon"># of records</span> <select class="form-control input-sm"  id="numRecords"><option value="10">10</option><option value="25" selected>25</option><option value="50">50</option><option value="100">100</option><option value="1000">All</option></select>


</div>
</div>
</div>
</div>
<br><br>
</div>
<div class="well well-sm hidden" id="filter-options">
<a class="close text-danger" href="javascript:void(0)" id="btn-close" title="Close"><i class="fa fa-close"></i></a>
  <span class="clearfix"></span><br>
<div class="row" >
<div class="col-sm-12 col-md-6">
<legend>Filter <button class="btn btn-sm btn-primary pull-right" type="button" id="reset-filter">Reset</button></legend>
<div class="form-group">
<div class="row">
<div class="col-sm-12 col-md-6">
<label for="project">Select Project</label> <select id="filter-project" class="form-control"><option value="">All projects</option>
@if(is_array($filters['projects']))
	@foreach($filters['projects'] as $project)
    	<option value="{{$project}}">{{$project}}</option>
    @endforeach
@endif
</select>
</div>
<div class="col-sm-12 col-md-6">
<label for="user">Select User</label> <select id="filter-user" class="form-control"><option value="">All users</option>

@if(is_array($filters['users']))
	@foreach($filters['users'] as $user_id=>$name)
    	<option value="{{$name}}">{{$name}}</option>
    @endforeach
@endif

</select>
</div>
</div>

</div>

<div class="form-group">
<div class="row">
<div class="col-sm-12 col-md-6">
<label for="subject">Enter Subject</label> <input id="filter-subject" class="form-control" placeholder="Enter subject"/>
</div>
<div class="col-sm-12 col-md-6">
<label for="date-range">Select Date</label> <select id="filter-date-range" class="form-control"><option value="">All Dates</option>
@if(is_array($filters['date_range']))
	@foreach($filters['date_range'] as $date)
    	<option value="{{$date}}">{{$date}}</option>
    @endforeach
@endif
</select>
</div>
</div>
</div>
<div class="form-group">
<div class="row">
<div class="col-md-12 col-sm-12"><div class="form-group text-center">
<a class="btn btn-primary" role="button" href="javascript:void(0)" id="btn-apply-filter">Apply</a>
</div></div>
</div>
</div>
</div>
<div class="col-sm-12 col-md-6">
<legend>Sort <button class="btn btn-sm btn-primary pull-right" type="button" id="reset-sort">Reset</button></legend>

<div class="form-group">
<div class="row">
<div class="col-sm-12 col-md-6">
<label for="sortby">Sort By</label> <select class="form-control" id="sort-by"><option value="default-sort">Date</option><option value="project">Project</option><option value="user">User</option><option value="subject">Subject</option></select>
</div>
<div class="col-sm-12 col-md-6">
<label for="sort-order">Sort Order</label> <select class="form-control" id="sort-order"><option value="asc">Ascending</option><option value="desc" selected>Decending</option></select>
</div>
</div>

</div>

</div>
</div>

</div>
<script src="{{url('media/front/js/ims-filter-and-sort.js')}}" type="text/javascript"></script> 