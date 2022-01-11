<?php
/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
* @see       /LICENSE
*/

namespace ViaBill\Service\Api;

use ViaBill\Object\Api\ApiResponse;
use ViaBill\Object\Api\ApiResponseError;

/**
 * Class OrderStatusApiService
 */
abstract class OrderStatusApiService
{
    /**
     * Filename Constant.
     */
    const FILENAME = 'OrderStatusApiService';

    /**
     * Gets Order Status API Response With Formatted Errors.
     *
     * @param \ViaBill $module
     * @param ApiResponse $apiResponse
     * @param int $transactionId
     *
     * @return ApiResponse
     */
    public function getWithFormattedError(\ViaBill $module, ApiResponse $apiResponse, $transactionId)
    {
        $errors = $apiResponse->getErrors();
        $errorBody = $apiResponse->getBody();

        if (!$errorBody) {
            $errorBody =
                sprintf(
                    $module->l('An unexpected error occurred for order %s. Status code %s'),
                    $transactionId,
                    $apiResponse->getStatusCode()
                );
        }

        $apiError = new ApiResponseError('', $errorBody);
        $allErrors = array_merge(
            [$apiError],
            $errors
        );

        return new ApiResponse($apiResponse->getStatusCode(), $apiResponse->getBody(), $allErrors);
    }
}
