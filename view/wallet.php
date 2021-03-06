<?php if (!defined("IN_WALLET")) { die("u can't touch this."); } ?>
<?php
if (!empty($error))
{
    echo "<p style='font-weight: bold; color: red;'>" . $error['message']; "</p>";
}
?>
<p>Hello, <strong><?php echo $user_session; ?></strong>! <?php if ($admin) {?><strong><font color="red">[Admin]</font><?php }?></strong></p>
<p>Current balance: <strong id="balance"><?php echo satoshitize($balance); ?></strong> <?=$short?></p>

<form action="index.php" method="POST">

<?php
if ($admin)
{
  ?>
<h4>Admin Links:</h4>
  <a href="?a=home" class="btn btn-default">Admin Dashboard</a>
<h4>User Links:</h4>
  <?php
}
?>
        <input type="hidden" name="action" value="logout" />
        <button type="submit" class="btn btn-default">Log out</button>
</form>

<br />
<p>Update your password:</p>
<form action="index.php" method="POST" class="clearfix" id="pwdform">
    <input type="hidden" name="action" value="password" />
    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
    <div class="col-md-2"><input type="password" class="form-control" name="oldpassword" placeholder="Current password"></div>
    <div class="col-md-2"><input type="password" class="form-control" name="newpassword" placeholder="New password"></div>
    <div class="col-md-2"><input type="password" class="form-control" name="confirmpassword" placeholder="Confirm new password"></div>
    <div class="col-md-2"><button type="submit" class="btn btn-default">Update password</button></div>
</form>
<p id="pwdmsg"></p>
<br />
<p>Withdraw funds:</p>
<button type="button" class="btn btn-default" id="donate">Donate to <?=$fullname?> wallet's owner!</button><br />
<p id="donateinfo" style="display: none;">Type the amount you want to donate and click <strong>Withdraw</strong></p>
<form action="index.php" method="POST" class="clearfix" id="withdrawform">
    <input type="hidden" name="action" value="withdraw" />
    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
    <div class="col-md-4"><input type="text" class="form-control" name="address" placeholder="Address"></div>
    <div class="col-md-2"><input type="text" class="form-control" name="amount" placeholder="Amount"></div>
    <div class="col-md-2"><button type="submit" class="btn btn-default">Withdraw</button></div>
</form>
<p id="withdrawmsg"></p>
<br />
<p>Your addresses:</p>
<form action="index.php" method="POST" id="newaddressform">
	<input type="hidden" name="action" value="new_address" />
	<button type="submit" class="btn btn-default">Get a new address</button>
</form>
<p id="newaddressmsg"></p>
<br />
<table class="table table-bordered table-striped" id="alist">
<thead>
<tr>
<td>Address:</td>
</tr>
</thead>
<tbody>
<?php
foreach ($addressList as $address)
{
echo "<tr><td>".$address."</td></tr>\n";
}
?>
</tbody>
</table>
<p>Last 10 transactions:</p>
<table class="table table-bordered table-striped" id="txlist">
<thead>
   <tr>
      <td nowrap>Date</td>
      <td nowrap>Address</td>
      <td nowrap>Type</td>
      <td nowrap>Amount</td>
      <td nowrap>Fee</td>
      <td nowrap>Confs</td>
      <td nowrap>Info</td>
   </tr>
</thead>
<tbody>
   <?php
   $bold_txxs = "";
   foreach($transactionList as $transaction) {
      if($transaction['category']=="send") { $tx_type = '<b style="color: #FF0000;">Sent</b>'; } else { $tx_type = '<b style="color: #01DF01;">Received</b>'; }
      echo '<tr>
               <td>'.date('n/j/Y h:i a',$transaction['time']).'</td>
               <td>'.$transaction['address'].'</td>
               <td>'.$tx_type.'</td>
               <td>'.abs($transaction['amount']).'</td>
               <td>'.$transaction['fee'].'</td>
               <td>'.$transaction['confirmations'].'</td>
               <td><a href="' . $blockchain_url,  $transaction['txid'] . '" target="_blank">Info</a></td>
            </tr>';
   }
   ?>
   </tbody>
