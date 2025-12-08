<?php
/**
 * SHINEMONITOR API PHP CLASS
 */

class shinemonitorApiPhp{
    private string $usrname;    
    private string $password;
    private string $companyKey;

    private string $source="1";
    private string $salt;
    private string $_app_id_ = "com.eybond.smartclient.ess";
    private string $_app_version_ = "3.26.1.2";
    private string $_app_client_ = "android";

    private string $pn;
    private string $sn;

    private string $token;
    private string $secret;


    private string $sha1Pwd;
    public function __construct($username, $password, $companyKey, $pn, $sn){
        $this->usrname = $username;
        $this->password = $password;
        $this->companyKey = $companyKey;
        $this->pn = $pn;
        $this->sn = $sn;
        $this->sha1Pwd = sha1($password);
    }

    public function shineAuthenticate(){
        $salt = strval(microtime(true) * 1000);
        $action = "&action=auth&usr=" . urlencode($this->usrname) . "&company-key=" . $this->companyKey . "&source=" . $this->source .
                "&_app_id_=" . $this->_app_id_ . "&_app_version_=" . $this->_app_version_ . "&_app_client_=" . $this->_app_client_;

        $sign = sha1($salt . $this->sha1Pwd . $action); 
        $url = "https://api.shinemonitor.com/public/?sign=$sign&salt=$salt$action";

        try {
            // ---- C U R L ----
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => true
            ]);

            $response  = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                throw new Exception("Errore cURL: $curlError");
            }

            if ($httpCode !== 200) {
                throw new Exception("HTTP code: $httpCode");
            }

            $data = json_decode($response);
            if (!$data) {
                throw new Exception("JSON decode error: " . json_last_error_msg());
            }
            $this->secret = $data->dat->secret;
            $this->token = $data->dat->token;
            if($this->secret == null || $this->token == null){
                throw new Exception("Secret o token = null: " .$data);
            }
            return $data->dat ?? null;

        } catch (Exception $e) {
            return false; // gestione interna errori
        }

    }

    public function shineRequest($actionString){
        $salt = strval(microtime(true) * 1000);
        $action = "&action=$actionString&devcode=2449&devaddr=1&sn=" . $this->sn . "&pn=" . $this->pn . "&source=" . $this->source .
                "&_app_id_=" . $this->_app_id_ . "&_app_version_=" . $this->_app_version_ . "&_app_client_=" . $this->_app_client_;
        $sign = sha1($salt . $this->secret . $this->token . $action);

        $url = "https://api.shinemonitor.com/public/?sign=$sign&salt=$salt&token=".$this->token."$action";

        echo $url;
        echo"<hr>";

        try {
            // ---- C U R L ----
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => true
            ]);

            $response  = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                throw new Exception("Errore cURL: $curlError");
            }

            if ($httpCode !== 200) {
                throw new Exception("HTTP code: $httpCode");
            }

            $data = json_decode($response);
            if (!$data) {
                throw new Exception("JSON decode error: " . json_last_error_msg());
            }

            // ✔ QUI È IL CONTROLLO GIUSTO
            if (isset($data->errorMessage) && $data->errorMessage !== "ERR_NONE") {
                throw new Exception("API error: " . $data->errorMessage);
            }

            return $data->dat ?? null;

        } catch (Exception $e) {
            echo "<b>⚠ ERRORE:</b> " . $e->getMessage();
            return false; // gestione interna errori
        }


    }
    
}




?>
