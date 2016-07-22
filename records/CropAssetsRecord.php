<?php
namespace Craft;

/**
 * Crop Assets Model.
 *
 * Adds the ability to manually crop and resize assets per field.
 *
 * @author    Bob Olde Hampsink <b.oldehampsink@nerds.company>
 * @copyright Copyright (c) 2016, Bob Olde Hampsink
 * @license   MIT
 *
 * @link      http://github.com/boboldehampsink
 *
 * @property int $id
 * @property int $sourceAssetId
 * @property int $targetAssetId
 * @property object|array $settings
 */
class CropAssetsRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'cropassets';
    }

    /**
     * {@inheritdoc}
     */
    protected function defineAttributes()
    {
        return array(
            'sourceAssetId' => AttributeType::Number,
            'targetAssetId' => AttributeType::Number,
            'settings' => AttributeType::Mixed,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function defineRelations()
    {
        return [
            'sourceAsset' => [static::BELONGS_TO, 'ElementRecord', 'required' => true],
            'targetAsset' => [static::BELONGS_TO, 'ElementRecord', 'required' => true, 'onDelete' => static::CASCADE],
        ];
    }
}