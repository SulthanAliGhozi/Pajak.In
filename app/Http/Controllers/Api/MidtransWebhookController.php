<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\PajakException;
use App\Exceptions\PaymentGatewayException;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        try {
            $result = $this->paymentService->handleMidtransWebhook($request->all());

            return response()->json([
                'status' => $result['status'],
                'message' => $result['message'],
            ]);
        } catch (PaymentGatewayException $e) {
            $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 403;

            return response()->json(['error' => $e->getMessage()], $code);
        } catch (PajakException $e) {
            $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 404;

            return response()->json(['error' => $e->getMessage()], $code);
        }
    }
}
