@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Rating And Review Details</title>
<style>
    .pi-mb-0 {
        background: #f2f3f4;
        border: 1px solid silver;
        padding: 8px 40px;
        position: relative;
    }
    .pi-input {
        background: silver none repeat scroll 0 0;
        height: 28px;
        left: 1px;
        line-height: 30px;
        position: absolute;
        text-align: center;
        top: 1px;
        width: 30px;
    } 
    .pi-input input[type="radio"] {
        line-height: normal;
        margin: 7px 0 0;
    }
    .pi-mb-0 > a {
        color: black;
        display: block;
    }
    .pi-check-acc{
        position: absolute;
        top: 0;
        right: 0;
        height: 30px;
        width: 30px;
        line-height: 30px;
        text-align: center;
    }
    .pi-check-acc .fa {
        line-height: 30px;
    }

</style>

@endsection

@section('content')
<input type="hidden" id="is_readonly" value="false">
<script type="text/javascript"  src="{{url('public/media/backend/js/star-rate.js')}}"></script>
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('/admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
            @if($user_id!=0) 
                <a href="{{url('/admin/rating-review/list/'.$user_id)}}">Manage Rating And Review</a>
             @else   
                <a href="{{url('/admin/rating-review/list')}}">Manage Rating And Review</a>
             @endif   

                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Rating And Review Details</a>
            </li>
        </ul>
        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Edit
                </div>
            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" role="form" action="" method="post" id="frm_rating_and_review">
                    {!! csrf_field() !!}
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                                <div class="col-md-6">  
                                    <!--<div class="col-md-4">-->  
                                    <h4><b>Review Details : </b></h4>
                                    <!--</div>-->
                                    <div class="col-md-12">  
                                        <div class="form-group clearfix">
                                            <label class="col-md-4 control-label">Rating : </label>
                                            <div class="col-md-8">     
                                                <span class="help-block">
                                                    <div id="hearts-existing" class="starrr" data-rating='{{$rating_details->rating}}'></div>
                                                </span>
                                            </div>
                                        </div>
                                        @if($rating_by_type==3)
                                        <div class="form-group clearfix">
                                            <label class="col-md-4 control-label">Review : </label>
                                            <div class="col-md-8">     
                                                <span class="help-block">
                                                    <textarea class="form-control" name="description">{{$rating_details->review}}</textarea>
                                                </span>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="form-group @if ($errors->has('status')) has-error @endif">
                                            <label class="col-md-4 control-label">Status<sup>*</sup></label>
                                            <div class="col-md-8">     
                                                <select class="form-control" name="status">
                                                    <option value="">-- Status -- </option>>
                                                    <option value="1" @if(old('status',$rating_details->status)==1) selected="selected" @endif>Active</option>
                                                    <option value="0" @if(old('status',$rating_details->status)==0) selected="selected" @endif>Inactive</option>
                                                </select>
                                                @if ($errors->has('status'))
                                                <span class="help-block">
                                                    <strong class="text-danger">{{ $errors->first('status') }}</strong>
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group">
                                          <label class="col-md-4 control-label"></label>
                                            <div class="col-md-8">      
                                            <input type="hidden" name="star_counter" id="star_counter" value="{{$rating_details->rating}}">
                                            <input type="hidden" name="selected_tags" id="selected_tags" value="{{$rating_details->rating_ques_id}}">
                                            <button type="submit" id="submit" class="btn btn-primary">Update</button>
                                        </div>
                                     </div>
                                        
                                    </div>
                                </div>
                                <div class="col-md-6">  
                                    @if($rating_by_type==3)
                                    <h4><b>Tags Details : </b></h4>
                                    <div class="col-md-12">
                                        <div id="accordion" role="tablist" aria-multiselectable="true">
                                            <div id="tag_list_data">
                                                @if(count($question_data))
                                                @foreach($question_data as $key => $tags)
                                                <?php if($tags->rating_star_no==intval($rating_details->rating)){ ?>
                                                <div class="card">
                                                    <div class="card-header" role="tab" id="heading{{$key}}">
                                                        <h5 class="mb-0 pi-mb-0 ">
                                                            <span class="pi-input">
                                                                <input type="radio" value="{{$tags->id}}" name="rating_tags" id="rating_tags" @if($rating_details->rating_ques_id==$tags->id) checked @endif>
                                                            </span>
                                                            <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{$key}}" aria-expanded="true" aria-controls="collapse{{$key}}">
                                                                {{$tags->ques_title}}
                                                            </a>
                                                        </h5>
                                                    </div>
                                                    <div id="collapse{{$key}}" class="collapse {{($key == '0') ? 'in' : ''}}" role="tabpanel" aria-labelledby="heading{{$key}}">
                                                        <div class="card-block">
                                                            {{$tags->ques_desc}}
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php } ?>
                                                @endforeach
                                                @else
                                                Sorry! Tags not foundation
                                                @endif
                                            </div>
                                        </div>
                                        <div for="rating_tags" generated="true" class="text-danger"></div>
                                    </div>
                                    
                                    @endif
                                    
                                    
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<style>
    #hearts { color: #ee8b2d;}
    #hearts-existing { color: #ee8b2d;}

    .glyphicon{
        display: inline-block;
        font-size: 22px;
        line-height: 14px;
        margin-left: 5px;
        cursor: pointer;
    }

    .help-block {
        margin-bottom: 10px;
        margin-top: 10px;
    }
    .btn-p {
        margin-top: 40px;
    }
</style>
<script>
$(document).ready(function() {
    $('#hearts-existing').on('starrr:change', function(e, value) {
        $('#star_counter').val(value);
        var selected_tags = $('#selected_tags').val();
        var array = selected_tags.split(",");
        $.ajax({
            url: "{!! url('/admin/rating/get-tags')!!}",
            type: 'post',
            data: {
                question_id: value
            },
            dataType: 'json',
            success: function(response) {
                var str = '';
                if (response.length > 0) {
                    $.each(response, function(index, value) {
                        str += '<div class="card-header" role="tab" id="heading' + index + '">';
                        str += '<h5 class="mb-0 pi-mb-0 ">';
                        str += '<span class="pi-input">';
                        if (inArray(value.id, array)) {
                            str += '<input type="checkbox" value="' + value.id + '" name="rating_tags" id="rating_tags" checked>';
                        } else {
                            str += '<input type="checkbox" value="' + value.id + '" name="rating_tags" id="rating_tags">';
                        }
                        str += '</span>';
                        str += '<a data-toggle="collapse" data-parent="#accordion" href="#collapse' + index + '" aria-expanded="true" aria-controls="collapse' + index + '">' + value.ques_title + '</a>';
                        str += '</h5>';
                        str += '</div>';
                        str += '<div id="collapse' + index + '" class="collapse" role="tabpanel" aria-labelledby="heading' + index + '">';
                        str += '<div class="card-block">' + value.ques_desc + '</div>';
                        str += '</div>';
                    });
                } else {
                    str += 'Sorry! Tags not foundation';
                }
                $('#tag_list_data').html(str);
            }
        })
    });


    function inArray(needle, haystack) {
        var length = haystack.length;
        for (var i = 0; i < length; i++) {
            if (haystack[i] == needle)
                return true;
        }
        return false;
    }

    jQuery("#frm_rating_and_review").validate({
        errorClass: 'text-danger',
        errorElement: 'div',
        rules: {
            'rating_tags': {
                required: true
            }
        },
        messages: {
            'rating_tags': {
                required: "Please select atlest one tag."
            }
        }
    });
});

</script>
@endsection