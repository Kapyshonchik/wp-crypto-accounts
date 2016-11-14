<?php

namespace wpblockchainaccounts;

require_once __DIR__."/../plugin/CryptoAccountsPlugin.php";
require_once __DIR__."/../model/Transaction.php";
require_once __DIR__."/../utils/Singleton.php";
require_once __DIR__."/../utils/BitcoinUtil.php";

use \Exception;

/**
 * Handle notifications from block.io.
 */
class BlockIoController extends Singleton {

	/**
	 * Process incomming request.
	 */
	public function process($payload) {
		if ($payload["type"]!="address")
			return;

		$data=$payload["data"];

		if (!$data["txid"])
			throw new Exception("No transaction id");

		if (!$data["address"])
			throw new Exception("No address");

		if (!$data["balance_change"])
			throw new Exception("No amount data");

		$transaction=Transaction::findOneBy("transactionHash",$data["txid"]);

		if (!$transaction) {
			$account=Account::findOneBy("depositAddress",$data["address"]);
			if (!$account)
				throw new Exception("No matching account.");

			$transaction=new Transaction();
			$transaction->notice="Deposit";
			$transaction->transactionHash=$data["txid"];
			$transaction->toAccountId=$account->id;
			$transaction->state=Transaction::CONFIRMING;
			$transaction->amount=BitcoinUtil::toSatoshi("btc",$data["balance_change"]);
			$transaction->save();
		}
	}
}