<?php

namespace Flare\Service\OneWaySMS;

/**
 * 
 * @author anthony
 * 
 */
class Result
{
    /**
     * 
     * @var string
     */
    const SEND_SMS = 'send_sms';

    /**
     * 
     * @var string
     */
    const TRANSACTION_STATUS = 'transaction_status';

    /**
     * 
     * @var string
     */
    const CREDIT_BALANCE = 'credit_balance';

    /**
     * 
     * @var array
     */
    public static $errors = array(
        self::SEND_SMS => array(
            '-100' => 'apipassname or apipassword is invalid',
            '-200' => 'senderid parameter is invalid',
            '-300' => 'mobileno parameter is invalid',
            '-400' => 'languagetype is invalid',
            '-500' => 'Invalid characters in message',
            '-600' => 'Insufficient credit balance'
        ),
        self::TRANSACTION_STATUS => array(
            //0 Success receive on mobile handset
            '100' => 'Message delivered to Telco',
            '-100' => 'mtid invalid / not found',
            '-200' => 'Message sending fail'
        ),
        self::CREDIT_BALANCE => array(
            '-100' => 'apipassname or apipassword is invalid'
        )
    );

    /**
     * 
     * @var string
     */
    private $result;

    /**
     * 
     * @var string
     */
    private $resultType;

    /**
     * 
     * @var string
     */
    private $errorCode;

    /**
     * 
     * @var string
     */
    private $errorMessage;

    /**
     * 
     * @param string $result
     * @param string $resultType
     * @param string $errorCode
     * @param string $errorMessage
     */
    public function __construct($result, $resultType, $errorCode = null, $errorMessage = null)
    {
        $this->result = $result;
        $this->resultType = $resultType;
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
    }

    /**
     * 
     * @return int
     */
    public function getValue()
    {
        return (int) $this->result;
    }

    /**
     * 
     * @return boolean
     */
    public function success()
    {
        $return = $this->hasError();
        if (!$return) {
            if ($this->resultType === self::TRANSACTION_STATUS) {
                $return = $this->result === true;
            } elseif ($this->resultType === self::SEND_SMS) {
                $return = $this->result ? true : false;
            }
        }
        return $return;
    }

    /**
     * 
     * @return boolean
     */
    public function hasError()
    {
        return $this->errorCode || $this->errorMessage;
    }

    /**
     * 
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * 
     * @return string
     */
    public function getError()
    {
        return $this->errorMessage;
    }

    /**
     * 
     * @return string
     */
    public function getResultType()
    {
        return $this->resultType;
    }
}