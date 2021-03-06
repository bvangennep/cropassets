<?php

namespace Craft;

/**
 * Crop Assets Model.
 *
 * Adds the ability to manually crop and resize assets per field.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2016, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 *
 * @property int $id
 * @property int $sourceAssetId
 * @property int $targetAssetId
 * @property int $entryId
 * @property int $fieldId
 * @property object|array $settings
 */
class CropAssetsModel extends Basemodel
{

    /**
     * {@inheritdoc} BaseModel::defineAttributes()
     *
     * @return array
     */
     protected function defineAttributes()
     {
         return array(
             'id' => AttributeType::Number,
             'sourceAssetId' => AttributeType::Number,
             'targetAssetId' => AttributeType::Number,
             'entryId' => AttributeType::Number,
             'fieldId' => AttributeType::String,
             'settings' => AttributeType::Mixed,
         );
     }
}
