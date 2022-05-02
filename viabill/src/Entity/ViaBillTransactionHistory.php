<?php
/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
* @see       /LICENSE
*/

use ViaBill\Object\Api\Payment\PaymentRequest;
use ViaBill\Object\Api\Payment\PaymentResponse;
use ViaBill\Object\Api\ApiResponse;
use ViaBill\Object\Api\CallBack\CallBackResponse;

/**
 * Class ViaBillTransactionHistory
 */
class ViaBillTransactionHistory extends ObjectModel
{       
    const RECENT_TRANSACTIONS_DAYS = 15; // in days   
    
    const IRREGULAR_MAX_PENDING_INTERVAL = 3600; // in seconds

    /**
     * Transaction Id Variable Declaration.
     *
     * @var
     */
    public $transaction_id;

    /**
     * Order Id Variable Declaration.
     *
     * @var
     */
    public $order_id;    

    /**
     * Checkout request date (Shop -> Viabill)
     *
     * @var
     */
    public $checkout_out_date;

    /**
     * Checkout request params (Shop -> Viabill)
     *
     * @var
     */
    public $checkout_out_params;

    /**
     * Checkout request response (Shop -> Viabill)
     *
     * @var
     */
    public $checkout_out_response;    

    /**
     * Checkout request success (Shop -> Viabill)
     *
     * @var
     */
    public $checkout_out_success;
    
    /**
     * Callback call date (Viabill -> Shop)
     *
     * @var
     */
    public $callback_in_date;

    /**
     * Callback call params (Viabill -> Shop)
     *
     * @var
     */
    public $callback_in_params;

    /**
     * Callback call status (Viabill -> Shop)
     *
     * @var
     */
    public $callback_in_status;

    /**
     * Complete call date (Viabill -> Shop)
     *
     * @var
     */
    public $complete_in_date;

    /**
     * Complete call params (Viabill -> Shop)
     *
     * @var
     */
    public $complete_in_approved;

    /**
     * Cancel call date (Viabill -> Shop)
     *
     * @var
     */
    public $cancel_in_date;

    /**
     * Cancel call params (Viabill -> Shop)
     *
     * @var
     */
    public $cancel_in_params;

    /**
     * Notes about the transaction
     *
     * @var
     */
    public $notes;
   
