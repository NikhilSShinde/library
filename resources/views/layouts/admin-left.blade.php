<?php
$segments = Request::segment(2);
$segment_prameter = '';
$segment_value = '';

switch ($segments) {
    case 'manage-roles':
        $segment_prameter = 'role';
        $segment_value = 'global';
        break;    
    case 'send-notification-to-user':
        $segment_prameter = 'send-notification-to-user';
        $segment_value = 'send-notification-to-user';
        break;
    case 'update-role':
        $segment_prameter = 'role';
        $segment_value = 'global';
        break;
    case 'roles':
        $segment_prameter = 'role';
        $segment_value = 'global';
        break;
    case 'global-settings':
        $segment_prameter = 'globalsetting';
        $segment_value = 'global';
        break;
    case 'update-global-setting':
        $segment_prameter = 'globalsetting';
        $segment_value = 'global';
        break;
    case 'countries':
        $segment_prameter = 'countries';
        $segment_value = 'global';
        break;
    case 'preferred-language':
        $segment_prameter = 'preferred-language';
        $segment_value = 'global';
        break;
    case 'states':
        $segment_prameter = 'states';
        $segment_value = 'global';
        break;
    case 'cities':
        $segment_prameter = 'cities';
        $segment_value = 'global';
        break;
    case 'admin-users':
        $segment_prameter = 'admin-users';
        $segment_value = 'user';
        break;
    case 'manage-users':
        $segment_prameter = 'register-user';
        $segment_value = 'user';
        break;
    case 'manage-users':
        $segment_prameter = 'star-user';
        $segment_value = 'user';
        break;
    case 'company-users':
        $segment_prameter = 'company-users';
        $segment_value = 'user';
        break;
    case 'agent-users':
        $segment_prameter = 'agent-users';
        $segment_value = 'user';
        break;
    case 'free-toner-users':
        $segment_prameter = 'free-toner-users';
        $segment_value = 'user';
        break;
    case 'star-users':
        $segment_prameter = 'star-users';
        $segment_value = 'user';
        break;
    case 'content-pages':
        $segment_prameter = 'content-pages';
        $segment_value = 'cms';
        break;
    case 'email-templates':
        $segment_prameter = 'email-template';
        $segment_value = 'email';
        break;
    case 'categories-list':
        $segment_prameter = 'category';
        $segment_value = 'category';
        break;
    case 'category':
        $segment_prameter = 'category';
        $segment_value = 'category';
        break;
    case 'contact-request-categories':
        $segment_value = 'contact';
        break;
    case 'contact-requests':
        $segment_value = 'contact';
        break;
    case 'faq-categories':
        $segment_value = 'faq';
        break;
    case 'faqs':
        $segment_value = 'faq';
        break;
    case 'blog-categories':
        $segment_value = 'blog';
        break;
    case 'blog':
        $segment_value = 'blog';
        break;
    case 'testimonials':
        $segment_value = 'testimonial';
        break;
    case 'newsletters':
        $segment_value = 'newsletters';
        break;
    case 'services-list':
        $segment_value = 'services';
        break;
    case 'service':
        $segment_value = 'services';
        break;
    case 'support-tickets':
        $segment_value = 'support';
        break;
    case 'coupons':
        $segment_value = 'coupons';
        break;
    case 'coupon':
        $segment_value = 'coupons';
        break;
    case 'wallet-history':
        $segment_value = 'wallet';
        break;
    case 'order-list':
        $segment_value = 'orders';
        break;
    case 'order-view':
        $segment_value = 'orders';
        break;
    case 'rating-review':
        $segment_value = 'rating';
        break;
    case 'reports':
        $segment_value = 'star-user-report';
        break;
    case 'reports':
        $segment_value = 'order-report-list';
        break;
    case 'users-payments':
        $segment_value = 'manage-users-payments';
        break;
    case 'loan':
        $segment_value = 'loan';
        break;
}
?>
<div class="page-sidebar-wrapper">

    <div class="page-sidebar navbar-collapse collapse">

        <ul class="page-sidebar-menu " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
            <li class="start active ">
                @if(Auth::user()->userInformation->user_type=="1")

                <a href="{{url("admin/dashboard")}}">

                    @endif 
                    @if(Auth::user()->userInformation->user_type=="4")

                    <a href="{{url("agent/dashboard")}}">

                        @endif

                        @if(Auth::user()->userInformation->user_type=="5")

                        <a href="{{url("company/dashboard")}}">

                            @endif
                            @if(Auth::user()->userInformation->user_type=="6")

                            <a href="{{url("agent-manager/dashboard")}}">

                                @endif
                                <i class="icon-home"></i>
                                <span class="title">Dashboard</span>
                            </a>
                            </li>
                            @if(Auth::user()->hasPermission('view.manage-countries')==true ||Auth::user()->hasPermission('view.manage-cities')==true|| Auth::user()->isSuperadmin())
                            <li  class="@if($segment_value=='global') open @endif">
                                <a href="javascript:void(0);">
                                    <i class="glyphicon glyphicon-cog"></i>
                                    <span class="title">Manage Global Values</span>
                                    <span class="arrow"></span>
                                </a>
                                <ul class="sub-menu" @if($segment_value=='global') style='display:block' @endif>

                                    @if(Auth::user()->hasPermission('view.roles')==true || Auth::user()->isSuperadmin())
                                    <li class="@if($segment_prameter=='role') active @endif"> 
                                        <a href="{{url('admin/manage-roles')}}">
                                            <i class="glyphicon glyphicon-check"></i> Manage Roles {{ Route::getCurrentRoute()->getPrefix()}}
                                        </a>
                                    </li>
                                    @endif

                                    @if(Auth::user()->hasPermission('view.global-settings')==true || Auth::user()->isSuperadmin())
                                    <li class="@if($segment_prameter=='globalsetting') active @endif"> 
                                        <a href="{{url('admin/global-settings')}}">
                                            <i class="glyphicon glyphicon-cog"></i> Manage Global Settings
                                        </a>
                                    </li>
                                    @endif

                                    @if(Auth::user()->hasPermission('view.manage-countries')==true || Auth::user()->isSuperadmin())
                                    <li class="@if($segment_prameter=='countries') active @endif"> 
                                        <a href="{{url('admin/countries/list')}}">
                                            <i class="glyphicon glyphicon-globe"></i> Manage Countries
                                        </a>
                                    </li>
                                    @endif
