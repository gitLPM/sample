<!-- ZST Add From !-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<!-- ZST Add End !-->
@extends('web.layout')


@section('content')


<style>


    input[type="text"], input[type="email"] {


        display: block;


        height: 50px;


        width: 100%;


            border: 1px solid #ddd !important;


        margin: 10px 1px !important;


    }





    .form-container {


      flex-direction: column;


      justify-content: center;


      align-items: center;


    }


    .card-wrapper {


      background-color: #6FB7E9;


      width: 100%;


      display: flex;





    }


    .personal-information {


      background-color: #3C8DC5;


      color: #fff;


      padding: 1px 0;


      text-align: center;


    }


    h1 {


      font-size: 1.3em;


      font-family: "Roboto"


    }


    input {


      margin: 1px 0;


      padding-left: 3%;


      font-size: 14px;


    }


    input[type="text"]{


      display: block;


      height: 50px;


      width: 97%;


      border: none;


    }


    input[type="email"]{


      display: block;


      height: 50px;


      width: 97%;


      border: none;


    }


    input[type="submit"]{


      display: block;


      height: 60px;


      width: 100%;


      border: none;


      background-color: #3C8DC5;


      color: #fff;


      margin-top: 2px;


      curson: pointer;


      font-size: 0.9em;


      text-transform: uppercase;


      font-weight: bold;


      cursor: pointer;


    }


    input[type="submit"]:hover{


      background-color: #6FB7E9;


      transition: 0.3s ease;


    }


    #column-left {


      width: 46.8%;


      float: left;


      margin-bottom: 2px;


    }


    #column-right {


      width: 46.8%;


      float: right;


    }





    @media only screen and (max-width: 480px){


      body {


        width: 100%;


        margin: 0 auto;


      }


      .form-container {


        margin: 0 2%;


      }


      input {


        font-size: 1em;


      }


      #input-button {


        width: 100%;


      }


      #input-field {


        width: 96.5%;


      }


      h1 {


        font-size: 1.2em;


      }


      input {


        margin: 2px 0;


      }


      input[type="submit"]{


        height: 50px;


      }


      #column-left {


        width: 96.5%;


        display: block;


        float: none;


      }


      #column-right {


        width: 96.5%;


        display: block;


        float: none;


      }


    }


    </style>


<!-- checkout Content -->


<section class="checkout-area">





@if(session::get('paytm') == 'success')


@php Session(['paytm' => 'sasa']); @endphp


<script>


jQuery(document).ready(function() {


// executes when HTML-Document is loaded and DOM is ready


jQuery("#update_cart_form").submit();


});





</script>


@endif


@include('web.clubheader')








<section class="pro-content">





<div class="container">


<div class="page-heading-title">


<h2> @lang('website.Checkout') </h2>





</div>


</div>


<!-- checkout Content -->


<section class="checkout-area">


<div class="container">


<div class="row">


    <div class="col-xs-12">


        <ul class="error">


            @foreach($errors->all() as $error)


            <li>{{ $error }}</li>


            @endforeach


        </ul>


            @if(session()->has('message') && !empty(session()->has('message')))


                <div class="alert alert-success alert-dismissible" role="alert">


                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>


                    {{ session()->get('message') }}


                </div>


            @endif


    </div>





<div class="col-12 col-xl-8 checkout-left">


<input type="hidden" id="hyperpayresponse" value="@if(!empty(session('paymentResponse'))) @if(session('paymentResponse')=='success') {{session('paymentResponse')}} @else {{session('paymentResponse')}}  @endif @endif">

<div class="alert alert-danger alert-dismissible" id="paymentError" role="alert" style="display:none;">

<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>

@if(!empty(session('paymentResponse')) and session('paymentResponse')=='error') {{session('paymentResponseData') }} @endif

</div>

<div class="row">

