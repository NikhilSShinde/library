@extends(config("piplmodules.front-view-layout-location"))

@section("meta")
<title>Frequently Asked Questions</title>
@endsection

@section("content")


<div class="container">
    <h3> Frequently Asked Questions</h3>
    <div class="row">
        <div class="col-md-2">
             <h3>Categories</h3>
             <ul>
                @if(count($tree)>0 && empty($locale)) 
                  @foreach($tree as $ls_category)
               
                    <li><a href='{{url('/faqs/')}}/{{$ls_category->cat_url}}'>{{$ls_category->display}}</a></li>
                    
                 @endforeach
                @endif
             </ul> 
        </div>
        <div class="col-md-10">
           @if(count($faqs)>0)
           @foreach($faqs as $faq)
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse{{$faq->id}}" aria-expanded="false">{{$faq->question}}</a>
                    </h4>
                </div>
              
                <div id="collapse{{$faq->id}}" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
                    <div class="panel-body">
                         {!! $faq->answer !!}
                    </div>
                </div>  
                   
             
            </div>
           @endforeach
           
           @else
            
                Sorry, No data found
                
           @endif
        </div>
           
        </div>
    </div>
</div>
@endsection