<!--                                    @if(Auth::user()->hasPermission('view.manage-spokenlanguage')==true || Auth::user()->isSuperadmin())
                                    <li class="@if($segment_prameter=='preferred-language') active @endif"> 
                                        <a href="{{url('admin/preferred-language/list')}}">
                                            <i class="glyphicon glyphicon-globe"></i> Manage Preferred Language
                                        </a>
                                    </li>
                                    @endif-->
                                    @if(Auth::user()->hasPermission('view.manage-states')==true || Auth::user()->isSuperadmin())
                                    <li class="@if($segment_prameter=='states') active @endif"> 
                                        <a href="{{url('admin/states/list')}}">
                                            <i class="glyphicon glyphicon-globe"></i> Manage Regions
                                        </a>
                                    </li>
                                    @endif
                                    @if(Auth::user()->hasPermission('view.manage-cities')==true || Auth::user()->isSuperadmin())
                                    <li class="@if($segment_prameter=='cities') active @endif"> 
                                        <a href="{{url('admin/cities/list')}}">
                                            <i class="glyphicon glyphicon-globe"></i> Manage Cities
                                        </a>
                                    </li>
                                    @endif
                                    @if(Auth::user()->hasPermission('view.manage-cities')==true || Auth::user()->isSuperadmin())
                                    <li class="@if($segment_prameter=='geo') active @endif"> 
                                        <a href="{{url('admin/city-geo-settings/list')}}">
                                            <i class="glyphicon glyphicon-globe"></i> Manage Cities GEO limits
                                        </a>
                                    </li>
                                    @endif
                                    
                                    
                                     @if(Auth::user()->hasPermission('view.send-notification')==true || Auth::user()->isSuperadmin())
                                        <li class="@if($segment_prameter=='send-notification-to-user') active @endif"> 
                                            <a href="{{url('admin/send-notification-to-user')}}">
                                            <i class="fa fa-envelope"></i> Send Notification
                                            </a>
                                        </li>
                                     @endif
                                </ul>
                            </li>
                            @endif
                             @if(Auth::user()->hasPermission('view.star-users')==true || Auth::user()->hasPermission('view.registered-users')==true || Auth::user()->hasPermission('view.free-toner-users')==true || Auth::user()->hasPermission('view.agent-manager-users')==true || Auth::user()->hasPermission('view.company-users')==true || Auth::user()->hasPermission('view.agent-users')==true || Auth::user()->hasPermission('view.admin-users')==true || Auth::user()->isSuperadmin())    
                            <li  class="@if($segment_value=='user') open @endif">
                                <a href="javascript:void(0);">
                                    <i class="icon-user"></i>
                                    <span class="title">Manage Users</span>
                                    <span class="arrow"></span>
                                </a>
                                <ul class="sub-menu" @if($segment_value=='user') style='display:block' @endif>
                                    @if(Auth::user()->hasPermission('view.admin-users')==true || Auth::user()->isSuperadmin())

                                    <li class="@if($segment_prameter=='admin-users') active @endif"> 
                                        <a href="{{url('admin/admin-users')}}">
                                            Manage Admin Users</a>
                                    </li>
                                    @endif
                                    
                                    
