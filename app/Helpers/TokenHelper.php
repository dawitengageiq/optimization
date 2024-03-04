<?php

namespace App\Helpers;

use App\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Log;
use Mockery\CountValidator\Exception;
use PHPEncryptData\Simple;

class TokenHelper
{
    protected $authorized = false;

    protected $validToken = false;

    public function __construct($token)
    {
        /*
        try
        {
            $decryptedData = Crypt::decrypt($token);

            //the token pattern is email space id so split the decrypted data by space
            $data = explode(' ', $decryptedData);

            $param = [
                'email' => $data[0],
                'id' => $data[1]
            ];

            $user = User::userByEmailAndID($param)->first();

            if($user==null)
            {
                $this->authorized = false;
            }
            else
            {
                $this->authorized = true;
            }

            $this->validToken = true;

        }
        catch (DecryptException $e)
        {
            //invalid token
            Log::info($e->getMessage());
            $this->validToken = false;
        }
        */

        try {
            /*
            $decryptedData = Crypt::decrypt($token);

            //the token pattern is email space id so split the decrypted data by space
            $data = explode(' ', $decryptedData);

            $param = [
                'email' => $data[0],
                'id' => $data[1]
            ];

            $user = User::userByEmailAndID($param)->first();

            if($user==null)
            {
                $this->authorized = false;
            }
            else
            {
                $this->authorized = true;
            }
            */

            //decrypt the token
            $encryptionKey = env('ENCRYPTION_KEY');
            $macKey = env('MAC_KEY');

            $encryptor = new Simple($encryptionKey, $macKey);
            $decryptedToken = $encryptor->decrypt($token);

            //the token pattern is email space id so split the decrypted data by space
            $data = explode(' ', $decryptedToken);

            $this->validToken = true;

            $param = [
                'email' => $data[0],
                'id' => $data[1],
            ];

            $user = User::userByEmailAndID($param)->first();

            if ($user == null) {
                $this->authorized = false;
            } else {
                $this->authorized = true;
            }

        } catch (Exception $e) {
            //invalid token
            Log::info($e->getCode());
            Log::info($e->getMessage());
            $this->validToken = false;
        } catch (\RuntimeException $e) {
            Log::info($e->getCode());
            Log::info($e->getMessage());
            $this->validToken = false;
        }

    }

    public function isAuthorized(): bool
    {
        return $this->authorized;
    }

    public function isValidToken(): bool
    {
        return $this->validToken;
    }
}
