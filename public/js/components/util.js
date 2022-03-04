$("#signtx").click(function(){
	var win = window.open('https://coinb.in/#sign', '_blank');
	if (win) {
	    //Browser has allowed it to be opened
	    win.focus();
	} else {
	    //Browser has blocked it
	    alert('Please allow popups for this website');
	}
});

$("#viewtx").click(function(event){
	event.preventDefault();
	$txid = $(this).data("txid");
	var win = window.open('https://www.blockchain.com/btc-testnet/tx/'+$txid);
	if (win) {
	    //Browser has allowed it to be opened
	    win.focus();
	} else {
	    //Browser has blocked it
	    alert('Please allow popups for this website');
	}
});