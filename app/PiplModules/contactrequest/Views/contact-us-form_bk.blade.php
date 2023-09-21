@extends(config("piplmodules.front-view-layout-location"))

@section("meta")
<title>Contact Us</title>
@endsection

@section("content")

<section class="container">

    <h1>Contact us</h1>

    @if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
    @endif

    <form role="form" method="post" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="row">
            <div class="col-md-7 col-sm-12">
                <fieldset>
                    <legend>Please fill below form to submit your query</legend>
                    <div class="form-group @if ($errors->has('name')) has-error @endif">
                        <label for="name" >Your name <sup>*</sup></label><input class="form-control" name="name" value="{{old('name',$user_data['name'])}}" />
                        @if ($errors->has('name'))
                        <span class="help-block">
                            <strong class="text-danger">{{ $errors->first('name') }}</strong>
                        </span>
                        @endif
                    </div>

                    <div class="form-group @if ($errors->has('email')) has-error @endif">
                        <label for="email" >Your email <sup>*</sup> </label><input class="form-control" name="email" value="{{old('email',$user_data['email'])}}" />
                        @if ($errors->has('email'))
                        <span class="help-block">
                            <strong class="text-danger">{{ $errors->first('email') }}</strong>
                        </span>
                        @endif
                    </div>

                    <div class="form-group @if ($errors->has('phone')) has-error @endif">
                        <label for="phone" >Your phone </label><input class="form-control" name="phone" value="{{old('phone')}}" />
                        @if ($errors->has('phone'))
                        <span class="help-block">
                            <strong class="text-danger">{{ $errors->first('phone') }}</strong>
                        </span>
                        @endif
                    </div>

                    @if(count($contact_categories) > 0)
                    <div class="form-group @if ($errors->has('category')) has-error @endif">
                        <label for="category" >Choose Category </label>
                        <select name="category" class="form-control">
                            <option value="">--Select--</option>
                            @foreach($contact_categories as $category)
                            <option @if(old('category')==$category->id) selected="selected" @endif value="{{$category->id}}">{{$category->name}}</option>
                            @endforeach
                        </select>

                        @if ($errors->has('category'))
                        <span class="help-block">
                            <strong class="text-danger">{{ $errors->first('category') }}</strong>
                        </span>
                        @endif

                    </div>

                    @endif

                    <div class="form-group @if ($errors->has('subject')) has-error @endif">
                        <label for="subject" >Subject <sup>*</sup> </label><input class="form-control" name="subject" value="{{old('subject')}}" />
                        @if ($errors->has('subject'))
                        <span class="help-block">
                            <strong class="text-danger">{{ $errors->first('subject') }}</strong>
                        </span>
                        @endif
                    </div>

                    <div class="form-group @if ($errors->has('message')) has-error @endif">
                        <label for="message" >Your message <sup>*</sup></label><textarea class="form-control" name="message">{{old('message')}}</textarea>

                        @if ($errors->has('message'))
                        <span class="help-block">
                            <strong class="text-danger">{{ $errors->first('message') }}</strong>
                        </span>
                        @endif
                    </div>

                    <div class="form-group @if ($errors->has('attachment')) has-error @endif">
                        <label for="attachment" >Attach a file </label><input class="form-control" name="attachment[]" multiple="multiple"  type="file" value="{{old('attachment')}}" />

                        @if ($errors->has('attachment'))
                        <span class="help-block">
                            <strong class="text-danger">{{ $errors->first('attachment') }}</strong>
                        </span>
                        @endif

                    </div>




                </fieldset>
            </div>
            <div class="col-md-4 col-sm-12 col-md-offset-1">

                <fieldset>
                    <legend>Our information</legend>


                </fieldset>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <button type="submit" class="btn btn-md btn-primary">Submit</button> 
            </div>
        </div>

    </form>

</section>
@endsection