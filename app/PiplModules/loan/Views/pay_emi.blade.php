@extends(config("piplmodules.back-view-layout-location"))

@section("meta")

<title>Pay EMI</title>

@endsection

@section('content')
<div class="page-content-wrapper">
    <div class="page-content">
        <!-- BEGIN PAGE BREADCRUMB -->
        <ul class="page-breadcrumb breadcrumb">
            <li>
                <a href="{{url('admin/dashboard')}}">Dashboard</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/loan-list')}}">Manage Loan</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="{{url('admin/loan-emi-list')}}/{{$emi_detail->loan_id}}">Manage Loan EMI</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <a href="javascript:void(0);">Pay EMI</a>
            </li>
        </ul>
        <!-- BEGIN SAMPLE FORM PORTLET-->
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Pay EMI
                </div>

            </div>
            <div class="portlet-body form">
                <form class="form-horizontal" name="frm_pay_emi"  id="rzp-footer-form" role="form" action="" method="post" enctype="multipart/form-data">
                    {!! csrf_field() !!}
              
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">    
                                <div class="col-md-8">  
                                    <div class="form-group @if ($errors->has('emi')) has-error @endif">
                                        <label class="col-md-6 control-label">EMI Amount<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="emi" readonly="" value="₹{{old('emi',$emi_detail->emi)}}" />
                                            
                                        </div>
                                    </div>
                                    <div class="form-group @if ($errors->has('emi_date')) has-error @endif">
                                        <label class="col-md-6 control-label">EMI Date<sup>*</sup></label>
                                        <div class="col-md-6">     
                                            <input class="form-control" name="emi_date" readonly="" value="{{old('emi_date',$emi_detail->emi_date)}}"  />
                                            
                                        </div>
                                    </div>
                                     
                                    <div class="form-group">
                                        <div class="col-md-12">   
                                            <button type="buton" id="paybtn" class="btn btn-primary  pull-right">Pay Now</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                
                <div id="paymentDetail" style="display: none">
                    <center>
                        <div>paymentID: <span id="paymentID"></span></div>
                        <div>paymentDate: <span id="paymentDate"></span></div>
                    </center>
                </div>
                
            </div>
        </div>
    </div>
</div>
</div>
<style>
    .submit-btn{
        padding: 10px 0px 0px 18px;
    }
    .thumb-image {
        margin: 10px;
    }
</style>
<script>
    $('#rzp-footer-form').submit(function (e) {
        var button = $(this).find('button');
        var parent = $(this);
        button.attr('disabled', 'true').html('Please Wait...');
        $.ajax({
            method: 'get',
            url: this.action,
            data: $(this).serialize(),
            complete: function (r) {
                console.log('complete');
                console.log(r);
            }
        });
        return false;
    });
</script>

<script>
    function padStart(str) {
        return ('0' + str).slice(-2);
    }

    function demoSuccessHandler(transaction) {
        // You can write success code here. If you want to store some data in database.
        $("#paymentDetail").removeAttr('style');
        $('#paymentID').text(transaction.razorpay_payment_id);
        var paymentDate = new Date();
        $('#paymentDate').text(
                padStart(paymentDate.getDate()) + '.' + padStart(paymentDate.getMonth() + 1) + '.' + paymentDate.getFullYear() + ' ' + padStart(paymentDate.getHours()) + ':' + padStart(paymentDate.getMinutes())
                );

        $.ajax({
            method: 'post',
            url: '{{url("admin/dopayment")}}',
            data: {
                "_token": "{{ csrf_token() }}",
                "razorpay_payment_id": transaction.razorpay_payment_id,
                "loan_emi_id":"{{$emi_detail->id}}",
                "loan_id":"{{$emi_detail->loan_id}}"
            },
            dataType: 'json',
            complete: function (r) {
                console.log('complete');
                console.log(r);
                alert("EMI paid successfully.");
                window.location.href = '{{url("admin/loan-emi-list")}}/{{$emi_detail->loan_id}}';
            }
        });
    }
</script>
<script>
    
    var options = {
        key: "rzp_test_xuwgB0d9EP1hxp",
        amount: '{{$emi_detail->emi*100}}',
        name: 'Loan EMI Payment',
        description: 'Loan EMI Payment',
        image: 'https://i.imgur.com/n5tjHFD.png',
        handler: demoSuccessHandler
    };
</script>
<script>
    window.r = new Razorpay(options);
    document.getElementById('paybtn').onclick = function () {
        r.open();
    };
</script>
@endsection