<!--                                    @if(Auth::user()->hasPermission('view.agent-manager-users')==true || Auth::user()->isSuperadmin())

                                    <li class="@if($segment_prameter=='agent-managers-users') active @endif"> 
                                        <a href="{{url('admin/agent-managers-users')}}">
                                            Manage Agent Manager Users</a>
                                    </li>
                                    @endif-->
                                    
                                    @if(Auth::user()->hasPermission('view.agent-users')==true || Auth::user()->isSuperadmin())

                                    <li class="@if($segment_prameter=='agent-users') active @endif"> 
                                        <a href="{{url('admin/agent-users')}}">
                                            Manage Agent Users</a>
                                    </li>
                                    @endif
<!--                                    @if(Auth::user()->hasPermission('view.company-users')==true || Auth::user()->isSuperadmin())

                                    <li class="@if($segment_prameter=='company-users') active @endif"> 
                                        <a href="{{url('admin/company-users')}}">
                                            Manage Company Users</a>
                                    </li>
                                    @endif-->
<!--                                    @if(Auth::user()->hasPermission('view.free-toner-users')==true || Auth::user()->isSuperadmin())

                                    <li class="@if($segment_prameter=='free-toner-users') active @endif"> 
                                        <a href="{{url('admin/free-toner-users')}}">
                                            Manage Free Toner Users</a>
                                    </li>
                                    @endif-->
                                    @if(Auth::user()->hasPermission('view.registered-users')==true || Auth::user()->isSuperadmin())

                                    <li class="@if($segment_prameter=='register-user') active @endif"> 
                                        <a href="{{url('admin/manage-users')}}">
                                            Manage Customers</a>
                                    </li>
                                    @endif
                                    @if((Auth::user()->hasPermission('view.star-users')==true) || Auth::user()->isSuperadmin())

                                    <li class="@if($segment_prameter=='star-users') active @endif"> 
                                        <a href="{{url('admin/star-users')}}">
                                            Manage Drivers</a>
                                    </li>
                                    @endif

                                </ul>
                            </li>
                           @endif 
                          @if(Auth::user()->hasPermission('view.order-list')==true || Auth::user()->isSuperadmin())
                            <li>
                               <a href="javascript:void(0);">
                                 <i class="fa fa-bar-chart-o"></i>
                                 <span class="title">Manage Trips</span>
                                 <span class="arrow"></span>
                               </a>
                               <ul class="sub-menu" @if($segment_value=='orders' || $segment_value=='pending_orders' || $segment_value=='active_orders' ||$segment_value=='completed_orders' ||$segment_value=='expired_orders') style='display:block' @endif>
                                @if(Auth::user()->hasPermission('view.order-list')==true || Auth::user()->isSuperadmin())
                                        <li class="@if($segment_prameter=='pending_orders') active @endif"> 
                                            <a href="{{url("/admin/order-list/pending")}}">
                                             <i class="fa fa-money"></i> Pending Trips
                                            </a>
                                        </li>
                                        <li class="@if($segment_prameter=='active_orders') active @endif"> 
                                            <a href="{{url("/admin/order-list/active")}}">
                                            <i class="fa fa-money"></i> Active Trips
                                            </a>
                                        </li>
                                        <li class="@if($segment_prameter=='completed_orders') active @endif"> 
                                            <a href="{{url("/admin/order-list/completed")}}">
                                            <i class="fa fa-money"></i> Completed Trips
                                            </a>
                                        </li>
                                        <li class="@if($segment_prameter=='expired_orders') active @endif"> 
                                            <a href="{{url("/admin/order-list/expired")}}">
                                            <i class="fa fa-money"></i> Expired Trips
                                            </a>
                                        </li>
                                        <li class="@if($segment_prameter=='manage-users-payments') active @endif"> 
                                            <a href="{{url("/admin/order-list/cancelled")}}">
                                            <i class="fa fa-money"></i> Canceled Trips
                                            </a>
                                        </li>
                               @endif         
                                </ul>
                            </li>
                          
                            @endif
                              @if(Auth::user()->hasPermission('view.wallet.history')==true || Auth::user()->hasPermission('manage-users-payments')==true || Auth::user()->isSuperadmin())
                            <li>
                                <a href="javascript:void(0);">
                                    <i class="fa fa-bar-chart-o"></i>
                                    <span class="title">Manage Payment</span>
                                    <span class="arrow"></span>
                                </a>
                                <ul class="sub-menu" @if($segment_value=='wallet' || $segment_value=='manage-users-payments') style='display:block' @endif>
                                @if(Auth::user()->hasPermission('view.manage-users-payments')==true || Auth::user()->isSuperadmin())
                                        <li class="@if($segment_prameter=='manage-users-payments') active @endif"> 
                                            <a href="{{url('admin/users-payments/list')}}">
                                            <i class="fa fa-money"></i> Manage Payments
                                            </a>
                                        </li>
                                
                              @endif   
                              @if(Auth::user()->hasPermission('view.wallet.history')==true || Auth::user()->isSuperadmin())
                                  
                                <li class="start @if($segment_value=='wallet') active @endif">
                                    <a href="{{url("/admin/wallet-history")}}">
                                        <i class="icon-list"></i>
                                        <span class="title">Wallet Transaction History</span>
                                    </a>
                                </li>
                                @endif 

                                </ul>
                            </li>                            
                            @endif
                             @if(Auth::user()->hasPermission('view.report.list')==true || Auth::user()->isSuperadmin())

                            <li>
                                <a href="javascript:void(0);">
                                    <i class="fa fa-bar-chart-o"></i>
                                    <span class="title">Manage Reports</span>
                                    <span class="arrow"></span>
                                </a>
                                <ul class="sub-menu" @if($segment_value=='reports') style='display:block' @endif>
                                    @if(Auth::user()->hasPermission('view.report.list')==true || Auth::user()->isSuperadmin())

                                    <li>
                                        <a href="{{url("/admin/reports/order-report-list")}}">Trips Reports</a>
                                    </li>

                                    @endif

                                    @if(Auth::user()->hasPermission('view.starreport.list')==true || Auth::user()->isSuperadmin())

                                    <li>
                                        <a href="{{url("admin/reports/star-user-report")}}">Driver User Reports</a>
                                    </li>
                                    @endif

                                    @if(Auth::user()->hasPermission('view.revenuereport.list')==true || Auth::user()->isSuperadmin())

                                    <li>
                                        <a href="{{url("/admin/reports/revenue")}}">Revenue Reports</a>
                                    </li>
                                    @endif

                                </ul>
                            </li>                            
                            @endif
                             @if(Auth::user()->hasPermission('view.support-ticket')==true || Auth::user()->isSuperadmin())

                            <li class="start @if($segment_value=='support') active @endif">
                                <a href="{{url("/admin/support-tickets")}}">
                                    <i class="icon-list"></i>
                                    <span class="title">Manage Support Tickets</span>
                                </a>
                            </li>
                            @endif
                            @if(Auth::user()->hasPermission('view.contact-requests')==true || Auth::user()->isSuperadmin())
                            <li>
                                <a href="javascript:void(0);">
                                    <i class="icon-envelope"></i>
                                    <span class="title">Manage Contact Us</span>
                                    <span class="arrow"></span>
                                </a>
                                <ul class="sub-menu">
                                    @if(Auth::user()->hasPermission('view.contact-requests')==true || Auth::user()->isSuperadmin())

                                    <li>
                                        <a href="{{url("admin/contact-request-categories")}}">Manage Contact Categories</a>
                                    </li>

                                    @endif

                                    @if(Auth::user()->hasPermission('view.contact-requests')==true || Auth::user()->isSuperadmin())

                                    <li>
                                        <a href="{{url("admin/contact-requests")}}">Manage Contact Requests</a>
                                    </li>
                                    @endif

                                </ul>
                            </li>
                            @endif
                            @if(Auth::user()->hasPermission('view.vehicle-list')==true || Auth::user()->isSuperadmin())
                            <li class="start @if($segment_value=='vehicle') active @endif">
                                <a href="{{url("/admin/vehicle-list")}}">
                                    <i class="icon-list"></i>
                                    <span class="title">Manage Vehicle</span>
                                </a>
                            </li>
                            @endif
                             @if(Auth::user()->hasPermission('view.rating-review')==true || Auth::user()->isSuperadmin())
                            <li  class="@if($segment_value=='rating') open @endif">
                                <a href="javascript:void(0);">
                                    <i class="icon-user"></i>
                                    <span class="title">Manage Rating And Review</span>
                                    <span class="arrow @if($segment_value=='rating') open @endif"></span>
                                </a>
                                <ul class="sub-menu" @if($segment_value=='rating') style='display:block' @endif>
                                    @if(Auth::user()->hasPermission('view.rating-review')==true || Auth::user()->isSuperadmin())
                                    <li class="start @if($segment_value=='rating') active @endif">
                                        <a href="{{url("/admin/rating-review/list")}}">
                                            <i class="icon-list"></i>
                                            <span class="title">Manage Rating</span>
                                        </a>
                                    </li>
                                    @endif
                                    @if(Auth::user()->hasPermission('view.rating-review')==true || Auth::user()->isSuperadmin())
                                    <li class="start @if($segment_value=='rating') active @endif">
                                        <a href="{{url("/admin/rating-review/tags-list")}}">
                                            <i class="icon-list"></i>
                                            <span class="title">Manage Rating Tags</span>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </li>
                            @endif
                            @if(Auth::user()->hasPermission('view.categories')==true || Auth::user()->isSuperadmin())

                            <li class="start @if($segment_value=='category') active @endif">
                                <a href="{{url("/admin/categories-list")}}">
                                    <i class="icon-list"></i>
                                    <span class="title">Manage Categories</span>
                                </a>
                            </li>
                            @endif
                            @if(Auth::user()->hasPermission('view.service')==true || Auth::user()->isSuperadmin())

                            <li class="start @if($segment_value=='services') active @endif">
                                <a href="{{url("/admin/services-list")}}">
                                    <i class="icon-list"></i>
                                    <span class="title">Manage Services</span>
                                </a>
                            </li>
                            @endif
                         
                           
                             @if(Auth::user()->hasPermission('view.content-pages')==true || Auth::user()->isSuperadmin())
                            <li class="start @if($segment_value=='cms') active @endif">
                                <a href="{{url("admin/content-pages/list")}}">
                                    <i class="icon-list"></i>
                                    <span class="title">Manage CMS Pages</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Auth::user()->hasPermission('view.email-templates')==true || Auth::user()->isSuperadmin())

                            <li  class="start @if($segment_value=='email') active @endif">
                                <a href="{{url("/admin/email-templates/list")}}">
                                    <i class="icon-list"></i>
                                    <span class="title">Manage Email template</span>
                                </a>
                            </li>
                            @endif