<div class="checkout-module">

    <ul class="nav nav-pills mb-3 checkoutd-nav d-none d-lg-flex" id="pills-tab" role="tablist">

       <li class="nav-item">

        <a class="nav-link @if(session('step')==0) active @elseif(session('step')>0)  @endif"  id="pills-billing-tab" data-toggle="pill" href="#pills-billing" role="tab" aria-controls="pills-billing" aria-selected="false"   >@lang('website.Billing Address')</a>
        </li>

        <li class="nav-item">

            <a class="nav-link @if(session('step')==1) active @elseif(session('step')>1) @endif" @if(session('step')>=1) id="pills-shipping-tab" data-toggle="pill" href="#pills-shipping" role="tab" aria-controls="pills-shipping" aria-selected="true" @endif>

            <span class="d-flex d-lg-none">1</span>
            <span class="d-none d-lg-flex">@lang('website.Shipping Address')</span></a>

        </li>



        <li class="nav-item">
        <a class="nav-link @if(session('step')==2) active @elseif(session('step')>2)  @endif" @if(session('step')>=2) id="pills-method-tab" data-toggle="pill" href="#pills-method" role="tab" aria-controls="pills-method" aria-selected="false" @endif> @lang('website.Shipping Methods')</a>
        </li>


        <li class="nav-item">
            <a class="nav-link @if(session('step')==3) active @elseif(session('step')>3) @endif"  @if(session('step')>=3) id="pills-order-tab" data-toggle="pill" href="#pills-order" role="tab" aria-controls="pills-order" aria-selected="false"@endif>@lang('website.Order Detail')</a>
        </li>

    </ul>




    <ul class="nav nav-pills mb-3 checkoutd-nav d-flex d-lg-none" id="pills-tab" role="tablist">
        <li class="nav-item ">
        <a class="nav-link @if(session('step')==0) active @elseif(session('step')>0) active-check @endif"  id="pills-billing-tab" data-toggle="pill" href="#pills-billing" role="tab" aria-controls="pills-billing" aria-selected="false">1</a>
        </li>


        <li class="nav-item second">

            <a class="nav-link  @if(session('step')==1) active @elseif(session('step')>1) active-check @endif" @if(session('step')>=1) id="pills-shipping-tab" data-toggle="pill" href="#pills-shipping" role="tab" aria-controls="pills-shipping" aria-selected="true" @endif>2</a>
            </li>

        <li class="nav-item third">

        <a class="nav-link @if(session('step')==2) active @elseif(session('step')>2) active-check @endif" @if(session('step')>=2) id="pills-method-tab" data-toggle="pill" href="#pills-method" role="tab" aria-controls="pills-method" aria-selected="false" @endif>3</a>


        </li>
        <li class="nav-item fourth">

        <a class="nav-link @if(session('step')==3) active @elseif(session('step')>3) active-check @endif"  @if(session('step')>=3) id="pills-order-tab" data-toggle="pill" href="#pills-order" role="tab" aria-controls="pills-order" aria-selected="false"@endif>4</a>
        </li>
    </ul>



    <div class="tab-content" id="pills-tabContent">
        
        <?php 
            //    echo session('billing_details')['billing_firstname'];
            // echo "<pre>";
            //     print_r(session('billing_details'));
            // echo "</pre>";

            // echo "<pre>";
            //     print_r(session('shipping_address'));
            // echo "</pre>";

        ?>

            <div class="tab-pane fade  @if(session('step') == 0) show active @endif"  id="pills-billing" role="tabpanel" aria-labelledby="pills-billing-tab">

            <form name="signup" enctype="multipart/form-data" action="{{ URL::to('/checkout_billing_address')}}" method="post">

            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />


            <div class="form-row">

                  <div class="form-group">

                          <label for=""> @lang('website.First Name')</label>

            <input type="text" required class="form-control same_address field-validate" id="billing_firstname" name="billing_firstname" value="{{session('billing_details')['billing_firstname']}}" aria-describedby="NameHelp1" placeholder="Enter Your Name">

            <span style="color:red;" class="help-block error-content" hidden>@lang('website.Please enter your first name')</span>


            </div>



            <div class="form-group">
                 <label for=""> @lang('website.Last Name')</label>

            <input required type="text" class="form-control same_address field-validate" id="exampleInputName2" aria-describedby="NameHelp2" placeholder="Enter Your Name" @if(!empty(session('billing_address'))>0) @if(session('billing_address')->same_billing_address==1)  @endif @else  @endif  id="billing_lastname" name="billing_lastname" value="{{session('billing_details')['billing_lastname']}}">

            <span style="color:red;" class="help-block error-content" hidden>@lang('website.Please enter your last name')</span>
           </div>

            <div class="form-group">
                 <label for=""> @lang('website.Company')</label>

                  <input type="text" class="form-control same_address" id="billing_company" name="billing_company" value="{{session('billing_details')['billing_company']}}" id="exampleInputCompany1" aria-describedby="companyHelp" placeholder="Enter Your Company Name">
                     <span class="help-block error-content" hidden>@lang('website.Please enter your company name')</span>
            </div>




            <div class="form-group">

            <label for=""> @lang('website.Address')</label>

            <input required type="text" class="form-control same_address field-validate" id="exampleInputAddress1" aria-describedby="addressHelp" placeholder="Enter Your Address" @if(!empty(session('22'))>0) @if(session('billing_address')->same_billing_address==1)  @endif @else  @endif  id="billing_street" name="billing_street" value="{{session('billing_details')['billing_street']}}">

            <span style='color:red' class="help-block error-content" hidden>@lang('website.Please enter your address')</span>
            </div>

            <div class="form-group">

            <label for=""> @lang('website.Country')</label>

            <div class="input-group select-control">

              <select required class="form-control same_address_select field-validate" id="billing_countries_id" aria-describedby="countryHelp" onChange="getBillingZones();" name="billing_countries_id" @if(!empty(session('billing_address'))) @if(session('billing_address')->same_billing_address==1)  @endif @else  @endif>


            <option value=""  >@lang('website.Select Country')</option>

            @if(!empty($result['countries']))

                @foreach($result['countries'] as $countries)

            <option value="{{$countries->countries_id}}" @if(!empty(session('billing_address'))) @if(!empty(session('billing_address')->billing_countries_id))


                {{session('billing_address')->billing_countries_id == $countries->countries_id}} @else @if(session('billing_address')['billing_countries_id'] == $countries->countries_id || session('billing_address')['countries_id'] == $countries->countries_id) selected @endif @endif @endif <?php if((session('billing_details')['billing_countries_id']) == $countries->countries_id){echo "selected"; } ?>>{{$countries->countries_name}}</option>

            @endforeach
            @endif
            </select>
            <span style='color:red' class="help-block error-content" hidden>@lang('website.Please select your country')</span>

           </div>


            <span class="help-block error-content" hidden>@lang('website.Please select your country')</span>

            </div>


            <div class="form-group">

            <label for=""> @lang('website.State')</label>

            <div class="input-group select-control">

            <select required class="form-control same_address_select" name="billing_zone_id" @if(!empty(session('billing_address'))) @if(session('billing_address')->same_billing_address==1)  @endif @else  @endif id="billing_zone_id" aria-describedby="stateHelp">


            <option value="" >@lang('website.Select State')</option>

            @if(!empty($result['zones']))

            @foreach($result['zones'] as $key=>$zones)


            {{-- <option value="{{$zones->zone_id}}" @if(!empty(session('billing_address')))  @if(!empty(session('billing_address')->billing_zone_id)) @if(session('billing_address')->billing_zone_id  == $zones->zone_id) @else @if(session('billing_address')['billing_zone_id'] == $zones->zone_id || session('billing_address')['zone_id'] == $zones->zone_id) selected @endif @endif @endif>{{$zones->zone_name}}</option> --}}
            <option value="{{$zones->zone_id}}"   <?php if((session('billing_details')['billing_zone_id']) == $zones->zone_id){echo "selected"; } ?>>{{$zones->zone_name}}</option>


            @endforeach


            @endif

            <option value="-1" @if(!empty(session('billing_address'))) @if(session('billing_address')->billing_zone_id)  @else @if(session('billing_address')['billing_zone_id'] == 'Other') selected @endif @endif @endif>@lang('website.Other')</option>

            </select>


            </div>
            <span class="help-block error-content" hidden>@lang('website.Please select your state')</span>

           </div>


            <div class="form-group">

            <label for=""> @lang('website.City')</label>

            <input required type="text" class="form-control same_address field-validate" id="billing_city" name="billing_city" value="{{session('billing_details')['billing_city']}}" placeholder="Enter Your City">


            <span style='color:red' class="help-block error-content" hidden>@lang('website.Please enter your city')</span>


            </div>
                    <div class="form-group">
                          <label for=""> @lang('website.Zip/Postal Code')</label>

            <input required type="text" class="form-control same_address field-validate" id="billing_zip" name="billing_zip" value="{{session('billing_details')['billing_zip']}}" aria-describedby="zpcodeHelp" placeholder="Enter Your Zip / Postal Code">
            <!--
                <small id="zpcodeHelp" class="form-text text-muted"></small>
            !-->

            <span style='color:red' class="help-block error-content" hidden>@lang('website.Please enter your Zip/Postal Code.')</span>
            </div>



           <div class="form-group">


            <label for=""> @lang('website.Phone')</label>


            <input type="tel" tabindex="111" class="form-control same_address text field-validate"  id="billing_phone_1" name="billing_phone" value="{{session('billing_details')['billing_phone']}}" aria-describedby="numberHelp" placeholder="Enter Your Phone Number">


            <span style='color:red' class="help-block error-content" hidden>@lang('website.Please enter your valid phone number')</span>


            </div>
            </div>
                    <div class="form-row">
                        <div class="form-group">
                            <div class="form-check">
            <input class="form-check-input" type="checkbox" id="same_billing_address" value="1" name="same_billing_address" 
            @if(session('billing_details')['same_billing_address']=='1')                   
                checked  
            @endif> @lang('Same billing and shipping address')


            <small id="checkboxHelp" class="form-text text-muted"></small>
           </div>
            </div>
            </div>
            <div class="form-row">
            <div class="form-group">
            <button type="submit"  class="btn swipe-to-top btn-secondary" ><span>@lang('website.Continue')</span></button>
            </div>
           </div>
           </form>
           </div>
            @php

               // dd(session('shipping_address'));
            @endphp


    <div class="tab-pane fade @if(session('step') == 1) show active @endif" id="pills-shipping" role="tabpanel" aria-labelledby="pills-shipping-tab">

        <form name="signup" enctype="multipart/form-data" class="form-validate"  action="{{ URL::to('/checkout_shipping_address')}}" method="post">

           <input type="hidden" required name="_token" id="csrf-token" value="{{ Session::token() }}" />
                  <div class="form-row">
                      <div class="form-group">
                          <label for=""> @lang('website.First Name')</label>

            <input required type="text" required class="form-control field-validate" id="firstname" name="firstname" 
                value="@if(!empty(session('shipping_address'))){{trim(session('shipping_address')->firstname)}}@endif" aria-describedby="NameHelp1" placeholder="Enter Your Name">


                <span style="color:red;" class="help-block error-content" hidden>@lang('website.Please enter your first name')</span>


                </div>


                <div class="form-group">


                <label for=""> @lang('website.Last Name')</label>


                <input required type="text" required class="form-control field-validate" id="lastname" name="lastname" value="@if(!empty(session('shipping_address'))){{session('shipping_address')->lastname}}@endif" aria-describedby="NameHelp1" placeholder="Enter Your Last Name">


                <span style="color:red;" class="help-block error-content" hidden>@lang('website.Please enter your last name')</span>

                </div>

                <?php if(Session::get('guest_checkout') == 1){ ?>


                <div class="form-group">


                <label for=""> @lang('website.Email')</label>


                <input type="text" required class="form-control field-validate" id="email" name="email" value="@if(!empty(session('shipping_address'))){{session('shipping_address')->email}}@endif" aria-describedby="NameHelp1" placeholder="Enter Your Email">


                <span style="color:red;" class="help-block error-content" hidden>@lang('website.Please enter your email')</span>


                </div>


                <?php } ?>


                <div class="form-group">


                <label for=""> @lang('website.Company')</label>


                <input type="text" class="form-control " id="company" aria-describedby="companyHelp" placeholder="Enter Your Company Name" name="company" value="@if(!empty(session('shipping_address'))) {{session('shipping_address')->company}}@endif">


                <span style="color:red;" class="help-block error-content" hidden>@lang('website.Please enter your company name')</span>


                </div>


                <?php if($result['commonContent']['settings']['is_enable_location'] == 1){ ?>


                <div class="form-group">


                <label for=""> @lang('website.Location')</label>


                <input type="text" required class="form-control field-validate" data-toggle="modal" data-target="#mapModal" name="location" id="location" aria-describedby="addressHelp" placeholder="@lang('website.Please enter your location or click here to open map')" value="@if( !empty(session('shipping_address'))  && isset (session('shipping_address')->location) ) {{session('shipping_address')->location}}@endif">

               </div>

                <?php }?>


                <input type="hidden" name="latitude" id="latitude" value="@if(!empty(session('shipping_address')) && isset(session('shipping_address')->latitude) ) {{session('shipping_address')->latitude}}@endif">


                <input type="hidden" name="longitude" id="longitude" value="@if(!empty(session('shipping_address')) && isset(session('shipping_address')->longitude)  ) {{session('shipping_address')->longitude}}@endif">


                <div class="form-group">


                <label for=""> @lang('website.Address')</label>


                <input required type="text" required class="form-control field-validate" name="street" id="street" aria-describedby="addressHelp" placeholder="@lang('website.Please enter your address')" value="@if(!empty(session('shipping_address'))) {{session('shipping_address')->street}}@endif">


                <span style="color:red;" class="help-block error-content" hidden>@lang('website.Please enter your address')</span>


                </div>

                <div class="form-group">

                    <label for=""> @lang('website.Country')</label>

                <div class="input-group select-control">


                <select required class="form-control field-validate" id="entry_country_id" onChange="getZones();" name="countries_id" aria-describedby="countryHelp">


                <option value="" selected>@lang('website.Select Country')</option>


                @if(!empty($result['countries']))


                @foreach($result['countries'] as $countries)


                    <option value="{{$countries->countries_id}}" @if(!empty(session('shipping_address'))) @if(session('shipping_address')->countries_id == $countries->countries_id) selected @endif @endif >{{$countries->countries_name}}</option>


                @endforeach


                @endif


                </select>


                </div>


                <span style="color:red;" class="help-block error-content" hidden>@lang('website.Please select your country')</span>


                </div>


                <div class="form-group">


                <label for=""> @lang('website.State')</label>


                <div class="input-group select-control">


                <select required class="form-control field-validate" id="entry_zone_id"  name="zone_id" aria-describedby="stateHelp">


                <option value="">@lang('website.Select State')</option>


                @if(!empty($result['zones']))
                @foreach($result['zones'] as $zones)
                    <option value="{{$zones->zone_id}}" @if(!empty(session('shipping_address'))) @if(session('shipping_address')->zone_id == $zones->zone_id) selected @endif @endif >{{$zones->zone_name}}</option>
                @endforeach


                @endif

                <option value="-1" @if(!empty(session('shipping_address'))) @if(session('shipping_address')->zone_id == 'Other') selected @endif @endif>@lang('website.Other')</option>
                </select>
                </div>
                <small id="stateHelp" class="form-text text-muted"></small>
                </div>


                <div class="form-group">
                <label for=""> @lang('website.City')</label>


                <input required type="text" class="form-control field-validate" id="city" name="city" value="@if(!empty(session('shipping_address'))){{session('shipping_address')->city}}@endif" placeholder="Enter Your City">


                <span style="color:red;" class="help-block error-content" hidden>@lang('website.Please enter your city')</span>
                </div>


                <div class="form-group">

                <label for=""> @lang('website.Zip/Postal Code')</label>

                <input required type="number" class="form-control field-validate" id="postcode" aria-describedby="zpcodeHelp" placeholder="@lang('website.Enter Your Zip / Postal Code')" name="postcode" value="@if(!empty(session('shipping_address'))){{session('shipping_address')->postcode}}@endif">


                <span style="color:red;" class="help-block error-content" hidden>@lang('website.Please enter your Zip/Postal Code')</span>

               </div>
                <div class="form-group">
                <label for=""> @lang('website.Phone')</label>

                <input required type="text" class="form-control text field-validate" tabindex="111" id="delivery_phone_1" aria-describedby="numberHelp" placeholder="@lang('website.Enter Your Phone Number')" name="delivery_phone" value="@if(!empty(session('shipping_address'))){{session('shipping_address')->delivery_phone}}@endif">


                <span style="color:red;" class="help-block error-content" hidden>@lang('website.Please enter your valid phone number')</span>

                </div>
                </div>
                <div class="form-row">
                <div class="form-group">
                <button type="submit"  class="btn swipe-to-top btn-secondary">@lang('website.Continue')</button>
                </div>
                </div>
                </form>
        </div>


            <div class="tab-pane fade  @if(session('step') == 2) show active @endif" id="pills-method" role="tabpanel" aria-labelledby="pills-method-tab">

            <div class="col-12 col-sm-12 ">


            <div class="row"> 
                <p>@lang('website.Please select a prefered shipping method to use on this order')</p></div>

                <p class="notification_msg"> 
                  Free shipping of orders above $ 75 and club member get free shipping above $ 50.
                    <br>
                     ( Note : Not applicable for products that includes Lotions, gels or mist )
                </p>

            </div>
            <br>


            <form name="shipping_mehtods" method="post" id="shipping_mehtods_form" enctype="multipart/form-data" action="{{ URL::to('/checkout_payment_method')}}">


            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />

                
            <?php 
            /* ZST
                echo "<pre>";
                    print_r($result['shipping_methods']);
                echo "</pre>";
            */
            ?>

            @if(!empty($result['shipping_methods'])>0)


                <input type="hidden" name="mehtod_name" id="mehtod_name">

                <input type="hidden" name="shipping_price" id="shipping_price">

                @php                    
                // dd($result['shipping_methods']);
                    // dd($result);
                @endphp
                

            @if((float)$result['commonContent']['settings']['free_shipping_limit']*$result['currency_value'] == session('total_price'))
            {{-- @if(1<2) --}}
                <div class="heading">
                   <h2>Free Shipping</h2>
                <hr>                
             </div>
                <div class="form-check">
                    <div class="form-row">
                        <ul class="list"style="list-style:none; padding: 0px;">
                    <li>
                      <input class="shipping_data" id="Free Shipping" type="radio" name="shipping_method"     value="freeShipping" shipping_price="0" method_name="Free Shipping" checked="">

                  <label for="Free Shipping">Free Shipping --- $0  </label>
                </li>
                </ul>               
             </div>
                </div>
            @else
            
             @foreach($result['shipping_methods'] as $shipping_methods)

            {{-- {{dd($shipping_methods['name'])}} --}}


            <div class="heading">
            <h2>{{$shipping_methods['name']}}</h2>
            <hr>
            </div>
            <div class="form-check">
            <div class="form-row">

