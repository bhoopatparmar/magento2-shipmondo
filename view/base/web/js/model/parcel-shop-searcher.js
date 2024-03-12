define([
    'ko',
    'mage/storage',
    'jquery'
], function(ko, storage, $) {

    var currentRequest = null;

    return function(carrier_code, wexoShippingData, shippingCountryId) {
        if (currentRequest && currentRequest.abort) {
            currentRequest.abort();
        }

        $('body').trigger('processStart');
        return storage.get('/rest/V1/wexo-shipmondo/get-parcel-shops?' + $.param({
            carrier_code: carrier_code,
            country: shippingCountryId,
            postcode: wexoShippingData.postcode,
            cache: true
        })).always(function() {
            currentRequest = null;
            $('body').trigger('processStop');
        });
    };
});
