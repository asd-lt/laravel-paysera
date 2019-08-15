<?php

namespace Asd\Paysera;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Asd\Paysera\Events\PayseraPaymentCallback;
use WebToPay_Exception_Callback;

class PayseraWrapper
{
    /**
     * Paysera callback process
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     * @throws \WebToPayException
     */
    public function callback(Request $request)
    {
        $parsedCallback = $this->parseCallback($request->all());

        if (!$parsedCallback) {
            abort(406, 'Your data is bad. Go away!');
        }

        event(new PayseraPaymentCallback($parsedCallback));
        return 'OK';
    }

    /**
     * @param $amount
     * @param $orderId
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \WebToPayException
     */
    public function redirectToPayment($amount, $orderId)
    {
        return redirect($this->requestPaymentUrl($amount, $orderId));
    }

    /**
     * @param $amount
     * @param $orderId
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \WebToPayException
     */
    public function requestPaymentUrl($amount, $orderId)
    {
        $amount = $this->prepareAmount($amount);

        $requestData = $this->buildPaymentRequest($amount, $orderId);

        return $this->preparePaymentUrl($requestData);
    }

    /**
     * Builds paysera payment request data
     *
     * @param $amount
     * @param $orderId
     *
     * @return array
     * @throws \WebToPayException
     */
    public function buildPaymentRequest($amount, $orderId)
    {
        $data = [
            'orderid' => $orderId,
            'amount' => $amount,
        ];

        return \WebToPay::buildRequest($this->requestData($data));
    }

    /**
     * Generates paysera payment request url with given request data
     *
     * @param $requestData
     *
     * @return string
     */
    protected function preparePaymentUrl($requestData)
    {
        return \WebToPay::PAY_URL . '?' . http_build_query($requestData);
    }

    /**
     * Converts amount to cents
     *
     * @param $amount
     *
     * @return int
     */
    protected function prepareAmount($amount)
    {
        return (int)round($amount * 100);
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected function requestData($data = [])
    {
        $request = [
            'projectid' => config('asd.project_id'),
            'sign_password' => config('asd.secret'),
            'currency' => config('asd.currency'),
            'country' => config('asd.country'),
            'accepturl' => url(config('asd.accept_url')),
            'cancelurl' => url(config('asd.cancel_url')),
            'callbackurl' => url('paysera/callback'),
            'test' => $this->isTest(),
        ];

        return array_merge($request, Arr::except($data, array_keys($request)));
    }


    /**
     * Parse paysera callback
     *
     * @param $callbackData
     *
     * @return array|bool
     * @throws \WebToPayException
     */
    protected function parseCallback($callbackData)
    {
        try {
            $response = \WebToPay::checkResponse($callbackData, [
                'projectid' => config('asd.project_id'),
                'sign_password' => config('asd.secret'),
            ]);

            return $response;
        } catch (WebToPay_Exception_Callback $e) {
            Log::error('Paysera callback: ', [get_class($e) . ': ' . $e->getMessage()]);
            return false;
        }
    }

    /**
     * @return \Illuminate\Config\Repository|int|mixed
     */
    protected function isTest()
    {
        if (config('asd.test') === null) {
            return 1;
        } else {
            return config('asd.test');
        }
    }
}