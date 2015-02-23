<?php


class Abstract_ExtendedApi_Catalog_Model_Product_Api extends Mage_Catalog_Model_Product_Api
{

    protected function _debug_log($value) {
        Mage::log($value, null, 'abstract_extendedapi.log');
    }

    protected function _prepareDataForSave($product, $productData) {
        // Extends product API in order to create configurable products
        parent::_prepareDataForSave($product, $productData);

        if (isset($productData['configurable_attributes_ids'])) {
            // Configurable_attributes_ids an array of attribute ids
            // used for the configurable product
            $configurableAttributesIds = $productData['configurable_attributes_ids'];

            $this::_debug_log("Attributi passati");
            $this::_debug_log($productData['configurable_attributes_ids']);

            $productType = $product->getTypeInstance(true);
            $productType->setProduct($product); // why?

            $attributes_array = $productType->getConfigurableAttributesAsArray();

            // add only new ids to configurable product
            foreach ($attributes_array as $key => $attribute) {
                $attribute_id = $attribute["attribute_id"];

                if(($id_key = array_search($attribute_id, $configurableAttributesIds)) !== false) {
                    unset($configurableAttributesIds[$id_key]);
                }
            }

            $this::_debug_log("Attributi nuovi rimasti");
            $this::_debug_log($configurableAttributesIds);

            if (count($configurableAttributesIds) > 0) {
               $productType->setUsedProductAttributeIds($configurableAttributesIds);
            }

            // cfr.: http://goo.gl/g22kX2
            $attributes_array = $productType->getConfigurableAttributesAsArray();
            foreach($attributes_array as $key => $attribute_array) {
                $attributes_array[$key]['use_default'] = 1;
                $attributes_array[$key]['position'] = 0;

                if (isset($attribute_array['frontend_label'])) {
                    $attributes_array[$key]['label'] = $attribute_array['frontend_label'];
                }
                else {
                    $attributes_array[$key]['label'] = $attribute_array['attribute_code'];
                }
            }

            // Add it back to the configurable product...
            $product->setConfigurableAttributesData($attributes_array);
        }

        if (isset($productData['associated_ids'])) {
            // associated_ids an array of simple product ids
            // that will be associated to the configurable product
            $simpleProductIds = $productData['associated_ids'];

            $this::_debug_log($product->getUsedProductIds());

            $this::_debug_log($simpleProductIds);

            // cfr.: http://goo.gl/7uVQRM
            $product->setConfigurableProductsData(array_flip($simpleProductIds));
        }

    }

}

