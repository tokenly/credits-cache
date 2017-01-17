<?php

namespace Tokenly\CreditsCache;

class CreditBalanceChanged {

    var $group_id;
    var $account_uuid;
    var $new_balance;

    function __construct($group_id, $account_uuid, $new_balance=null) {
        $this->group_id     = $group_id;
        $this->account_uuid = $account_uuid;
        $this->new_balance  = $new_balance;
    }

}
