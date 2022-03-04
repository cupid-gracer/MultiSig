@extends('master.main')

@section('title','Home Page')

@section('content')

    {{--@include('includes.search')--}}
<?php
$xmrUsd = Cache::remember('xmrUsd', 60, function() {
$url = "https://min-api.cryptocompare.com/data/price?fsym=XMR&tsyms=GBP,USD,CAD,EUR";
            $json = json_decode(file_get_contents($url), true);
            $xmrUsd = $json["USD"];
            $xmrCad = $json["CAD"];
            $xmrEur = $json["EUR"];
            $xmrGbp = $json["GBP"];
    return $xmrUsd;
});
?>

</div>
            <div class="row justify-content-center">

<div class="col-md-8">
                    <h4><div class="image"><img src="/img/mmxmr.png"> MON</h4>
                    <p>
                      MON/USD: <?php  echo $xmrUsd?>
{{ \App\Marketplace\Utility\CurrencyConverter::convertToLocal($xmrUsd) }}
    <div class="row">
        <div class="col-md-3 col-sm-12" style="margin-top:2.3em">
            @include('includes.categories')
            <div class="row mt-3">
                <div class="col">
                    <div class="card ">
                        <div class="card-header">
                            Official Link Mirrors
                        </div>
                        <div class="card-body text-center">
                            @foreach(config('marketplace.mirrors') as $mirror)
                                <a href="{{$mirror}}" style="text-decoration:none;">{{$mirror}}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9 col-sm-12 mt-3 ">

            <div class="row">
                <div class="col">
                  @if (Auth::check())  <h1 class="col-10">Welcome back to {{config('app.name')}}@auth {{auth()->user()->username}}@endauth</h1>
                    <hr>
                  @else
                      <h1 class="col-10">Welcome to {{config('app.name')}}</h1>
                    <hr> @endif
                </div>
            </div>

            <div class="row">
                <div class="col">
                    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquam, aliquid cupiditate dolore enim et
                    eveniet fugiat illum ipsum itaque minus molestias nihil optio porro quisquam quo saepe sunt velit
                    veritatis.
                </div>
            </div>
            <div class="row mt-5">

                <div class="col-md-4">
                    <h4><i class="fa fa-money-bill-wave-alt text-info"></i> No deposit</h4>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium aliquid dolorem hic nisi
                        ratione repellendus suscipit totam vitae!
                    </p>
                </div>

                <div class="col-md-4">
                    <h4><i class="fa fa-shield-alt text-info"></i> Escrow</h4>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium aliquid dolorem hic nisi
                        ratione repellendus suscipit totam vitae!
                    </p>
                </div>

                <div class="col-md-4">
                    <h4><div class="image"><img src="/img/coins.ico"> Multiple-Coins</h4>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium aliquid dolorem hic nisi
                        ratione repellendus suscipit totam vitae!
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <hr>
                </div>
            </div>
            @isModuleEnabled('FeaturedProducts')
                @include('featuredproducts::frontpagedisplay')
            @endisModuleEnabled

            <div class="row mt-4">

                <div class="col-md-4">
                    <h4>
                        Top Vendors
                    </h4>
                    <hr>
                    @foreach(\App\Vendor::topVendors() as $vendor)
                        <table class="table table-borderless table-hover">
                            <tr>
                                <td>
                                    <a href="{{route('vendor.show',$vendor)}}"
                                       style="text-decoration: none; color:#212529">{{$vendor->user->username}}</a>
                                </td>
                                <td class="text-right">
                                    <span class="btn btn-sm @if($vendor->vendor->experience >= 0) btn-primary @else btn-danger @endif active"
                                          style="cursor:default">Level {{$vendor->getLevel()}}</span>

                                </td>
                            </tr>
                        </table>
                    @endforeach
                </div>
                <div class="col-md-4">
                    <h4>
                        Latest orders
                    </h4>
                    <hr>
                    @foreach(\App\Purchase::latestOrders() as $order)
                        <table class="table table-borderless table-hover">
                            <tr>
                                <td>
                                    <img class="img-fluid" height="23px" width="23px"
                                         src="{{ asset('storage/'  . $order->offer->product->frontImage()->image) }}"
                                         alt="{{ $order->offer->product->name }}">
                                </td>
                                <td>
                                    {{str_limit($order->offer->product->name,50,'...')}}
                                </td>
                                <td class="text-right">
                                    {{$order->getSumLocalCurrency()}} {{$order->getLocalSymbol()}}
                                </td>
                            </tr>
                        </table>
                    @endforeach
                </div>

                <div class="col-md-4">
                    <h4>
                        Rising vendors
                    </h4>
                    <hr>
                    @foreach(\App\Vendor::risingVendors() as $vendor)
                        <table class="table table-borderless table-hover">
                            <tr>
                                <td>
                                    <a href="{{route('vendor.show',$vendor)}}"
                                       style="text-decoration: none; color:#212529">{{$vendor->user->username}}</a>
                                </td>
                                <td class="text-right">
                                    <span class="btn btn-sm @if($vendor->vendor->experience >= 0) btn-primary @else btn-danger @endif active"
                                          style="cursor:default">Level {{$vendor->getLevel()}}</span>
                                </td>
                            </tr>
                        </table>
                    @endforeach
                </div>
</div>
@if (Auth::check())
  IM LOGGED IN.
@else
  IM LOGGED OUT.
@endif
        </div>
</div>

           
            

@stop