@if($shipping_methods['success']==1)
                
    <ul class="list"style="list-style:none; padding: 0px;">

        @foreach($shipping_methods['services'] as $services)
                <?php               
                //echo "dsfdsdfsdfsdfsdf".$services['name'];              
                if($services['shipping_method']=='upsShipping')
                
                    $method_name=$shipping_methods['name'].'('.$services['name'].')';
                else{
                        $method_name=$services['name'];
                    }
                ?>
            <li>
                    <input class="shipping_data" id="{{$method_name}}" type="radio" name="shipping_method" value="{{$services['shipping_method']}}" shipping_price="{{$services['rate']}}"  method_name="{{$method_name}}" @if(!empty(session('shipping_detail')) and !empty(session('shipping_detail')) > 0)

            @if(session('shipping_detail')->mehtod_name == $method_name) checked @endif

            @elseif($shipping_methods['is_default']==1) checked @endif


            @if($shipping_methods['is_default']==1) checked @endif
            >
            <label for="{{$method_name}}">{{$shipping_methods['name']}} ---    {{Session::get('symbol_left')}}{{$services['rate']* session('currency_value')}}{{Session::get('symbol_right')}}

           </label>
            </li>
            
        @endforeach
     </ul>
@else
    <ul class="list"style="list-style:none; padding: 0px;">
       <li>@lang('website.Your location does not support this') {{$shipping_methods['name']}}.</li>
     </ul>
