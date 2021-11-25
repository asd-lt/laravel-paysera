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
     * @var null|string
     */
    private $acceptUrl = null;

    /**
     * @var null|string
     */
    private $cancelUrl = null;

    /**
     * Generate`s accept page
     *
     * @return string
     */
    public function pageAccept()
    {
        if (view()->exists(config('asd.accept_view', 'paysera.accept'))) {
            return view(config('asd.accept_view', 'paysera.accept'));
        } else {
            return 'Accept';
        }
    }

    /**
     * Generate`s cancel page
     *
     * @return string
     */
    public function pageCancel()
    {
        if (view()->exists(config('asd.cancel_view', 'paysera.cancel'))) {
            return view(config('asd.cancel_view', 'paysera.cancel'));
        } else {
            return 'Cancel';
        }
    }

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
     * @param array $config
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \WebToPayException
     */
    public function requestPaymentUrl($amount, $orderId, $config = [])
    {
        $this->updateConfig($config);

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
            'accepturl' => $this->getAcceptUrl(),
            'cancelurl' => $this->getCancelUrl(),
            'callbackurl' => url('paysera/callback'),
            'test' => $this->isTest(),
        ];

        return array_merge($request, Arr::except($data, array_keys($request)));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\UrlGenerator|string
     */
    private function getAcceptUrl(): string
    {
        return $this->acceptUrl ?: url(config('asd.accept_url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\UrlGenerator|string
     */
    private function getCancelUrl(): string
    {
        return $this->cancelUrl ?: url(config('asd.cancel_url'));
    }

    /**
     * @param $config
     */
    private function updateConfig($config): void
    {
        if (!empty($config['accept_url'])) {
            $this->acceptUrl = $config['accept_url'];
        }

        if (!empty($config['cancel_url'])) {
            $this->cancelUrl = $config['cancel_url'];
        }
    }

    /**
     * Parse paysera callback
     *
     * @param $callbackData
     *
     * @return array|bool
     * @throws \WebToPayException
     */
    private function parseCallback($callbackData)
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