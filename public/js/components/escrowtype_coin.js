var types = ["fe", "normal", "multisig"];
var coins = ["btc", "btcm", "xmr", "stb"];

$(document).ready(function(){

	var isEnableMultisig = $("#isEnableMultisig").val();
	if(!isEnableMultisig)
	{
		$("#types option").each(function(){
			if($(this).val() == "multisig") $(this).remove();
		});
		$("#coins option").each(function(){
			if($(this).val() == "btcm") $(this).remove();
		});
	}

	if( $("#types").val() == "multisig" )
	{
		$("#coins option").each(function(){
			if($(this).val() != "btcm")
			{
				$(this).attr("hidden", true );
				$(this).removeAttr("selected");
			}
			else $(this).removeAttr("hidden" );
		});
	}
	else
	{
		if( $("#types").val().includes("multisig"))
		{
			// $("#coins option").each(function(){
			// 	if($(this).val() == "btcm") $(this).attr("selected", true );
			// });
			$(this).removeAttr("hidden");
		}
		else 
		$("#coins option").each(function(){
			if($(this).val() == "btcm") $(this).attr("hidden", true);
			else $(this).removeAttr("hidden");
		});
	}


	$("#types").change(function(){
		if( $("#types").val() == "multisig" )
		{
			$("#coins option").each(function(){
				if($(this).val() != "btcm")
				{
					$(this).attr("hidden", true );
					$(this).removeAttr("selected" );
				}
				else $(this).removeAttr("hidden" );
			});
		}
		else
		{
			if( $("#types").val().includes("multisig") )
			{
				$("#coins option").each(function(){
					// if($(this).val() == "btcm") $(this).attr("selected", true );
					$(this).removeAttr("hidden" );
				});
			}
			else
			$("#coins option").each(function(){
				if($(this).val() == "btcm")
				{ 
					$(this).attr("hidden", true );
					$(this).removeAttr("selected");
				}
				else $(this).removeAttr("hidden" );
			});
		}
	});

	// $("#coins").change(function(){
	// 	$("#coins option").each(function(){
	// 		if($(this).val() == "btcm") $(this).attr("selected", true );
	// 	});
	// });
});