@endif
    </div>

          </div>
            @endforeach
            @endif
            @endif
            <div class="alert alert-danger alert-dismissible error_shipping" role="alert" style="display:none;">

            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            @lang('website.Please select your shipping method')
            </div>
            <div class="row">
            <div class="col-12 col-sm-12">
            <button type="submit"class="btn swipe-to-top btn-secondary" id="continue_btn">@lang('website.Continue')</button>

           </div>           
        </div>
            </form>

            </div>


            <div class="tab-pane fade @if(session('step') == 3) show active @endif" id="pills-order" role="tabpanel" aria-labelledby="pills-method-order">          

            <?php
            $price = 0;
            ?>
            <form method='POST' id="update_cart_form" action='{{ URL::to('/place_order')}}' >
            {!! csrf_field() !!}
            <table class="table top-table">
                
         
            @foreach( $result['cart'] as $products)

            <?php
                     $orignal_price = $products->final_price * session('currency_value');
                      $price+= $orignal_price * $products->customers_basket_quantity;
            ?>
            <tbody>
            <tr class="d-flex">
            <td class="col-12 col-md-2" >
            <input type="hidden" name="cart[]" value="{{$products->customers_basket_id}}">
            <a href="{{ URL::to('/product-detail/'.$products->products_slug)}}" class="cart-thumb">
            <img class="img-fluid" src="{{asset('').$products->image_path}}" alt="{{$products->products_name}}" alt="">
            </a>
            </td>


            <td class="col-12 col-md-5 justify-content-start">
               <div class="item-detail">
                   <span class="pro-info">
            @if($products->select_category_slug)

            @php
                $category = DB::table('categories')

                        ->leftJoin('categories_description', 'categories_description.categories_id', '=', 'categories.categories_id')

                        ->leftJoin('products_to_categories', 'products_to_categories.categories_id', '=', 'categories.categories_id')

                        ->where('products_to_categories.products_id', $products->products_id)

                        ->where('categories.parent_id', 0)

                        ->where('categories.categories_status', 1)

                        ->where('language_id', Session::get('language_id'))->first();

            // dd($category);


            @endphp
        {{-- {{dd($products)}} --}}

            <a href="{{ URL::to('category/'.$category->categories_slug.'/'.$products->select_category_slug)}}">{{$category->categories_name}} -> {{$products->select_category_name}}</a>


            @else





            <?php
             $cates = '';
			 $min_value = 0;
            //  echo "<pre>";
            //         print_r($products);
            //     echo "</pre>";
            //     exit;
            ?>


            @foreach($products->categories as $key=>$category)

            <?php
                $cates = $category->categories_name;
            ?>

           {{-- {{$category->categories_name}}@if(++$key === count($products->categories)) @else, @endif --}}


            @endforeach
            <?php
                echo $cates;
				$min_value = 0;
            ?>


            @endif
            </span>
            <h5 class="pro-title">
                   <a href="{{ URL::to('/product-detail/'.$products->products_slug)}}">
                         {{$products->products_name}}                       
                         
                    </a>
            </h5>

            <div class="item-attributes">

            @if(isset($products->attributes))

            @foreach($products->attributes as $attributes)

            <small>{{$attributes->attribute_name}} : {{$attributes->attribute_value}}</small>
            
            @if($products->f_price==0)
                <p style="color:red"><b>Free Product </b></p>
				<?php 
				echo $min_value = $orignal_price*$products->customers_basket_quantity;
				?>
            @endif

            @endforeach

            @endif

            </div>
            </div>

            </td>
            <?php
            $orignal_price = $products->final_price * session('currency_value');
            ?>


            <td class="item-price col-12 col-md-2"><span>{{Session::get('symbol_left')}}{{number_format($orignal_price+0,2)}}{{Session::get('symbol_right')}}</span></td>


            <td class="col-12 col-md-1">
              <div class="input-group item-quantity">
                 <input type="text" id="quantity" readonly name="quantity" class="form-control input-number" value="{{$products->customers_basket_quantity}}">
             </div>
            </td>

            <td class="align-middle item-total col-12 col-md-2 ">
                @if($products->f_price==0)
                {{Session::get('symbol_left')}}0.00
                @else
                {{Session::get('symbol_left')}}{{(number_format($orignal_price*$products->customers_basket_quantity,2))}}{{Session::get('symbol_right')}}
                @endif
            </td>
            </tr>
         </tbody>


            @endforeach
            </table>
          
          <?php
        
            if(!empty(session('coupon_discount'))){

              $coupon_amount = session('currency_value') * session('coupon_discount');

            }else{

                $coupon_amount = 0;

            }

            if(!empty(session('tax_rate'))){

                $tax_rate = session('tax_rate');

            }else{
            $tax_rate = 0;

            }


            // dd( Session::get('shipping_price'));


           if(session('step') > 2 ){

                if(!empty(Session::get('shipping_price')) and !empty(Session::get('shipping_price'))>0){

                $shipping_price = Session::get('shipping_price');
                $shipping_name = Session::get('shipping_detail');

                }
                else
                {
                    $shipping_price = 0;
                    $shipping_name = '';
                }
            }else{
                $shipping_price = 0;
            }


            // echo $shipping_price;
            // dd(session('shipping_detail')->shipping_price);
            // Checkmember Ship



            $freeshipping = array();

            $mytime = \Carbon\Carbon::now();
            $todayDate = $mytime->format("Y-m-d");
            $customer = auth()->guard('customer')->user();

            $UserMember = DB::table('user_membership')->where('user_id',  $customer->id)->where('payment_status', 'Success')->where('end_date', '>=', $todayDate)->where('plan_status', '1')->first();

			$price = $price - $min_value;
            if(isset($UserMember) && !empty($UserMember)){

                // dd($price,$tax_rate,$shipping_price);

                $memberDiscount = 0;
				

                if($price <= '50'){

                    $amount = $price;
                    $number = 1;
                    $percent = 20;
                    $subtotal = $amount * $number;
                    $discount = ($percent / 100) * $subtotal;
                    $totals = $subtotal - $discount;
                    $totals = number_format((float)$totals, 2, '.', '');
                    $memberDiscount = $price-$totals;
                    session(['member_discount_price'=>($memberDiscount)]);
                    session(['member_discount_percentages'=>($percent)]);

                }elseif($price > '50' && $price <= '99'){

                    $amount = $price;
                    $number = 1;
                    $percent = 25;
                    $subtotal = $amount * $number;
                    $discount = ($percent / 100) * $subtotal;
                    $totals = $subtotal - $discount;
                    $totals = number_format((float)$totals, 2, '.', '');
                    $memberDiscount = $price-$totals;
                    session(['member_discount_price'=>($memberDiscount)]);
                    session(['member_discount_percentages'=>($percent)]);


                }elseif($price > '99'){

                    $amount = $price;

                    $number = 1;
                    $percent = 30;
                    $subtotal = $amount * $number;
                    $discount = ($percent / 100) * $subtotal;

                    $totals = $subtotal - $discount;
                    $totals = number_format((float)$totals, 2, '.', '');
                    $memberDiscount = $price-$totals;
                    session(['member_discount_price'=>($memberDiscount)]);
                    session(['member_discount_percentages'=>($percent)]);

                }

                // If Price Greater than 50
                $total_price_shipping = ($price+$tax_rate)-$coupon_discount-$memberDiscount;
                $producIdArray = array();
                foreach( $result['cart'] as $products){
                    $producIdArray[] = $products->products_id;
                }

                $CheckProId = [0];
                $valueExist = array();
                if(isset( $producIdArray) && !empty( $producIdArray)){
                    foreach( $producIdArray as $productsid){
                       
                        if(in_array($productsid, $CheckProId)){
                             $valueExist[] = 'yes';
                        }   
                    }
                }
                // print_r($valueExist);
               

                if( !empty($valueExist)){
                    if($total_price_shipping > '49'){
                    $shipping_price = 0;
                    $shipping_detail['shipping_price'] = 0;
                    $freeshipping[] = 'yes';
                    session(['free_shipping'=> 'Free Shipping']);
                    }
                }
               

                $tax_rate = number_format((float)$tax_rate, 2, '.', '');
                $coupon_discount = number_format((float)$coupon_amount, 2, '.', '');

                //$total_price = ($price+$tax_rate+($shipping_price*session('currency_value')))-$coupon_discount;

                $total_price = ($price+$tax_rate+$shipping_price)-$coupon_discount-$memberDiscount;
                //ZST $total_price = $shipping_price;           


                // echo $total_price;

                session(['total_price'=>($total_price)]);               

            }else{

                // dd($price,$tax_rate,$shipping_price);

                $tax_rate = number_format((float)$tax_rate, 2, '.', '');
                $coupon_discount = number_format((float)$coupon_amount, 2, '.', '');

                //$total_price = ($price+$tax_rate+($shipping_price*session('currency_value')))-$coupon_discount;

                $total_price = ($price+$tax_rate+$shipping_price)-$coupon_discount;

                session(['total_price'=>($total_price)]);

            }
            // dd(session('free_shipping'));
            ?>
            </form>

            <div class="col-12 col-sm-12">
                <div class="row">
                    <div class="heading">
                         <h4>@lang('website.orderNotesandSummary')</h4>
                     </div>


            <div class="form-group" style="width:100%; padding:0;">

            <label for="exampleFormControlTextarea1">@lang('website.Please write notes of your order')</label>

            {{-- id="exampleFormControlTextarea1" --}}

            <textarea name="comments" style="height:auto !important"   class="form-control" id="order_comments" rows="5">@if(!empty(session('order_comments'))){{session('order_comments')}}@endif</textarea>

                                 </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            </div>





            <div class="col-12 col-xl-4 checkout-right cart-page-one cart-area cartBg extensivePerfume py-4">

               <div class="cartContent p-0">

               <h4>@lang('website.Order Summary')</h4>


                <h5>@lang('website.SubTotal')<span>{{Session::get('symbol_left')}}{{number_format($price+0,2)}}{{Session::get('symbol_right')}}</span></h5>

                @if($memberDiscount > 0)

                    <h5>@lang('Club Member Discount')<span>{{Session::get('symbol_left')}}{{number_format($memberDiscount+0,2)}}{{Session::get('symbol_right')}}</span></h5>

                @endif


                <h5>@lang('website.Discount')<span>{{Session::get('symbol_left')}}{{number_format((float)$coupon_discount+0,2)}}{{Session::get('symbol_right')}}</span></h5>


                <h5>@lang('website.Tax')<span>{{Session::get('symbol_left')}}{{$tax_rate}}{{Session::get('symbol_right')}}</span></h5>


                <h5>@lang('website.Shipping Cost')<span>{{Session::get('symbol_left')}}{{$shipping_price}}{{Session::get('symbol_right')}} @if($freeshipping) (Free Shipping) @endif</span></h5>


                <hr>


                <h5>@lang('website.Total')<strong>{{ Session::get('symbol_left') }} {{ number_format( (float)$total_price, 2, '.', '')  }} {{Session::get('symbol_right')}}</strong></h5>


            </div>





            <div class="col-12 col-sm-12 mb-3">


                <div class="row">
                    <div class="heading"><br>
                        <h4>@lang('website.Payment Methods')</h4>
                    </div>

                <div class="alert alert-danger error_payment" style="display:none" role="alert">

                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>

                @lang('website.Please select your payment method')
                </div>

                <form name="shipping_mehtods" method="post" id="payment_mehtods_form" enctype="multipart/form-data" action="{{ URL::to('/order_detail')}}">

                <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />

                <div class="form-group" style="width:100%; padding:0;">


                {{-- <label for="exampleFormControlTextarea1" style="width:100%; margin-bottom:30px;">@lang('website.Please select a prefered payment method to use on this order')</label> --}}

                <input id="payment_currency" type="hidden" onClick="paymentMethods();" name="payment_currency" value="{{session('currency_code')}}">


                @foreach($result['payment_methods'] as $payment_methods)

                 @if($payment_methods['active']==1)

                    @if($payment_methods['payment_method']=='braintree')

                     <input id="{{$payment_methods['payment_method']}}_public_key" type="hidden" name="public_key" value="{{$payment_methods['public_key']}}">


                <input id="{{$payment_methods['payment_method']}}_environment" type="hidden" name="{{$payment_methods['payment_method']}}_environment" value="{{$payment_methods['environment']}}">


                @if(session('step') > 2 )
                <div class="form-check form-check-inline" style="width: 100%; margin-top: 28px;">

                <input id="{{$payment_methods['payment_method']}}_label" type="radio" onClick="paymentMethods();" name="payment_method" class="form-check-input payment_method" value="{{$payment_methods['payment_method']}}" > Card Payment

                {{-- <label class="form-check-label" for="{{$payment_methods['payment_method']}}_label">
                    <img src="{{asset('web/images/miscellaneous').'/'.$payment_methods['payment_method'].'.png'}}" alt="{{$payment_methods['name']}}">
                </label> --}}


                </div>
                @endif

                @else





                <input id="{{$payment_methods['payment_method']}}_public_key" type="hidden" name="public_key" value="{{$payment_methods['public_key']}}">


                <input id="{{$payment_methods['payment_method']}}_environment" type="hidden" name="{{$payment_methods['payment_method']}}_environment" value="{{$payment_methods['environment']}}">








                <div class="form-check form-check-inline">


                <input onClick="paymentMethods();" id="{{$payment_methods['payment_method']}}_label" type="radio" name="payment_method" class="form-check-input payment_method" value="{{$payment_methods['payment_method']}}" @if(!empty(session('payment_method'))) @if(session('payment_method')==$payment_methods['payment_method']) checked @endif @endif>


                <label class="form-check-label" for="{{$payment_methods['payment_method']}}_label">


                @if(file_exists( 'web/images/miscellaneous/'.$payment_methods['payment_method'].'.png'))


                <img width="100px" src="{{asset('web/images/miscellaneous/').'/'.$payment_methods['payment_method'].'.png'}}" alt="{{$payment_methods['name']}}">


                @else


                {{$payment_methods['name']}}


                @endif


                </label>


                </div>


                @endif


                @endif





                @endforeach





                </div>


                </form>








                <div class="button">


                @foreach($result['payment_methods'] as $payment_methods)





                @if($payment_methods['active']==1 and $payment_methods['payment_method']=='banktransfer')


                <div class="alert alert-info alert-dismissible" id="payment_description" role="alert" style="display: none">


                <span>{{$payment_methods['descriptions']}}</span>


                </div>


                @endif





                @endforeach





                @if(session('step') > 2 )
                @php
                     $customer = auth()->guard('customer')->user();
                @endphp

                 <form id="checkout" method="post" action="{{ URL::to('/place_order')}}">


                    <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />


                    <div class="form-container">


                       <input id="column-left" type="text" name="first-name" value="{{$customer->first_name}}" placeholder="First Name" required="required" style="opacity: 0;; height:0px;margin: 0px !important;" />

                       <input id="column-right" type="text" name="last-name" value="{{$customer->last_name}}" placeholder="Last Name" style="opacity: 0;  height:0px;margin: 0px !important;"/>

                        <input id="input-field" type="text" name="number" placeholder="Card Number" required="required"  data-parsley-trigger="keyup"

                        data-parsley-type="number" />

                        <input id="column-left" type="text" name="expiry" placeholder="MM / YY" required="required" />

                        <input id="column-right" type="text" name="cvc" placeholder="CCV" required="required"  data-parsley-trigger="keyup"

                        data-parsley-type="number"/>



                       

                       <input id="input-field" type="text" name="streetaddress" autocomplete="on" maxlength="45" placeholder="Street Address" style="opacity: 0; height:0px;margin: 0px !important;"/>

                       <input id="column-left" type="text" name="city" autocomplete="on" maxlength="20" placeholder="City" style="opacity: 0 ; height:0px;margin: 0px !important;"/>

                       <input id="column-right" type="text" name="zipcode" autocomplete="on" pattern="[0-9]*" maxlength="5" placeholder="ZIP code" style="opacity: 0; height:0px;margin: 0px !important;"/>

                       <input id="input-field" type="email" name="email" value="{{$customer->email}}" required="required" autocomplete="on" maxlength="40" placeholder="Email" style="opacity: 0"/>

                        <input id="input-field" type="text" name="amount" value="@lang('website.Pay') {{ Session::get('symbol_left') }} {{ number_format( (float)$total_price+0, 2, '.', '') }} {{ Session::get('symbol_right') }}" autocomplete="on" maxlength="40" placeholder="Amount" readonly/>

                        <input id="input-button" name="submit" type="submit" value="Submit"/>
                        <div class="card-wrapper" style="opacity: 0; height: 0px;"></div>

                    </div>





                    <!-- Modal Header -->


                    {{-- <div class="modal-header">


                    <button type="button" class="close" data-dismiss="modal">&times;</button>


                    </div> --}}


                    {{-- <div class="modal-body">


                    <div id="payment-form"></div>


                    </div>


                    <div class="modal-footer">


                    <button type="submit" class="btn btn-dark">@lang('website.Pay') {{ Session::get('symbol_left') }} {{ number_format( (float)$total_price+0, 2, '.', '') }} {{ Session::get('symbol_right') }}</button>


                    </div> --}}


                </form>




                @endif
                @if(session('step') > 2 )
                <form id="checkout2" method="post" action="{{ URL::to('/place_order')}}">
                    <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
                    <input id="paypal_label" type="radio" name="payment_method" class=" " value="paypal" > Paypal Payment
                    <input id="input-button_paypal" class="btn btn-dark" name="submit" type="submit" value="Order Now"/>
                    {{-- <button id="banktransfer_button" class="btn btn-dark payment_btns">@lang('website.Order Now')</button> --}}
                </form>
                @endif

                <!--- paypal -->


                <div id="paypal_button" class="payment_btns" style="display: none"></div>





                {{-- <button id="braintree_button" class="btn btn-dark payment_btns" data-toggle="modal" data-target="#braintreeModel" >@lang('website.Order Now')</button> --}}





                <button id="stripe_button" class="btn btn-dark payment_btns" style="display: none" data-toggle="modal" data-target="#stripeModel" >@lang('website.Order Now')</button>





                <button id="cash_on_delivery_button" class="btn btn-dark payment_btns" style="display: none">@lang('website.Order Now')</button>


                <button id="razor_pay_button" class="razorpay-payment-button btn btn-dark payment_btns"  style="display: none"  type="button">@lang('website.Order Now')</button>


                <a href="{{ URL::to('/store_paytm')}}" id="pay_tm_button" class="btn btn-dark payment_btns"  style="display: none"  type="button">@lang('website.Order Now')</a>





                <button id="instamojo_button" class="btn btn-dark payment_btns" style="display: none" data-toggle="modal" data-target="#instamojoModel">@lang('website.Order Now')</button>





                <a href="{{ URL::to('/checkout/hyperpay')}}" id="hyperpay_button" class="btn btn-dark payment_btns" style="display: none">@lang('website.Order Now')</a>


                <button id="banktransfer_button" class="btn btn-dark payment_btns" style="display: none">@lang('website.Order Now')</button>


                <button id="paystack_button" class="btn btn-dark payment_btns" style="display: none">@lang('website.Order Now')</button>





                <button id="midtrans_button" class="btn btn-dark payment_btns" style="display: none">@lang('website.Order Now')</button>


                <input type="hidden" id="midtransToken" value="">


                {{-- payment error content show --}}


                <div class="alert alert-danger alert-dismissible" id="payment_error" role="alert" style="display: none">


                <span class="sr-only">@lang('website.Error'):</span>


                <span id="payment_error-error-text"></span>


                </div>





                </div>


                </div>


                <!-- The braintree Modal -->


                <div class="modal fade" id="braintreeModel">


                <div class="modal-dialog">


                <div class="modal-content">


                <form id="checkout" method="post" action="{{ URL::to('/place_order')}}">


                <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />


                <!-- Modal Header -->


                <div class="modal-header">


                <h4 class="modal-title">@lang('website.BrainTree Payment')</h4>


                <button type="button" class="close" data-dismiss="modal">&times;</button>


                </div>


                <div class="modal-body">


                <div id="payment-form"></div>


                </div>


                <div class="modal-footer">


                <button type="submit" class="btn btn-dark">@lang('website.Pay') {{ Session::get('symbol_left') }} {{ number_format( (float)$total_price+0, 2, '.', '') }} {{ Session::get('symbol_right') }}</button>


                </div>


                </form>


                </div>


                </div>


                </div>





                <!-- The instamojo Modal -->


                <div class="modal fade" id="instamojoModel">


                <div class="modal-dialog">


                <div class="modal-content">


                <form id="instamojo_form" method="post" action="">


                <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />


                <input type="hidden" name="amount" value="{{number_format((float)$total_price+0, 2, '.', '')}}">


                <!-- Modal Header -->


                <div class="modal-header">


                <h4 class="modal-title">@lang('website.Instamojo Payment')</h4>


                <button type="button" class="close" data-dismiss="modal">&times;</button>


                </div>


                <div class="modal-body">


                <div class="from-group mb-3">


                    <div class="col-12"> <label for="inlineFormInputGroup">@lang('website.Full Name')</label></div>


                    <div class="input-group col-12">


                        <input type="text" name="firstName" id="firstName" placeholder="@lang('website.Full Name')" class="form-control">


                        <span class="help-block error-content" hidden>@lang('website.Please enter your full name')</span>


                    </div>


                </div>


                <div class="from-group mb-3">


                    <div class="col-12"> <label for="inlineFormInputGroup">@lang('website.Email')</label></div>


                    <div class="input-group col-12">


                        <input type="text" name="email_id" id="email_id" placeholder="@lang('website.Email')" class="form-control">


                        <span class="help-block error-content" hidden>@lang('website.Please enter your email address')</span>


                    </div>


                </div>


                <div class="from-group mb-3">


                    <div class="col-12"> <label for="inlineFormInputGroup">@lang('website.Phone Number')</label></div>


                    <div class="input-group col-12">


                        <input type="text" name="phone_number" id="insta_phone_number" placeholder="@lang('website.Phone Number')" class="form-control">


                        <span class="help-block error-content" hidden>@lang('Please enter your valid phone number')</span>


                    </div>


                </div>





                <div class="alert alert-danger alert-dismissible" id="insta_mojo_error" role="alert" style="display: none">


                <span class="sr-only">@lang('website.Error'):</span>


                <span id="instamojo-error-text"></span>


                </div>


                </div>


                <div class="modal-footer">


                <button type="button" id="pay_instamojo" class="btn btn-dark">@lang('website.Pay') {{ Session::get('symbol_left') }} {{ number_format( (float)$total_price+0, 2, '.', '') }} {{ Session::get('symbol_right') }}</button>


                </div>


                </form>


                </div>


                </div>


                </div>





                <!-- The stripe Modal -->


                <div class="modal fade" id="stripeModel">


                <div class="modal-dialog">


                <div class="modal-content">





                <main>


                <div class="container-lg">


                <div class="cell example example2">


                <form>


                <div class="row">


                <div class="field">


                <div id="example2-card-number" class="input empty"></div>


                <label for="example2-card-number" data-tid="elements_examples.form.card_number_label">@lang('website.Card number')</label>


                <div class="baseline"></div>


                </div>


                </div>


                <div class="row">


                <div class="field half-width">


                <div id="example2-card-expiry" class="input empty"></div>


                <label for="example2-card-expiry" data-tid="elements_examples.form.card_expiry_label">@lang('website.Expiration')</label>


                <div class="baseline"></div>


                </div>


                <div class="field half-width">


                <div id="example2-card-cvc" class="input empty"></div>


                <label for="example2-card-cvc" data-tid="elements_examples.form.card_cvc_label">@lang('website.CVC')</label>


                <div class="baseline"></div>


                </div>


                </div>


                <button type="submit" class="btn btn-dark" data-tid="elements_examples.form.pay_button">@lang('website.Pay') {{$web_setting[19]->value}}{{number_format((float)$total_price+0, 2, '.', '')}}</button>





                <div class="error" role="alert"><svg xmlns="https://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17">


                <path class="base" fill="#000" d="M8.5,17 C3.80557963,17 0,13.1944204 0,8.5 C0,3.80557963 3.80557963,0 8.5,0 C13.1944204,0 17,3.80557963 17,8.5 C17,13.1944204 13.1944204,17 8.5,17 Z"></path>


                <path class="glyph" fill="#FFF" d="M8.5,7.29791847 L6.12604076,4.92395924 C5.79409512,4.59201359 5.25590488,4.59201359 4.92395924,4.92395924 C4.59201359,5.25590488 4.59201359,5.79409512 4.92395924,6.12604076 L7.29791847,8.5 L4.92395924,10.8739592 C4.59201359,11.2059049 4.59201359,11.7440951 4.92395924,12.0760408 C5.25590488,12.4079864 5.79409512,12.4079864 6.12604076,12.0760408 L8.5,9.70208153 L10.8739592,12.0760408 C11.2059049,12.4079864 11.7440951,12.4079864 12.0760408,12.0760408 C12.4079864,11.7440951 12.4079864,11.2059049 12.0760408,10.8739592 L9.70208153,8.5 L12.0760408,6.12604076 C12.4079864,5.79409512 12.4079864,5.25590488 12.0760408,4.92395924 C11.7440951,4.59201359 11.2059049,4.59201359 10.8739592,4.92395924 L8.5,7.29791847 L8.5,7.29791847 Z"></path>


                </svg>


                <span class="message"></span></div>


                </form>


                <div class="success">


                    <div class="icon">


                    <svg width="84px" height="84px" viewBox="0 0 84 84" version="1.1" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="https://www.w3.org/1999/xlink">


                        <circle class="border" cx="42" cy="42" r="40" stroke-linecap="round" stroke-width="4" stroke="#000" fill="none"></circle>


                        <path class="checkmark" stroke-linecap="round" stroke-linejoin="round" d="M23.375 42.5488281 36.8840688 56.0578969 64.891932 28.0500338" stroke-width="4" stroke="#000" fill="none"></path>


                    </svg>


                    </div>


                    <h3 class="title" data-tid="elements_examples.success.title">@lang('website.Payment successful')</h3>


                    <p class="message"><span data-tid="elements_examples.success.message">@lang('website.Thanks You Your payment has been processed successfully')</p>


                </div>





                </div>


                </div>


                </main>


                </div>


                </div>


                </div>





                </div>


            </div>


            </div>


            </div>


            </section>


            </section>