</table>
<script type="text/javascript">
var blockchain_url = "<?=$blockchain_url?>";
$("#withdrawform input[name='action']").first().attr("name", "jsaction");
$("#newaddressform input[name='action']").first().attr("name", "jsaction");
$("#pwdform input[name='action']").first().attr("name", "jsaction");
$("#donate").click(function (e){
  $("#donateinfo").show();
  $("#withdrawform input[name='address']").val("<?=$donation_address?>");
  $("#withdrawform input[name='amount']").val("0.01");
});
$("#withdrawform").submit(function(e)
{
    var postData = $(this).serializeArray();
    var formURL = $(this).attr("action");
    $.ajax(
    {
        url : formURL,
        type: "POST",
        data : postData,
        success:function(data, textStatus, jqXHR) 
        {
            var json = $.parseJSON(data);
            if (json.success)
            {
              $("#withdrawform input.form-control").val("");
            	$("#withdrawmsg").text(json.message);
            	$("#withdrawmsg").css("color", "green");
            	$("#withdrawmsg").show();
            	updateTables(json);
            } else {
            	$("#withdrawmsg").text(json.message);
            	$("#withdrawmsg").css("color", "red");
            	$("#withdrawmsg").show();
            }
            if (json.newtoken)
            {
              $('input[name="token"]').val(json.newtoken);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) 
        {
            //ugh, gtfo    
        }
    });
    e.preventDefault();
});
$("#newaddressform").submit(function(e)
{
    var postData = $(this).serializeArray();
    var formURL = $(this).attr("action");
    $.ajax(
    {
        url : formURL,
        type: "POST",
        data : postData,
        success:function(data, textStatus, jqXHR) 
        {
            var json = $.parseJSON(data);
            if (json.success)
            {
            	$("#newaddressmsg").text(json.message);
            	$("#newaddressmsg").css("color", "green");
            	$("#newaddressmsg").show();
            	updateTables(json);
            } else {
            	$("#newaddressmsg").text(json.message);
            	$("#newaddressmsg").css("color", "red");
            	$("#newaddressmsg").show();
            }
            if (json.newtoken)
            {
              $('input[name="token"]').val(json.newtoken);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) 
        {
            //ugh, gtfo    
        }
    });
    e.preventDefault();
});
$("#pwdform").submit(function(e)
{
    var postData = $(this).serializeArray();
    var formURL = $(this).attr("action");
    $.ajax(
    {
        url : formURL,
        type: "POST",
        data : postData,
        success:function(data, textStatus, jqXHR) 
        {
            var json = $.parseJSON(data);
            if (json.success)
            {
               $("#pwdform input.form-control").val("");
               $("#pwdmsg").text(json.message);
               $("#pwdmsg").css("color", "green");
               $("#pwdmsg").show();
            } else {
               $("#pwdmsg").text(json.message);
               $("#pwdmsg").css("color", "red");
               $("#pwdmsg").show();
            }
            if (json.newtoken)
            {
              $('input[name="token"]').val(json.newtoken);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) 
        {
            //ugh, gtfo    
        }
    });
    e.preventDefault();
});

function updateTables(json)
{
	$("#balance").text(json.balance.toFixed(8));
	$("#alist tbody tr").remove();
	for (var i = json.addressList.length - 1; i >= 0; i--) {
		$("#alist tbody").prepend("<tr><td>" + json.addressList[i] + "</td></tr>");
	}
	$("#txlist tbody tr").remove();
	for (var i = json.transactionList.length - 1; i >= 0; i--) {
		var tx_type = '<b style="color: #01DF01;">Received</b>';
		if(json.transactionList[i]['category']=="send")
		{
			tx_type = '<b style="color: #FF0000;">Sent</b>';
		}
		$("#txlist tbody").prepend('<tr> \
               <td>' + moment(json.transactionList[i]['time'], "X").format('l hh:mm a') + '</td> \
               <td>' + json.transactionList[i]['address'] + '</td> \
               <td>' + tx_type + '</td> \
               <td>' + Math.abs(json.transactionList[i]['amount']) + '</td> \
               <td>' + json.transactionList[i]['fee'] + '</td> \
               <td>' + json.transactionList[i]['confirmations'] + '</td> \
               <td><a href="' + blockchain_url.replace("%s", json.transactionList[i]['txid']) + '" target="_blank">Info</a></td> \
            </tr>');
	}
}
</script>
