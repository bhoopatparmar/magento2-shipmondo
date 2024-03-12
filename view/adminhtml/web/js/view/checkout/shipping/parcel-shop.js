define([
    'Salecto_Shipping/js/view/parcel-shop',
    'ko',
    'mage/translate',
    'underscore',
    'Salecto_Shipmondo/js/model/parcel-shop-searcher',
    'jquery'
], function(AbstractParcelShop, ko, $t, _, parcelShopSearcher, $) {

    return AbstractParcelShop.extend({
        defaults: {
            shipmondo_code: null
        },

        initialize: function() {
            this._super();

            this.parcelShopSearcher = (function(wexoShippingData, shippingCountryId) {
                try {
                    let currentShippingMethod = $('#shipping-method-additional-load').data('current-shipping-method');
                    var current = currentShippingMethod.shipping_method.extension_attributes.wexo_shipping_method_type;
                    return parcelShopSearcher(this.mapped_shipmondo_codes[current], wexoShippingData, shippingCountryId)
                } catch (e) {
                    return null;
                }
            }).bind(this);

            this.shippingPostcode.subscribe(function(newVal) {
                if (!this.source.shipping_method) {
                    this.source.set('wexoShippingData.postcode', newVal);
                }
            }, this);

            return this;
        },

        /**
         * @returns {*}
         */
        getPopupText: function() {
            return ko.pureComputed(function() {
                return $t('%1 service points in postcode <u>%2</u>')
                    .replace('%1', this.parcelShops().length)
                    .replace('%2', this.wexoShippingData().postcode);
            }, this);
        },

        formatOpeningHours: function(parcelShop) {
            try {
                if (parcelShop.opening_hours.length && parcelShop.opening_hours) {
                    return '<table>' + _.map(parcelShop.opening_hours, function(day) {
                        return '<tr><td>' + day + '</td></tr>'
                    }).join('') + '</table>';
                }
                return '';
            }
            catch (e) {
                return '';
            }
        }
    });
});