<!-- map model code start -->


<div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-modal="true">





<div class="modal-dialog modal-dialog-centered modal-lg " role="document">


<div class="modal-content">


<div class="modal-body">





    <div class="container">


        <div class="row align-items-center">





        <div class="form-group">


<input type="text" id="pac-input" name="address_address" class="form-control map-input">


</div>


<div id="address-map-container" style="width:100%;height:400px; ">


<div style="width: 100%; height: 100%" id="map"></div>


</div>


    </div>


    <button type="button" class="close" data-dismiss="modal" aria-label="Close">


        <span aria-hidden="true">×</span>


        </button>


    </div>


</div>


<div class="modal-footer">





<button type="button" class="btn btn-primary" onclick="setUserLocation()"><i class="fas fa-location-arrow"></i></button>


<button type="button" class="btn btn-secondary" onclick="saveAddress()">Save</button>


</div>


</div>


</div>


</div>


<!-- map modal code end -->


<style>


.pac-container{


z-index: 1052 !important;


}


.pac-card {


margin: 10px 10px 0 0;


border-radius: 2px 0 0 2px;


box-sizing: border-box;


-moz-box-sizing: border-box;


outline: none;


box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);


background-color: #fff;


font-family: Roboto;


}





#pac-container {


padding-bottom: 12px;