    /**
     * Sets ViaBill Order Refund Entity Definitions.
     *
     * @var array
     */
    public static $definition = [
        'table' => 'viabill_transaction_history',
        'primary' => 'id_viabill_transaction_history',
        'fields' => [            
            'transaction_id' => ['type' => self::TYPE_STRING],
            'order_id' => ['type' => self::TYPE_INT],
            'checkout_out_date' => ['type' => self::TYPE_DATE],
            'checkout_out_params' => ['type' => self::TYPE_STRING],
            'checkout_out_response' => ['type' => self::TYPE_STRING],
            'checkout_out_success' => ['type' => self::TYPE_BOOL],
            'callback_in_date' => ['type' => self::TYPE_DATE, 'required' => false],
            'callback_in_params' => ['type' => self::TYPE_STRING, 'required' => false],
            'callback_in_status' => ['type' => self::TYPE_STRING, 'required' => false],
            'complete_in_date' => ['type' => self::TYPE_DATE, 'required' => false],
            'complete_in_approved' => ['type' => self::TYPE_STRING, 'required' => false],
            'cancel_in_date' => ['type' => self::TYPE_DATE, 'required' => false],
            'cancel_in_params' => ['type' => self::TYPE_STRING, 'required' => false],
            'notes' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false],
        ],
    ];    
 
    /**
     * Gets id_viabill_transaction_history From viabill_transaction_history Table By Order ID.
     *
     * @param int $idOrder
     *
     * @return int
     */
    public static function getPrimaryKeyByTransaction($idTransaction)
    {
        $query = new DbQuery();
        $query->select(self::$definition['primary']);
        $query->from(pSQL(self::$definition['table']));
        $query->where('`transaction_id`="' . pSQL($idTransaction).'"');
        
        $primary_key = (int) Db::getInstance()->getValue($query);

        /*
        if (empty($primary_key)) {
            $query_str = 'SELECT '.self::$definition['primary'].' FROM '._DB_PREFIX_.self::$definition['table'].
            ' WHERE `transaction_id` = "' .  pSQL($idTransaction).'"'; 
            $primary_key = (int) Db::getInstance()->getValue($query_str);                
        }
        */
        
        return $primary_key;
    }
    
    /**
     * Gets id_viabill_transaction_history From viabill_transaction_history Table By Order ID.
     *
     * @param int $idOrder
     *
     * @return int
     */
    public static function getPrimaryKeyByOrder($idOrder)
    {
        $query = new DbQuery();
        $query->select(pSQL(self::$definition['primary']));
        $query->from(pSQL(self::$definition['table']));
        $query->where('`order_id`=' . (int) $idOrder);

        $primary_key = (int) Db::getInstance()->getValue($query);

        return $primary_key;
    }      
    
    /**
     * Create new entry
     */
    public function createNew(PaymentRequest $paymentRequest, ApiResponse $paymentResponse)
    {
        $request_params = $paymentRequest->getSerializedData();

        $response_params = array(
            'statusCode' => $paymentResponse->getStatusCode(),
            'body' => $paymentResponse->getBody(),
            'errors' => $paymentResponse->getErrors(),
            'effectiveUrl' => $paymentResponse->getEffectiveUrl()
        );

        $success_val = 1;
        if (!in_array($response_params['statusCode'], [200, 204])) { 
            $success_val = 0;
        }        
        
        $this->transaction_id = $request_params['transaction'];
        $this->order_id = $request_params['order_number'];
        $this->checkout_out_date = date('Y-m-d H:i:s');
        $this->checkout_out_params = json_encode($request_params);
        $this->checkout_out_response = json_encode($response_params);
        $this->checkout_out_success = $success_val;
        $this->save();        
    }

    /**
     * Update transaction entry with callback info
     *     
     */
    public function updateAfterCallback(CallBackResponse $callbackResponse) {        
        $status = $callbackResponse->getStatus();        
        $callback_params = array(
            'order_id' => $callbackResponse->getOrderNumber(),
            'amount' => $callbackResponse->getAmount(),
            'currency' => $callbackResponse->getCurrency(),
            'status' => $status,
            'signature' => $callbackResponse->getSignature()
        );

        $current_date = date('Y-m-d H:i:s');

        // sanity check        
        if (!empty($this->callback_in_status)) {
            if ($this->callback_in_status != $status) {
                $notes = $this->notes;
                $irregular_note = 'Another callback call was made on '.$current_date.' with new status: '. $status. '(old status: '.$this->callback_in_status.' / old date: '.$this->callback_in_date.')';
                $this->notes .= ' IRREGULAR:'.$irregular_note.'IRREGULARE ';
            }
        }

        $this->callback_in_date = $current_date;
        $this->callback_in_params = json_encode($callback_params);        
        $this->callback_in_status = $status;        
        
        // save changes into the database table
        $this->save();        

        // check if you need to remove old or excessive db table entries
        $this->truncateTableRows();
    }

    /**
     * Update the transaction entry after a cancel call
     *     
     */    
    public function updateAfterCancel($cancelResponse) {
        $this->cancel_in_date = date('y-m-d H:i:s');
        $this->cancel_in_params = json_encode($cancelResponse);        

        $this->save();
    }

    /**
     * Update the transaction entry after a complete call
     */
    public function updateAfterComplete($isOrderApproved) {
        $this->complete_in_date = date('y-m-d H:i:s');
        $this->complete_in_approved = ($isOrderApproved)?1:0;

        $this->save();
    }    

    /**
     * Get the time window to search for irregular transactions (in days)
     * 
     * @return array
     */
    public static function getRecentTransactionsDays()
    {
        $days = self::RECENT_TRANSACTIONS_DAYS;
        return $days;
    }

    /**
     * Get all the recent transactions
     * 
     * @return array
     */
    public static function getRecentTransactions()
    {
        $days = self::getRecentTransactionsDays();
        $date_range = '-'.$days.' days';
        $date_from = date('Y-m-d', strtotime($date_range));
        
        $query = new DbQuery();
        $query->select('*');
        $query->from(pSQL(self::$definition['table']));
        $query->where('checkout_out_date > "'.$date_from.'"');
        $query->orderBy('id_viabill_transaction_history DESC');        

        $results = Db::getInstance()->executeS($query);

        $recent_transactions = array();
        if (!empty($results)) {
            foreach ($results as $result) {                
                $checkout_date = self::getTransactionDate($result['checkout_out_date']);
                $callback_date = self::getTransactionDate($result['callback_in_date']);
                $complete_date = self::getTransactionDate($result['complete_in_date']);
                $cancel_date = self::getTransactionDate($result['cancel_in_date']);

                $status = strtoupper($result['callback_in_status']);

                if (empty($status)) {
                    $status = 'PENDING';
                    $status_class = 'pending';
                } else {
                    $status = trim(strtoupper($status));
                    switch ($status) {
                        case 'APPROVED': 
                            $status_class = 'approved';
                            break;
                        case 'CANCELLED': 
                            $status_class = 'cancelled';
                            break;    
                        case 'REJECTED':
                            $status_class = 'rejected';
                            break;
                        default:
                            $status_class = 'unknown';
                            break;        
                    }
                }

                // is it irregular?
                $irregular = false;
                $irregular_notes = array();
                $replacements = array();
                
                if (!empty($notes)) {
                    if (preg_match_all('/IRREGULAR:(.+?)IRREGULARE/', $notes, $matches)) {
                        $irregular = true;
                        foreach ($matches[0] as $match) {
                            $replacements[] = $match;
                        }
                        foreach ($matches[1] as $match) {
                            list($key, $value) = explode(' ',$match,2);
                            $irregular_notes[trim($key)] = trim($value);
                        }	
                    }	
                }

                if (!empty($replacements)) {
                    foreach ($replacements as $replacement) {
                        $result['notes'] = str_replace($replacement, '', $result['notes']);
                    }
                    $result['notes'] = trim($result['notes']);
                }                

                // generate an automated notes messages
                $notes = '';                
                switch ($status) {
                    case 'APPROVED':
                        $notes .= 'The transaction has been approved by ViaBill. ';
                        break;
                    case 'CANCELLED':
                        $notes .= 'The transaction has been cancelled by the buyer. ';
                        break;    
                    case 'REJECTED':
                        $notes .= 'The transaction has been rejected by ViaBill. ';
                        break;      
                    case 'PENDING':                        
                        $current_time = strtotime(date('Y-m-d H:i:s'));
                        $transaction_time = strtotime($checkout_date);
                        $differenceInSeconds = $current_time - $transaction_time;
                        $diffenceInHuman = self::secondsToHuman($differenceInSeconds);                        

                        $notes .= 'The transaction remains in pending status as no callback has been received yet (waiting time: '.$diffenceInHuman.'). ';                        
                        // check for irregular behavior
                        if ($differenceInSeconds > self::IRREGULAR_MAX_PENDING_INTERVAL) {
                            $notes .= 'The wait time for the callback is longer than expected, which marks this transaction as irregular. ';
                            $irregular_notes['PENDING_WAIT_TIME'] = "The wait time ({$diffenceInHuman}) for the callback is longer than expected.";
                            $irregular = true;
                        }
                        if (!empty($complete_date)) {
                            $notes .= 'The buyer appears to have completed the payment. ';    
                        }
                        if (!empty($cancel_date)) {
                            $notes .= 'The buyer appears to have cancelled the payment. ';
                        }                         
                        break;          
                    case 'UNKNOWN':
                    default:    
                        $notes .= 'The callback status is unknown. ';
                        $irregular_notes['UNKNOWN_STATUS'] = "The callback status is unknown ({$status}).";
                        $irregular = true;
                        break;              
                }

                // check for other types of irregularity
                if (empty($complete_date) && empty($cancel_date)) {                    
                    $notes .= 'The buyer appears not to have completed or cancelled the payment. ';
                    $irregular_notes['MISSING_ACTION'] = 'The buyer appears not to have completed or cancelled the payment. ';
                    $irregular = true;
                }

                if (empty($complete_date) && ($status == 'APPROVED')) {
                    $notes .= 'The buyer appears not to have completed the payment, even though the transaction is Approved. ';
                    $irregular_notes['INVALID_COMBINATION'] = 'The buyer appears not to have completed the payment, even though the transaction is Approved. ';
                    $irregular = true;
                }

                if (empty($cancel_date) && ($status == 'CANCELLED')) {
                    $notes .= 'The buyer appears not to have cancelled the payment, even though the transaction is Cancelled. ';
                    $irregular_notes['INVALID_COMBINATION'] = 'The buyer appears not to have cancelled the payment, even though the transaction is Cancelled. ';
                    $irregular = true;
                }
                                          
                if (!empty($result['notes'])) {
                    $notes .= '<br/>Additional notes:<br/>'.$result['notes'];
                }               

                $recent_transaction = array(
                    'transaction_id' => $result['transaction_id'],
                    'order_id' => $result['order_id'],
                    'checkout_date' => $checkout_date,
                    'checkout_params' => self::getTransactionParams($result['checkout_out_params']),
                    'checkout_response' => self::getTransactionParams($result['checkout_out_response']),
                    'checkout_success' => self::getTransactionBool($result['checkout_out_success']),
                    'callback_date' => $callback_date,
                    'callback_params' => self::getTransactionParams($result['callback_in_params']),
                    'callback_status' => $result['callback_in_status'],
                    'complete_date' => $complete_date,
                    'complete_approved' => $result['complete_in_approved'],
                    'cancel_date' => $cancel_date,
                    'cancel_params' => self::getTransactionParams($result['cancel_in_params']),                    
                    'status' => $status,
                    'status_class' => $status_class,
                    'irregular' => ($irregular)?'Yes':'No',
                    'irregular_notes' => ($irregular)?'<ul><li>'.implode('</li><li>', $irregular_notes).'</li></ul>':'',
                    'notes' => $notes,
                );

                $recent_transactions[] = $recent_transaction;
            }
        }

        return $recent_transactions;  
    }

    /**
     * Truncate excessive table rows, based on the RECENT_TRANSACTIONS_DAYS days limit
     */
    public static function truncateTableRows() {
        $days = self::getRecentTransactionsDays();
        $date_range = '-'.$days.' days';
        $date_from = date('Y-m-d', strtotime($date_range));

        $delete_query = 'DELETE FROM `' . _DB_PREFIX_ . self::$definition['table'].'` WHERE checkout_out_date < "'.$date_from.'"';
        Db::getInstance()->execute($delete_query);
    }

    /**
     * Util functions
     */
    public static function getTransactionDate($date_str) {
        if (empty($date_str)) return '';
        if (strpos($date_str, '0000-00-00')!==false) return '';
        return $date_str;
    }

    public static function getTransactionParams($param_str) {
        if (empty($param_str)) return '';
        return json_decode($param_str, true);
    }

    public static function getTransactionBool($bool) {
        if (($bool == '1') || ($bool == 1) || ($bool == 'yes') || ($bool == 'true')) return true;
        return false;
    }

    public static function secondsToHuman($num_of_seconds) {        
        $sec = $num_of_seconds%60;
        $min = floor(($num_of_seconds%3600)/60);
        $hour = floor(($num_of_seconds%86400)/3600);
        $day = floor(($num_of_seconds%2592000)/86400);
        $Month = floor($num_of_seconds/2592000);
    
        $human_readable = '';
        if (!empty($Month)) $human_readable .= "$Month months, ";
        if (!empty($day)) $human_readable .= "$day days, ";
        if (!empty($hour)) $human_readable .= "$hour hours, ";
        if (!empty($min)) $human_readable .= "$min minutes, ";
        if (!empty($sec)) $human_readable .= "$sec seconds ";
    
        return $human_readable;
    }


}