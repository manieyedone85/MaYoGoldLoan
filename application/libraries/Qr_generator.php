<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH.'third_party/endroid_qrcode/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;

class Qr_generator {

    public function create($data, $output = 'browser')
    {
        $qrCode = new QrCode($data);
        $qrCode->setSize(600);
        $qrCode->setWriterByName('png');
        $qrCode->setMargin(20);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);
        $qrCode->setForegroundColor(['r'=>0,'g'=>0,'b'=>0,'a'=>0]);
        $qrCode->setBackgroundColor(['r'=>255,'g'=>255,'b'=>255,'a'=>0]);
        $qrCode->setValidateResult(false);

        if (!empty(settingAll('logo'))) {
            $qrCode->setLogoPath(settingAll('logo'));
            $qrCode->setLogoWidth(150);
        }

        if ($output === 'browser') {
            header('Content-Type: '.$qrCode->getContentType());
            echo $qrCode->writeString();
            exit;
        }

        // Save file
        $filename = 'qr_' . time() . '.png';
        $path = FCPATH.'uploads/qr/'.$filename;
        $qrCode->writeFile($path);

        return $filename;
    }

    public function generate_expiring_qr($userId, $deviceId, $deviceUUID)
    {
        $expiresAt = time() + (5 * 60); // 5 minutes

        $payload = [
            'uid'    => $userId,
            'device' => $deviceId,
            'uuid'   => $deviceUUID,
            "host"   => $_SERVER['HTTP_HOST'],
            "aud"    => 'MAYO_INVOICE_APP',
            "typ"    => 'QR_LOGIN',
            "iat"    => time(),
            "alg"    => HASH,
            'exp'    => $expiresAt
        ];

        // SIGNATURE
        $payload['sig'] = hash_hmac(
            HASH,
            json_encode($payload),
            SECRET
        );

        $encodedData = base64_encode(json_encode($payload));

        // Save log
        $CI =& get_instance();
        $CI->db->insert('device_qr_log', [
            'user_id'      => $userId,
            'device_id'    => $deviceId,
            'generated_at' => date('Y-m-d H:i:s'),
            'expires_at'   => date('Y-m-d H:i:s', $expiresAt),
            'qr_data'      => $encodedData
        ]);

        // QR Generation
        $qrCode = new QrCode($encodedData);
        $qrCode->setSize(500);
        $qrCode->setWriterByName('png');
        $qrCode->setMargin(20);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);

        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
        exit;
    }
}