margin-right: 12px;


}





.pac-controls {


display: inline-block;


padding: 5px 11px;


}





.pac-controls label {


font-family: Roboto;


font-size: 13px;


font-weight: 300;


}





#pac-input {


background-color: #fff;


font-family: Roboto;


font-size: 15px;


font-weight: 300;


margin-left: 12px;


padding: 0 11px 0 13px;


text-overflow: ellipsis;


width: 400px;


}





#pac-input:focus {


border-color: #4d90fe;


}





#title {


color: #fff;


background-color: #4d90fe;


font-size: 25px;


font-weight: 500;


padding: 6px 12px;


}


#target {


width: 345px;


}


</style>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/121761/card.js"></script>
<script src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/121761/jquery.card.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js" integrity="sha512-eyHL1atYNycXNXZMDndxrDhNAegH2BDWt1TmkXJPoGf1WLlNYt08CSjkqF5lnCRmdm3IrkHid8s2jOUY4NIZVQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


<script>
$('document').ready(function() {

    $("#input-button_paypal").hide();
    $("#input-button").hide();

    $('input:radio[name="payment_method"]').change(function(){

        if ($(this).val() == 'paypal') {
        $("#input-button_paypal").show();
        $('#braintree_label').prop("checked", false);
        $("#input-button").hide();
    }
    if ($(this).val() == 'braintree') {
        $('#paypal_label').prop("checked", false);
        $("#input-button_paypal").hide();
        $("#input-button").show();
    }
    });

});


