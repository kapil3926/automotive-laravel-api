<?php

use App\Models\MailTemplate;
use Illuminate\Support\Facades\Mail;

if (!function_exists('array_unset_recursive')) {
    /**
     * Removes given key from an array
     *
     * @param $array
     * @param $remove
     * @return bool
     */
    function array_unset_recursive(&$array, $remove): bool
    {
        $remove = (array)$remove;
        foreach ($array as $key => &$value) {
            if (in_array($value, $remove)) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                array_unset_recursive($value, $remove);
            }
        }
        return true;
    }
}

if (!function_exists('send_mail')) {
    /**
     * Send Mail to Given Mail ID
     * Returns a boolean
     *
     * @param $name
     * @param $data
     * @param $to
     * @return bool
     */
    function send_mail($name, $data, $to): bool
    {
        $templateData = MailTemplate::where('name', $name)->first();
        if ($templateData) {
            $html = $templateData->html_code;
            $header = $templateData->header;
            foreach ($data as $key => $datum) {
                if ($key == 'name') {
                    $datum = ucwords($datum);
                }
                $html = str_ireplace("@@$key@@", $datum, $html);
                $header = str_ireplace("@@$key@@", $datum, $header);
            }
            Mail::send([], [], function ($message) use ($data, $header, $to, $html) {
                $message->to($to, $data['name'])->subject($header)->from(env('MAIL_FROM_ADDRESS'), "You've Got Fuel App")
                    ->setBody($html, 'text/html');
            });
            return true;
        } else {
            return false;
        }
    }
}


if (!function_exists('send_sms')) {
    /**
     * Returns curl response
     * @param $mobile
     * @param $text
     * @param $flowId
     * @return string a string
     */
    function send_sms($mobile, $text, $flowId): string
    {
//        dd($text);
        //Your authentication key
        $authKey = env('SMS_AUTH_KEY');
        //Sender ID,While using route4 sender id should be 6 characters long.
        $senderId = env('SMS_SENDER_ID');
        $postData = new StdClass();
        $postData->flow_id = $flowId;
        $postData->sender = $senderId;
        $postData->mobiles = '91' . $mobile;
        if ($text) {
            $postData->var = $text;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.msg91.com/api/v5/flow/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                "authkey: $authKey",
                "content-type: application/JSON"
            ),
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
//        $err = curl_error($curl);
        curl_close($curl);

//        Print error if any
//        if ($err) {
//            echo "cURL Error #:" . $err;
//        } else {
//            echo $response;
//        }
//
        return $response;
    }
}