<!--                            @if(Auth::user()->hasPermission('view.coupon-list')==true || Auth::user()->isSuperadmin())

                            <li class="start @if($segment_value=='coupons') active @endif">
                                <a href="{{url("/admin/coupons")}}">
                                    <i class="icon-list"></i>
                                    <span class="title">Manage Coupons</span>
                                </a>
                            </li>
                            @endif-->
                           
                            @if(Auth::user()->hasPermission('view.slider.images')==true || Auth::user()->isSuperadmin())

                            <li class="start @if($segment_value=='tutorials') active @endif">
                                <a href="{{url("admin/tutorials-list")}}">
                                    <i class="icon-list"></i>
                                    <span class="title">Manage Tutorials Image</span>
                                </a>
                            </li>
                            @endif

                            
                            @if(Auth::user()->hasPermission('view.faqs')==true || Auth::user()->isSuperadmin())
                            <li>
                                <a href="javascript:void(0);">
                                    <i class="icon-question"></i>
                                    <span class="title">Manage Faq's</span>
                                    <span class="arrow"></span>
                                </a>
                                <ul class="sub-menu">
                                    @if(Auth::user()->hasPermission('view.faq-categories')==true || Auth::user()->isSuperadmin())

                                    <li>
                                        <a href="{{url("admin/faq-categories")}}">Manage Faq Categories</a>
                                    </li>

                                    @endif

                                    @if(Auth::user()->hasPermission('view.faqs')==true || Auth::user()->isSuperadmin())

                                    <li>
                                        <a href="{{url("admin/faqs")}}">Manage Faq's</a>
                                    </li>
                                    @endif

                                </ul>
                            </li>
                            @endif
                            
                            @if(Auth::user()->hasPermission('view.loan')==true || Auth::user()->isSuperadmin())
                            <li>
                                <a href="javascript:void(0);">
                                    <i class="icon-question"></i>
                                    <span class="title">Manage Loan</span>
                                    <span class="arrow"></span>
                                </a>
                                <ul class="sub-menu">                                   
                                    @if(Auth::user()->hasPermission('view.loan')==true || Auth::user()->isSuperadmin())
                                    <li>
                                        <a href="{{url("admin/loan-list")}}">Manage Loan</a>
                                    </li>
                                    @endif
                                </ul>
                            </li>
                            @endif

<!--                            @if(Auth::user()->hasPermission('view.blog')==true || Auth::user()->isSuperadmin())
                            <li>
                                <a href="javascript:void(0);">
                                    <i class="icon-question"></i>
                                    <span class="title">Manage Blog</span>
                                    <span class="arrow"></span>
                                </a>
                                <ul class="sub-menu">
                                    @if(Auth::user()->hasPermission('view.blog-categories')==true || Auth::user()->isSuperadmin())

                                    <li>
                                        <a href="{{url("admin/blog-categories")}}">Manage Blog Categories</a>
                                    </li>

                                    @endif

                                    @if(Auth::user()->hasPermission('view.blog')==true || Auth::user()->isSuperadmin())

                                    <li>
                                        <a href="{{url("/admin/blog")}}">Manage Blog</a>
                                    </li>
                                    @endif

                                </ul>
                            </li>
                            @endif-->




                            </ul>
                            <!-- END SIDEBAR MENU -->
                            </div>
                            </div>
