<?php
/**
 * AakashSMS API Client for WHMCS
 * Based on AakashSMS API v4 documentation
 */
namespace SMSAlert\Lib\AakashSms;

use SMSAlert\Lib\Curl\Client;

class AakashSms {
    /**
     * API Token
     */
    private $token;
    
    /**
     * API URL
     */
    private $url = 'https://www.aakashsms.com/admin/public/sms/v4/send';
    
    /**
     * Sender ID
     */
    private $sender;
    
    /**
     * Errors array
     */
    private $errors = array();
    
    /**
     * Additional options
     */
    private $options = array();

    /**
     * Set authentication token
     * 
     * @param string $token API token
     * @return object
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * Set sender ID
     * 
     * @param string $sender Sender ID
     * @return object
     */
    public function setSender($sender) {
        $this->sender = $sender;
        return $this;
    }

    /**
     * Set additional options
     * 
     * @param array $options Additional options
     * @return object
     */
    public function setOptions($options = array()) {
        if (!is_array($options)) {
            $this->options = array();
        } else {
            $this->options = $options;
        }
        return $this;
    }

    /**
     * Get options with defaults
     * 
     * @return array
     */
    public function getOptions() {
        $default_options = array(
            "plugin" => 'whmcs-aakashsms',
            "website" => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''
        );
        $this->options = array_merge($default_options, $this->options);
        return $this->options;
    }

    /**
     * Format phone number for Nepal
     * 
     * @param string $number Phone number
     * @return string
     */
    private function formatNumber($number) {
        // Remove any non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // Handle Nepal numbers
        if (strlen($number) == 10 && substr($number, 0, 2) == '98') {
            // Already in correct format (98XXXXXXXX)
            return $number;
        } elseif (strlen($number) == 13 && substr($number, 0, 5) == '97798') {
            // Remove country code 977 (97798XXXXXXXX -> 98XXXXXXXX)
            return substr($number, 3);
        } elseif (strlen($number) == 12 && substr($number, 0, 4) == '9779') {
            // Handle 9779XXXXXXXX format
            return '9' . substr($number, 4);
        }
        
        return $number;
    }

    /**
     * Get authentication parameters
     * 
     * @return array|false
     */
    private function getAuthParams() {
        if (empty($this->token)) {
            $this->errors[] = "Missing API token";
            return false;
        }
        return array('token' => $this->token);
    }

    /**
     * Get error messages
     * 
     * @return array|false
     */
    private function getErrors() {
        if (!empty($this->errors)) {
            return array('status' => 'error', 'description' => $this->errors);
        }
        return false;
    }

    /**
     * Send SMS
     * 
     * @param string $to Phone number
     * @param string $text Message text
     * @param string $schedule Schedule time (optional)
     * @return array
     */
    public function send($to, $text, $schedule = null) {
        if (empty($to)) {
            $this->errors[] = 'Phone number is missing';
        }
        
        if (empty($text)) {
            $this->errors[] = 'SMS text is missing';
        }

        if (empty($this->sender)) {
            $this->errors[] = 'Sender ID is missing';
        }

        $auth = $this->getAuthParams();
        if ($this->getErrors()) {
            return $this->getErrors();
        }

        $params = array(
            'auth_token' => $this->token,
            'to' => $this->formatNumber($to),
            'text' => $text,
            'from' => $this->sender
        );

        $client = new Client();
        $response = $client->request('POST', $this->url, ['json' => $params, 'http_errors' => false]);
        $body = json_decode($response->getBody(), true);
        
        // Handle AakashSMS response format
        if (isset($body['response_code']) && $body['response_code'] == 200) {
            return array(
                'status' => 'success',
                'description' => array(
                    'batchid' => isset($body['batch_id']) ? $body['batch_id'] : uniqid(),
                    'message' => 'SMS sent successfully'
                )
            );
        } else {
            $error_msg = isset($body['message']) ? $body['message'] : 'Unknown error occurred';
            return array(
                'status' => 'error',
                'description' => $error_msg
            );
        }
    }

    /**
     * Check balance
     * 
     * @return array
     */
    public function balanceCheck() {
        $auth = $this->getAuthParams();
        if ($this->getErrors()) {
            return $this->getErrors();
        }

        $url = 'https://www.aakashsms.com/admin/public/sms/v4/balance';
        $params = array('auth_token' => $this->token);

        $client = new Client();
        $response = $client->request('POST', $url, ['json' => $params, 'http_errors' => false]);
        $body = json_decode($response->getBody(), true);

        if (isset($body['response_code']) && $body['response_code'] == 200) {
            return array(
                'status' => 'success',
                'description' => array(
                    'balance' => isset($body['balance']) ? $body['balance'] : 0
                )
            );
        } else {
            return array(
                'status' => 'error',
                'description' => isset($body['message']) ? $body['message'] : 'Could not fetch balance'
            );
        }
    }

    /**
     * Generate OTP (using regular SMS since AakashSMS doesn't have dedicated OTP endpoint)
     * 
     * @param string $to Phone number
     * @param string $template Message template with [otp] placeholder
     * @return array
     */
    public function generateOtp($to, $template) {
        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // Replace [otp] placeholder with actual OTP
        $message = str_replace('[otp]', $otp, $template);
        
        $result = $this->send($to, $message);
        
        if ($result['status'] == 'success') {
            // Store OTP in session or database for verification
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['aakash_otp_' . $to] = $otp;
            $_SESSION['aakash_otp_time_' . $to] = time();
        }
        
        return $result;
    }

    /**
     * Validate OTP
     * 
     * @param string $to Phone number
     * @param string $otp OTP to validate
     * @return array
     */
    public function validateOtp($to, $otp) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $stored_otp = isset($_SESSION['aakash_otp_' . $to]) ? $_SESSION['aakash_otp_' . $to] : null;
        $otp_time = isset($_SESSION['aakash_otp_time_' . $to]) ? $_SESSION['aakash_otp_time_' . $to] : null;
        
        // Check if OTP exists and is not expired (5 minutes)
        if ($stored_otp && $otp_time && (time() - $otp_time) <= 300) {
            if ($stored_otp == $otp) {
                // Clear OTP from session after successful validation
                unset($_SESSION['aakash_otp_' . $to]);
                unset($_SESSION['aakash_otp_time_' . $to]);
                
                return array(
                    'status' => 'success',
                    'description' => array(
                        'desc' => 'Code Matched successfully.'
                    )
                );
            } else {
                return array(
                    'status' => 'error',
                    'description' => 'Invalid OTP'
                );
            }
        } else {
            return array(
                'status' => 'error',
                'description' => 'OTP expired or not found'
            );
        }
    }

    /**
     * Get delivery report
     * 
     * @param string $batchId Batch ID
     * @return array
     */
    public function pullReport($batchId) {
        $auth = $this->getAuthParams();
        if ($this->getErrors()) {
            return $this->getErrors();
        }

        $url = 'https://www.aakashsms.com/admin/public/sms/v4/status';
        $params = array(
            'auth_token' => $this->token,
            'batch_id' => $batchId
        );

        $client = new Client();
        $response = $client->request('POST', $url, ['json' => $params, 'http_errors' => false]);
        $body = json_decode($response->getBody(), true);

        if (isset($body['response_code']) && $body['response_code'] == 200) {
            return array(
                'status' => 'success',
                'description' => array(
                    'report' => array(
                        array('status' => isset($body['status']) ? $body['status'] : 'delivered')
                    )
                )
            );
        } else {
            return array(
                'status' => 'error',
                'description' => isset($body['message']) ? $body['message'] : 'Could not fetch report'
            );
        }
    }
}