$('form').card({
    container: '.card-wrapper',
    width: 280,
});

jQuery(document).on('click', '#cash_on_delivery_button, #banktransfer_button', function(e){


jQuery("#update_cart_form").submit();


});


</script>


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


jQuery("#update_cart_form").submit();


console.log(r);


}


})


return false;


})


</script>





<script>


function padStart(str) {


return ('0' + str).slice(-2)


}





function demoSuccessHandler(transaction) {


// You can write success code here. If you want to store some data in database.


jQuery("#paymentDetail").removeAttr('style');


jQuery('#paymentID').text(transaction.razorpay_payment_id);


var paymentDate = new Date();


jQuery('#paymentDate').text(


padStart(paymentDate.getDate()) + '.' + padStart(paymentDate.getMonth() + 1) + '.' + paymentDate.getFullYear() + ' ' + padStart(paymentDate.getHours()) + ':' + padStart(paymentDate.getMinutes())


);





jQuery.ajax({


method: 'post',


url: "{!!route('dopayment')!!}",


data: {


"_token": "{{ csrf_token() }}",


"razorpay_payment_id": transaction.razorpay_payment_id


},


complete: function (r) {


jQuery("#update_cart_form").submit();


console.log(r);


}


})


}


</script>


<?php





if(!empty($result['payment_methods'][6]) and $result['payment_methods'][6]['active'] == 1){


$rezorpay_key =  $result['payment_methods'][6]['RAZORPAY_KEY'];


if(!empty($result['commonContent']['setting'][79]->value)){


$name = $result['commonContent']['setting'][79]->value;


}else{


$name = Lang::get('website.Ecommerce');


}





$logo = $result['commonContent']['setting'][15]->value;


?>


<script>


var options = {


key: "{{ $rezorpay_key }}",


amount: '<?php echo (float) round($total_price, 2)*100;?>',


name: '{{$name}}',


image: '{{$logo}}',


handler: demoSuccessHandler


}


</script>


<script>


window.r = new Razorpay(options);


document.getElementById('razor_pay_button').onclick = function () {


r.open()


}


</script>





<?php


}





