<!--<div class="col-md-2 col-sm-12">
    <h3>Search</h3>-->


<div class="search-section">

    <form class="form-inline" role="form" method="post" action="{{ url('/blog/search') }} ">
        {!! csrf_field() !!}
        <div class="input-group">
            <input type="text" name="searchText" value="{{ old('searchText',isset($keyword)?$keyword:'')}}"  class="form-control"/>
            <span class="input-group-btn">
                <button type="submit" class="btn btn-default" type="button">Go!</button>
            </span>
        </div>
        @if(!empty(session('search-error')))
        <span class="help-block">
            <strong class="text-danger">{{ session('search-error') }}</strong>
        </span>
        @endif
    </form>


</div>



<!--    <h3>Categories</h3>
    <hr />
    <ul class="tree">
        @foreach ($category_tree as $category)
        <a href="{{ url('/blog/categories/'.$category->slug) }}" title="Click to view posts ">{!! $category->display !!}</a>
        @endforeach
    </ul>-->
<!--</div>-->