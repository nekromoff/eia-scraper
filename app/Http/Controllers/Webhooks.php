<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\Routing\Exception\NoConfigurationException;

class Webhooks extends BaseController
{
    private function verifyWebhook($signature, $key)
    {
        $hash = \hash_hmac('sha256', $signature['timestamp'] . $signature['token'], $key);
        return $signature['signature'] === $hash;
    }

    /**
     * Handles Permanent Failure event from Mailgun.
     *
     * @param Request $request Webhook body
     * @return string
     */
    public function permanentFailure(Request $request)
    {
        $key = app('config')->get('services')['mailgun']['signingKey'];
        if (!$key) {
            throw new NoConfigurationException();
        }
        if (!$this->verifyWebhook($request->signature, $key)) {
            throw new NotAcceptableHttpException();
        }

        \App\Watcher::where('email', $request->{'event-data'}['recipient'])->delete();
        return 'ok';
    }
}
