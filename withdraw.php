<?php

	require_once __DIR__."/src/utils/WpUtil.php";
	require_once __DIR__."/src/model/Transaction.php";
	require_once __DIR__."/src/model/Account.php";

	use wpblockchainaccounts\WpUtil;
	use wpblockchainaccounts\Transaction;
	use wpblockchainaccounts\Account;

	require_once WpUtil::getWpLoadPath();

	$account=Account::getCurrentUserAccount();

	if (!$account)
		return "<i>not logged in</i>";

	try {
		if (!$_REQUEST["address"])
			throw new Exception("Please enter the address to withdraw to.");

		if (!$_REQUEST["amount"])
			throw new Exception("Please enter the amount to withdraw.");

		$address=$_REQUEST["address"];
		$amount=$_REQUEST["amount"];

		$_REQUEST["address"]="";
		$_REQUEST["amount"]="";

		$t=$account->withdraw($_REQUEST["denomination"],$address,$amount);

		switch ($t->getState()) {
			case Transaction::COMPLETE:
				$_SESSION["bca_withdraw_success"]=
					"The withdrawal has been processed.";
				break;

			case Transaction::SCHEDULED:
				$_SESSION["bca_withdraw_success"]=
					"Your withdrawal has been initiated.<br/>".
					"Please see your account history for progress.";
				break;

			default:
				throw new Exception("Unknown transaction state.");
				break;
		}
	}

	catch (Exception $e) {
		$_SESSION["bca_withdraw_error"]=$e->getMessage();
		$_SESSION["bca_withdraw_address"]=$_REQUEST["address"];
		$_SESSION["bca_withdraw_amount"]=$_REQUEST["amount"];
	}

	header("Location: ".$_REQUEST["afterWithdraw"]);