foreach($result['payment_methods'] as $payment_methods){


if($payment_methods['active']==1 and $payment_methods['payment_method']=='midtrans'){


if($payment_methods['environment'] == 'Live'){


print '<script src="https://app.midtrans.com/snap/snap.js" data-client-key="'.$payment_methods['public_key'].'"></script>';


}else{


print '<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="'.$payment_methods['public_key'].'"></script>';





}


}


}











?>





<script>


jQuery( document ).ready( function () {


var midtrans_environment = jQuery('#midtrans_environment').val();


if(midtrans_environment !== undefined){


midtrans_environment = midtrans_environment;


}else{


midtrans_environment = ';'


}


});





</script>








<script type="text/javascript">


document.getElementById('midtrans_button').onclick = function(){


var tokken = jQuery('#midtransToken').val();


// SnapToken acquired from previous step


snap.pay(tokken, {


// Optional


onSuccess: function(result){


// alert('onSuccess');


// /* You may add your own js here, this is just example */ document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);


paymentSuccess(JSON.stringify(result, null, 2));


},


// Optional


onPending: function(result){


// alert('onPending');


/* You may add your own js here, this is just example */ document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);


},


// Optional


onError: function(result){


jQuery('#payment_error').show();


var response = JSON.stringify(result, null, 2);


// alert('error');


/* You may add your own js here, this is just example */ document.getElementById('payment_error-error-text').innerHTML += result.status_message;


}


});


};


</script>


@if($result['commonContent']['settings']['google_map_api'])


<script src="https://maps.googleapis.com/maps/api/js?key=<?=$result['commonContent']['settings']['google_map_api']?>&libraries=places&callback=initialize" async defer></script>


<script>


var markers;


var myLatlng;


var map;


var geocoder;


function setUserLocation(){


if (navigator.geolocation) {


navigator.geolocation.getCurrentPosition(function(position) {


myLatlng = {


lat: position.coords.latitude,


lng: position.coords.longitude


};





markers.setPosition(myLatlng);


map.setCenter(myLatlng);





}, function() {


});


}


}


function saveAddress(){


var latlng = markers.getPosition();


geocoder.geocode({'location': latlng}, function(results, status) {


if (status === 'OK') {


if (results[0]) {


var street = "";


var state = "";


var country = "";


var city = "";


var postal_code = "";





for (var i = 0; i < results[0].address_components.length; i++) {


    for (var b = 0; b < results[0].address_components[i].types.length; b++) {


        switch (results[0].address_components[i].types[b]) {


            case 'locality':


                city = results[0].address_components[i].long_name;


                break;


            case 'administrative_area_level_1':


                state = results[0].address_components[i].long_name;


                break;


            case 'country':


                country = results[0].address_components[i].long_name;


                break;


            case 'postal_code':


                postal_code =  results[0].address_components[i].long_name;


                break;


            case 'route':


                if (street == "") {


                street = results[0].address_components[i].long_name;


                }


            break;





            case 'street_address':


                if (street == "") {


                street += ", " + results[0].address_components[i].long_name;


                }


            break;


        }


    }


}


$("#postcode").val(postal_code);


$("#street").val(street);


$("#city").val(city);





$("#latitude").val(markers.getPosition().lat());


$("#longitude").val(markers.getPosition().lng());





// $("#entry_country_id").val(country);





$("#location").val(latlng);





$("#entry_country_id option").filter(function() {


    //may want to use $.trim in here


    return $(this).text() == country;


}).prop('selected', true);


if(getZones("no_loader")){


    $("#entry_zone_id option").filter(function() {


    //may want to use $.trim in here


    return $(this).text() == state;


    }).prop('selected', true);


}


$('#mapModal').modal('hide');





} else {


console.log('No results found');


}


} else {


console.log('Geocoder failed due to: ' + status);


}


});


}





function initialize() {


defaultPOS = {


lat: <?=$result['commonContent']['setting'][126]->value?>,


lng: <?=$result['commonContent']['setting'][127]->value?>


};


map = new google.maps.Map(document.getElementById('map'), {


center: defaultPOS,


zoom: 13,


mapTypeId: 'roadmap'


});


geocoder = new google.maps.Geocoder;


markers = new google.maps.Marker({


map: map,


draggable:true,


position: defaultPOS


});











var infowindow = new google.maps.InfoWindow;


// Create the search box and link it to the UI element.


var input = document.getElementById('pac-input');


var searchBox = new google.maps.places.SearchBox(input);


map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);





map.addListener('bounds_changed', function() {


searchBox.setBounds(map.getBounds());


});





searchBox.addListener('places_changed', function() {


var places = searchBox.getPlaces();





if (places.length == 0) {


return;


}





var bounds = new google.maps.LatLngBounds();





places.forEach(function(place) {


if (!place.geometry) {


console.log("Returned place contains no geometry");


return;


}


var icon = {


url: place.icon,


size: new google.maps.Size(71, 71),


origin: new google.maps.Point(0, 0),


anchor: new google.maps.Point(17, 34),


scaledSize: new google.maps.Size(25, 25)


};


console.log(place.geometry.location);


// Create a marker for each place.


markers.setPosition(place.geometry.location);


markers.setTitle(place.name);








if (place.geometry.viewport) {


// Only geocodes have viewport.


bounds.union(place.geometry.viewport);


} else {


bounds.extend(place.geometry.location);


}


});


map.fitBounds(bounds);


});


}





</script>


@endif





@endsection


<style>
.notification_msg
{
    color:green;
    text-align:center;
    font-weight:bold;
}
</style>


<!-- ZST Add From !-->

 <script>
function phoneFormatter() {
 $('.text').attr({ placeholder : '(___) ___-____' });
  $('input[tabindex="111"]').on('input', function() {
    var number = $(this).val().replace(/[^\d]/g, '')
    if (number.length == 7) {
      number = number.replace(/(\d{3})(\d{4})/, "$1-$2");
    } else if (number.length == 10) {
      number = number.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
     
    }
    $(this).val(number)
    $('.text').attr({ maxLength : 10 });
    
  });
};

$(phoneFormatter);
</script>
<!-- ZST Add End !-->

