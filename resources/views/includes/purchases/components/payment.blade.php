<div class="col-md-6">
    <h3 class="mb-4">Payment</h3>

    <table class="table ">
        <tr>
            <td>To pay:</td>
            <td>
                @if($purchase -> isDelivered())
                    <span class="badge badge-success">Paid</span>
                @elseif($purchase -> isCanceled())
                    <span class="badge badge-secondary">Canceled</span>
                @elseif($purchase -> isDisputed() && $purchase -> dispute -> isResolved())
                    <span class="badge badge-success">Resolved</span>
                @else
                    {{ $purchase -> coin_sum }} <span class="badge badge-info">{{ $purchase -> coin_label }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td>Address received:</td>
            <td>
                @if($purchase -> isDelivered())
                    <span class="badge badge-success">Paid</span>
                @elseif($purchase -> isCanceled())
                    <span class="badge badge-secondary">Canceled</span>
                @elseif($purchase -> isDisputed() && $purchase -> dispute -> isResolved())
                    <span class="badge badge-success">Resolved</span>
                @else
                    @if($purchase -> coin_balance == 'unavailable')
                        <span class="badge badge-danger">{{ $purchase -> coin_balance }}</span>
                    @else
                        {{ $purchase -> coin_balance }} <span class="badge badge-info">{{ $purchase -> coin_label }}</span>
                    @endif
                    @if($purchase -> enoughBalance()) <span class="badge badge-success">enough</span> @endif
                @endif
            </td>
        </tr>
        <tr>
            <td>Address:</td>
            <td><input type="text" readonly class="form-control" value="{{ $purchase -> address }}"></td>
        </tr>
        @if($purchase -> vendor_tx != "" && $purchase -> isVendor() && ! $purchase -> isDelivered() && !$purchase -> isDisputed() )
        <tr>
            <td>Bitcoin Core Command:</td>
            <td>
                <textarea rows="5" readonly class="form-control">{{ $purchase -> vendor_tx }}</textarea>
            </td>
        </tr>
        @endif
        @if($purchase -> buyer_tx != ""  && $purchase -> isBuyer() && $purchase -> isSent()&&  ! $purchase -> isDelivered() &&  !$purchase -> isDisputed())
        <tr>
            <td>Bitcoin Core Command:</td>
            <td>
                <textarea rows="5" readonly class="form-control">{{ $purchase -> buyer_tx }}</textarea>
            </td>
        </tr>
        @endif
        @if($purchase -> buyer_tx != "" && $purchase -> isDisputed() && auth() -> user() -> isAdmin() && !$purchase -> dispute -> isResolved())
        <tr>
            <td>Bitcoin Core Command for dispute:</td>
            <td>
                <textarea rows="5" readonly class="form-control">{{ $purchase -> buyer_tx }}</textarea>
            </td>
        </tr>
        @endif
        @if($purchase -> vendor_tx != "" && $purchase -> isDisputed() && !$purchase -> dispute -> isResolved() && !auth() -> user() -> isAdmin() && ( ($purchase -> buyer_id == $purchase -> winner_id && $purchase -> isBuyer()) || ($purchase -> vendor_id == $purchase -> winner_id && $purchase -> isVendor()) ) )
        <tr>
            <td>Bitcoin Core Command for dispute:</td>
            <td>
                <textarea rows="5" readonly class="form-control">{{ $purchase -> vendor_tx }}</textarea>
            </td>
        </tr>
        @endif

        @if( (((($purchase -> isVendor() && $purchase -> vendor_tx != ""  && !$purchase -> isDisputed() ) ||  ($purchase -> isBuyer() &&  $purchase -> isSent() && !$purchase -> isDisputed()  ))  &&  ! $purchase -> isDelivered()) || ( ($purchase -> isBuyer() || $purchase -> isVendor() || ( auth() -> user() -> isAdmin() &&  $purchase -> buyer_tx != "" )) &&  $purchase -> isDisputed() && !$purchase -> dispute -> isResolved() && $purchase -> vendor_tx != ""   && ( ($purchase -> buyer_id == $purchase -> winner_id && $purchase -> isBuyer()) || ($purchase -> vendor_id == $purchase -> winner_id && $purchase -> isVendor()) ||  auth() -> user() -> isAdmin() )  )) && $purchase -> type == "multisig")
         {{-- --}}
        <tr>
            <td>Sign</td>
            <td>
                <form  action="
                    @if( $purchase -> isVendor() && !$purchase -> isDisputed())
                      {{  route('profile.sales.submit.tx', $purchase) }}
                    @elseif( $purchase -> isBuyer() && !$purchase -> isDisputed())
                      {{  route('profile.purchases.delivered.tx', $purchase) }}
                    @elseif($purchase -> isDisputed() && !$purchase -> dispute -> isResolved() && $purchase -> isVendor())
                      {{  route('profile.sales.submit.tx', $purchase) }}
                    @elseif($purchase -> isDisputed() && !$purchase -> dispute -> isResolved() && $purchase -> isBuyer())
                      {{  route('profile.purchases.submit.despute.tx', $purchase) }}
                    @elseif($purchase -> isDisputed() && !$purchase -> dispute -> isResolved() && auth() -> user() -> isAdmin() )
                      {{  route('profile.purchases.delivered.tx', $purchase) }}
                    @endif"  method="POST">
                {{ csrf_field() }}

                    <textarea  name="transaction" rows="5" class="form-control" placeholder="Please use Bitcoin Core Cli to sign with above text using your private key! And put Hex string here" style="margin-bottom: 5px;" ></textarea>
                  {{-- <button id="signtx" type="button" class="btn btn-success" >Go to Sign</button> --}}

                @if(!$purchase -> isDisputed() && $purchase -> buyer_tx != "" && $purchase -> buyer_tx != null && $purchase -> type == "multisig" && $purchase -> isSent() && $purchase -> isBuyer())
                    <button href="#" class="btn btn-outline-success" type="submit" ><i class="fas fa-clipboard-check mr-2"></i> 
                       Sign & Mark as delivered
                    </button>
                @endif

                @if(!$purchase -> isDisputed() && $purchase -> buyer_tx != "" && $purchase -> buyer_tx != null && $purchase -> type == "multisig" && $purchase -> isDelivered() && $purchase -> isBuyer())
                    <button class="btn btn-outline-success" type="submit" ><i class="fas fa-clipboard-check mr-2"></i> 
                       Sign & Mark as delivered again
                    </button>
                @endif
                @if(!$purchase -> isDisputed() && $purchase -> vendor_tx != "" && $purchase -> vendor_tx != null && $purchase -> type == "multisig" && $purchase -> isPurchased() && $purchase -> isVendor())
                        <button class="btn btn-outline-success"  type="submit" ><i class="fas fa-clipboard-check mr-2"></i> 
                           Sign & Mark as sent
                        </button>
                @endif
                @if(!$purchase -> isDisputed() && $purchase -> buyer_tx != "" && $purchase -> buyer_tx != null && $purchase -> type == "multisig" && $purchase -> isSent() && $purchase -> isVendor())
                    <button class="btn btn-outline-success" type="submit"  ><i class="fas fa-clipboard-check mr-2"></i> 
                       Sign & Mark as sent again
                    </button>
                @endif
                @if($purchase -> isDisputed() && $purchase -> type == "multisig" && !$purchase -> dispute -> isResolved() && $purchase -> vendor_tx != "" && $purchase -> vendor_tx != null )
                    <button class="btn btn-outline-success" type="submit"  ><i class="fas fa-clipboard-check mr-2"></i> 
                       Sign & Resolve
                    </button>
                @endif
                </form>

            </td>
        </tr>
        @endif
        <tr>
            <td>State</td>
            <td>
                <div class="btn-group">
                    <span class="btn disabled btn-sm @if($purchase -> isPurchased()) btn-primary @else btn-outline-secondary @endif">Purchased</span>
                    {{-- @if($purchase->type=='normal') --}}
                    <span class="btn disabled btn-sm @if($purchase -> isSent()) btn-primary @else btn-outline-secondary @endif">Sent</span>
                    {{-- @endif --}}
                    <span class="btn disabled btn-sm @if($purchase -> isDelivered()) btn-primary @else btn-outline-secondary @endif">Delivered</span>
                    <span class="btn disabled btn-sm @if($purchase -> isDisputed()) btn-danger @else btn-outline-secondary @endif">Disputed</span>
                    <span class="btn disabled btn-sm @if($purchase -> isCanceled()) btn-danger @else btn-outline-secondary @endif">Canceled</span>
                </div>
            </td>
        </tr>
        <tr>
            <td>Type:</td>
            <td>{{ \App\Purchase::$types[$purchase->type] }}</td>
        </tr>
        <tr>
            <td colspan="2" class="justify-content-center text-center">
                @if(!$purchase -> isDisputed())
                    @if($purchase->isPurchased())
                        <a href="{{ route('profile.purchases.canceled.confirm', $purchase) }}"
                           class="btn btn-outline-danger"><img src="/img/winclose.ico"> Cancel purchase</a>
                    @endif

                @if($purchase->isPurchased() && $purchase->type == 'normal' && $purchase -> coin_label != "XMR")
                        <a href="http://explorerzydxu5ecjrkwceayqybizmpjjznk5izmitf2modhcusuqlid.onion/nojs/{{ $purchase -> address }}" target="_blank"
                           class="btn btn-outline-success"><img src="/img/eye.ico"> View TX</a>
                    @endif

                    @if( $purchase -> type == "multisig" && (($purchase -> vendor_tx == "" || $purchase -> vendor_tx == null) && $purchase -> isPurchased() && $purchase -> isVendor()))
                        <a href="{{ route('profile.sales.generatetx', $purchase) }}"
                           class="btn btn-outline-success"><img src="/img/gencode.ico">
                           Generate Raw Tx
                        </a>
                    @endif
                    {{-- @if($purchase->type == 'normal' && $purchase -> isPurchased() && $purchase -> isVendor()) --}}
                    @if($purchase -> type != "multisig" && $purchase -> isPurchased() && $purchase -> isVendor())
                        <a href="{{ route('profile.sales.sent.confirm', $purchase) }}"
                           class="btn btn-outline-mblue"><img src="/img/clipboards.ico"> Mark as
                            sent</a>
                    @endif


                    
                    

                     {{-- @if($purchase->type == 'normal' && $purchase -> isSent() && $purchase -> isBuyer()) --}}
                     @if($purchase -> type != "multisig" &&  $purchase -> isSent() && $purchase -> isBuyer())
                        <a href="{{ route('profile.purchases.delivered.confirm', $purchase) }}"
                           class="btn btn-outline-success"><img src="/img/clipboards.ico"> Mark as
                            delivered</a>
                    @endif
                @endif
                 @if(!$purchase->complete_tx_id == null && !$purchase->complete_tx_id == "" )
                    <a href="http://explorerzydxu5ecjrkwceayqybizmpjjznk5izmitf2modhcusuqlid.onion/nojs/{{$purchase->complete_tx_id}}" target="_blank" 
                       class="btn btn-outline-success"><img src="/img/eye.ico"> View Tx</a>
                @endif


                @if(!$purchase -> isDisputed() && ($purchase -> isBuyer() || $purchase -> isVendor()))
                    <a href="#dispute" class="btn btn-outline-danger"><img src="/img/poop.ico">
                        Dispute</a>
                @endif
                {{-- Show to vendor if it is delivered --}}
                @if($purchase -> type != "multisig" &&  $purchase -> isDelivered() && $purchase -> isVendor() && $purchase -> coin_label != "XMR")
                    <a href="http://explorerzydxu5ecjrkwceayqybizmpjjznk5izmitf2modhcusuqlid.onion/nojs/{{ $purchase -> address }}" target="_blank"
                           class="btn btn-outline-success"><img src="/img/eye.ico"> View TX</a>
                @endif
                @if($purchase -> type != "multisig" &&  $purchase -> isSent() && $purchase -> isVendor() && $purchase -> coin_label != "XMR")
                    <a href="http://explorerzydxu5ecjrkwceayqybizmpjjznk5izmitf2modhcusuqlid.onion/nojs/{{ $purchase -> address }}" target="_blank"
                           class="btn btn-outline-success"><img src="/img/eye.ico"> View TX</a>
                @endif
                {{-- Show to the winner if it is resolved --}}
                @if($purchase->hex && $purchase->isDisputed() && $purchase->dispute->isResolved() && $purchase->dispute->isWinner())
                    <div class="alert alert-warning">
                        To retrieve funds from this purchase please sign this transaction and send it.
                    </div>
                    <textarea cols="30" rows="5" class="form-control" readonly>{{ $purchase->hex }}</textarea>
                @endif

              
            </td>




        </tr>

    </table>

    {{-- Instructions for escrow --}}
    {{-- Purchased buyer--}}
    @if($purchase -> isPurchased() && $purchase -> isBuyer() && !$purchase -> enoughBalance())
        <div class="alert alert-warning text-center">
            To proceed with purchase send the enough <em>Bitcoin</em> to the address: <span
                    class="badge badge-info">{{ $purchase -> address }}</span>
        </div>
    @endif

    {{-- Purchased vendor --}}
    @if($purchase -> isVendor() && $purchase -> isPurchased() && $purchase -> enoughBalance())
        <div class="alert alert-warning text-center">
            The buyer has paid sufficient amount on the <em>Escrow</em> address. It's recommended to send the
            goods now!
        </div>
    @elseif($purchase -> isVendor() && $purchase -> isPurchased())
        <div class="alert alert-warning text-center">
            The buyer has not paid sufficient amount on the <em>Escrow</em> address. Don't send the goods now!
        </div>
    @endif

    {{-- Sent vendor --}}
    @if($purchase -> isBuyer() && $purchase -> isSent())
        <div class="alert alert-warning text-center">
            By marking this purchase as delivered you will release the funds from the address to the vendors
            address.
        </div>
    @endif


</div>

{{-- @include('includes.purchases.components.modaltx') --}}
@push('javascript')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/components/util.js')}}"></script>
@endpush